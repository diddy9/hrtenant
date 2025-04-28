<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Models\{Leave_Category, Leave_Application, FinancialYear, User, UserRole, Notifications};
use Carbon\Carbon;
use Validator;

class LeaveController extends Controller
{
    public function getCategories() {
        $categories = Leave_Category::all();
    
        return response()->json([
            'message' => 'Leave categories retrieved successfully',
            'categories' => $categories
        ]);
    }

    public function storeCategory(Request $req) {
        $validatedData = $req->validate([
            'type' => ['required', 'string', 'max:255'],
            'days' => ['required', 'numeric']
        ]);

        $category = Leave_Category::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->user()->id,
            'type' => ucwords($req->type),
            'days' => $req->days
        ]);

        return response()->json(['message' => 'Leave category added successfully', 'category' => $category]);
    }

    public function updateCategory(Request $req, $id) {
        $validatedData = $req->validate([
            'type' => ['required', 'string', 'max:255'],
            'days' => ['required', 'numeric']
        ]);

        $leave = Leave_Category::find($id);
        if (!$leave) return response()->json(['error' => 'Category not found'], 404);

        $leave->update([
            'type' => $req->type,
            'days' => $req->days
        ]);

        return response()->json(['message' => 'Leave category updated successfully']);
    }

    public function deleteCategory($id) {
        $leave = Leave_Category::find($id);
        if (!$leave) return response()->json(['error' => 'Category not found'], 404);

        $leave->update(['deleted_at' => Carbon::now()->toDateTimeString()]);
        return response()->json(['message' => 'Leave category deleted successfully']);
    }

    public function getApplications() {
        return response()->json(Leave_Application::with(['category', 'reliever', 'user'])
            ->where('user_id', auth()->user()->id)
            ->get());
    }

    public function apply(Request $req){
        $validatedData = $req->validate([
            'category' => ['required'],
            'from' => ['required', 'date', 'after:today'],
            'to' => ['required', 'date', 'after:from'],
            'relief' => ['required']
        ]);

        $category = Leave_Category::find($req->category);
        if (!$category) {
            return response()->json(['error' => 'Invalid leave category selected'], 400);
        }

        // Check if the tenant has a Leave Manager (role_id = 5)
        $tenantId = auth()->user()->tenant_id;
        $hasLeaveManager = UserRole::where('tenant_id', $tenantId)->where('role_id', 5)->exists();

        if (!$hasLeaveManager) {
            return response()->json(['error' => 'Your organization does not have a Leave Manager assigned. Leave applications cannot be processed.'], 400);
        }

        $days = $this->getWorkingDays($req->from, $req->to, []);
        $remainder = $this->getRemainingLeaveDays($req->category);

        if ($days > $category->days) {
            return response()->json(['error' => 'The selected days exceed the allowed leave days for this category'], 400);
        }

        if ($days > $remainder) {
            return response()->json(['error' => 'The selected days exceed your available leave balance'], 400);
        }

        // Check Financial Year
        $financialYear = FinancialYear::where('tenant_id', $tenantId)->first();
        if (!$financialYear) {
            return response()->json(['error' => 'Financial Year not defined for your organization'], 400);
        }

        $status = (auth()->user()->id == auth()->user()->supervisor_id) ? 'APPROVED' : 'PENDING';
        $approver = (auth()->user()->id == auth()->user()->supervisor_id) ? $this->getLeaveManager() : auth()->user()->supervisor_id;

        $app = Leave_Application::create([
            'user_id' => auth()->user()->id,
            'tenant_id' => auth()->user()->tenant_id,
            'sid' => auth()->user()->supervisor_id,
            'reliver_id' => $req->relief,
            'category_id' => $req->category,
            'start_date' => $req->from,
            'end_date' => $req->to,
            'days' => $days,
            'status' => $status,
            'leave_year' => date('Y'),
        ]);

        $this->sendNotification(auth()->user()->id, $approver, 'Pending leave approval', 'Leave', $app->id);

        return response()->json(['message' => 'Leave request submitted successfully', 'application' => $app]);

    }

    public function acceptLeave($id) {
        $application = Leave_Application::find($id);
        $nid = Notifications::where([
            ['mid', '=', $id],
            ['type', '=', 'Leave'],
            ['receiver', '=', auth()->user()->id]
        ])->orderBy('id', 'desc')->select('id')->pluck('id')->first();
        $notification = Notifications::find($nid);
        
        if ($application->sid == auth()->user()->id) {
            $application->status = "APPROVED";
            $this->sendNotification($application->user_id, $this->getLeaveManager(), "Pending leave approval", "Leave", $application->id);
        } elseif ($this->isLeaveManager(auth()->user()->id)) {
            $application->status = "GRANTED";
        }
        
        $notification->status = "READ";
        $application->save();
        $notification->save();

        return response()->json(['message' => 'Leave application processed successfully', 'status' => $application->status]);
    }

    public function rejectLeave(Request $req) {
        $application = Leave_Application::find($req->lid);
        $notification = Notifications::find($req->nid);
        
        $application->status = "REJECTED";
        $notification->status = "READ";
        
        $application->save();
        $notification->save();

        return response()->json(['message' => 'Leave application rejected successfully']);
    }

    private function isLeaveManager($userId) {
        return UserRole::where('user_id', $userId)->where('role_id', 5)->exists();
    }

    private function getLeaveManager() {
        return UserRole::where('role_id', 5)->inRandomOrder()->value('user_id');
    }

    public function sendNotification($sender, $receiver, $message, $type, $rid) {
        Notifications::create([
            'tenant_id' => auth()->user()->tenant_id,
            'mid' => $rid,
            'sender' => $sender,
            'receiver' => $receiver,
            'message' => $message,
            'type' => $type,
        ]);
    }

    private function getWorkingDays($from, $to, $holidays = []) {
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);
        $workingDays = 0;

        while ($start <= $end) {
            if (!$start->isWeekend() && !in_array($start->toDateString(), $holidays)) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }

    private function getRemainingLeaveDays($categoryId) {
        $leaveTaken = Leave_Application::where('user_id', auth()->user()->id)
            ->where('category_id', $categoryId)
            ->where('status', 'GRANTED')
            ->sum('days');

        $totalDays = Leave_Category::where('id', $categoryId)->value('days');

        return $totalDays - $leaveTaken;
    }

     // Financial Year Functions
     public function getFinancialYears() {
        $financialYears = FinancialYear::where('tenant_id', auth()->user()->tenant_id)->get();
        return response()->json($financialYears);
    }

    public function storeFinancialYear(Request $req) {
        $validatedData = $req->validate([
            'start_date' => ['required'],
            'end_date' => ['required'],
            'carryover_percentage' => ['required', 'numeric', 'min:0', 'max:100']
        ]);

        // Check if a financial year already exists for the tenant
        $existing = FinancialYear::where('tenant_id', auth()->user()->tenant_id)->first();
        
        if ($existing) {
            return response()->json(['error' => 'A financial year already exists for this tenant. Please update instead.'], 400);
        }

        $financialYear = FinancialYear::create([
            'tenant_id' => auth()->user()->tenant_id,
            'start_date' => $req->start_date,
            'end_date' => $req->end_date,
            'carryover_percentage' => $req->carryover_percentage
        ]);

        return response()->json([
            'message' => 'Financial year added successfully', 
            'financial_year' => $financialYear
        ], 201);
    }

    public function updateFinancialYear(Request $req) {
        $validatedData = $req->validate([
            'start_date' => ['required'],
            'end_date' => ['required']
        ]);

        $financialYear = FinancialYear::where('tenant_id', auth()->user()->tenant_id)->first();

        if (!$financialYear) {
            return response()->json(['error' => 'Financial year not found for this tenant'], 404);
        }

        $financialYear->update([
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date']
        ]);

        return response()->json(['message' => 'Financial year updated successfully', 'financial_year' => $financialYear]);
    }

    public function deleteFinancialYear() {
        $financialYear = FinancialYear::where('tenant_id', auth()->user()->tenant_id)->first();
        $financialYear->delete();
        return response()->json(['message' => 'Financial year deleted successfully']);
    }


}
