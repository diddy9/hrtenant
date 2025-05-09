<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{PayslipDetail, Configurations, Designation, User, UserRole, Notifications, Payslip, Pay_Staff_Details};
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollScheduleReport;

class PayrollController extends Controller
{
    public function itemByDesignation($id)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {
            $designation = PayslipDetail::distinct()->pluck('desig_id');
            $details = PayslipDetail::where('desig_id', $id)->orderBy('id', 'desc')->get();

            return response()->json([
                'per_designation' => $designation,
                'details' => $details
            ]); 
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    public function storeItem(Request $request)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'designation' => ['required', 'numeric'],
                'amount' => ['required', 'numeric'],
                'type' => ['required']
            ]);

            $item = PayslipDetail::create([
                'tenant_id' => auth()->user()->tenant_id,
                'created_by' => auth()->id(),
                'desig_id' => $request->designation,
                'item_name' => ucwords($request->name),
                'amount' => $request->amount,
                'type' => $request->type
            ]);
            return response()->json(['message' => 'Line item added successfully', 'item' => $item]);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    public function updateItem(Request $request, $id)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'amount' => ['required', 'numeric'],
            ]);

            $item = PayslipDetail::findOrFail($id);
            $item->item_name = ucwords($request->name);
            $item->amount = $request->amount;
            $item->status = $request->status ?? $item->status;
            $item->update();

            return response()->json(['message' => 'Line item updated successfully', 'item' => $item]);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    public function deleteItem($id)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {
            $item = PayslipDetail::findOrFail($id);
            $item->deleted_at = Carbon::now();
            $item->save();

            return response()->json(['message' => 'Line item deleted successfully']);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    private function isPayrollManager($userId) {
        return UserRole::where('user_id', $userId)->where('role_id', 6)->exists();
    }

    public function paySummary()
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {
            $payslips = Payslip::where('status', 'PAID')->orderBy('id', 'desc')->get();

            $payrollSummary = Payslip::groupBy('payment_date', 'created_by')
                ->selectRaw('sum(gross) as gross, sum(deductions) as deductions, sum(net) as net, payment_date, created_by')
                ->orderBy('payment_date', 'desc')
                ->get();

            return response()->json([
                'payslips' => $payslips,
                'payroll_summary' => $payrollSummary,
            ]);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    public function validateSalary(Request $request)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {

            $request->validate([
                'month' => 'required|string|max:255',
                'year' => 'required|string|max:255',
            ]);

            $date = $request->year . '-' . $request->month;
            $exists = Payslip::where('payment_date', $date)->exists();

            return response()->json([
                'payment_date' => $date,
                'already_processed' => $exists,
                'message' => $exists ? 'Salary already processed.' : 'Salary can be processed.',
            ]);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    public function paySalary($date)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {

            $pending = Payslip::where([
                ['status', 'PENDING'],
                ['payment_date', $date]
            ])->count();

            if ($pending > 0) {
                Payslip::where('payment_date', $date)->update(['status' => 'PAID']);
                return response()->json(['message' => 'Payment made successfully']);
            } 
            return response()->json(['message' => 'No pending salaries to pay']);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    public function exportSchedule($date)
    {
        return Excel::download(new PayrollScheduleReport($date), 'schedule.xlsx');
    }

    public function processPayroll($date)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {
            if (Payslip::where('payment_date', $date)->exists()) {
                $payslips = Payslip::where('payment_date', $date)->get();
                return response()->json(['payslips' => $payslips]);
            }

            $employees = User::all();
            foreach ($employees as $employee) {
                $empDesig = $employee->designation_id;
                $gross = $this->getDefaultGross($employee->id);
                $deductions = $this->getDefaultDeductions($employee->id);
                $net = $gross - $deductions;

                $payslip = Payslip::create([
                    'tenant_id' => $user->tenant_id,
                    'created_by' => $user->id,
                    'user_id' => $employee->id,
                    'desig_id' => $empDesig,
                    'gross' => $gross,
                    'deductions' => $deductions,
                    'net' => $net,
                    'payment_date' => $date
                ]);
            }

            return response()->json(['message' => 'Salaries processed successfully.']);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    public function getProcessedPayroll($date)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {
            if (!Payslip::where('payment_date', $date)->exists()) {
                return response()->json(['message' => 'Date not found.'], 404);
            }

            $payslips = Payslip::where('payment_date', $date)->get();
            return response()->json(['payslips' => $payslips]);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    public function viewPayslip($id)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {
            $payslip = Payslip::findOrFail($id);
            if ($payslip->status === 'PAID') {
                return response()->json(['message' => 'Cannot edit a paid salary.'], 403);
            }

            return response()->json([
                'payslip' => $payslip,
                'employee' => User::find($payslip->user_id),
                'credit_details' => PayslipDetail::where('desig_id', $payslip->desig_id)->where('status', 'Active')->where('type', 'Credit')->get(),
                'debit_details' => PayslipDetail::where('desig_id', $payslip->desig_id)->where('status', 'Active')->where('type', 'Debit')->get(),
                'other_credits' => Pay_Staff_Details::where('pid', $payslip->id)->where('type', 'Credit')->get(),
                'other_debits' => Pay_Staff_Details::where('pid', $payslip->id)->where('type', 'Debit')->get(),
            ]);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    public function storePayslipItem(Request $request)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {
            $request->validate([
                'name' => 'required|string|max:255',
                'amount' => 'required|numeric',
                'type' => 'required|string|max:255',
                'emp_id' => 'required|integer',
                'pid' => 'required|integer'
            ]);

            $payslip = Payslip::find($request->pid);
            if (!$payslip) {
                return response()->json(['message' => 'Invalid payslip ID.'], 404);
            }

            // Validate that the emp_id matches the user_id on the payslip
            if ($payslip->user_id !== (int) $request->emp_id) {
                return response()->json(['message' => 'Payslip is not assigned to this employee.'], 422);
            }

            if ($payslip->status === 'PAID') {
                return response()->json(['message' => 'Cannot edit a paid salary.'], 403);
            }

            $pid = $request->pid;
            $amount = $request->amount;

            if ($request->type == 'Credit') {
                $this->updatePayslipGross($pid, $amount);
            } else {
                $this->updatePayslipDeductions($pid, $amount);
            }
            $this->updatePayslipNet($pid);

            $detail = Pay_Staff_Details::create([
                'tenant_id' => $user->tenant_id,
                'created_by' => $user->id,
                'user_id' => $request->emp_id,
                'pid' => $pid,
                'item_name' => ucwords($request->name),
                'amount' => $amount,
                'type' => $request->type
            ]);

            return response()->json([
                'payslip' => $payslip->refresh(),
                'employee' => User::find($payslip->user_id),
                'credit_details' => PayslipDetail::where('desig_id', $payslip->desig_id)
                    ->where('status', 'Active')
                    ->where('type', 'Credit')
                    ->get(),
                'debit_details' => PayslipDetail::where('desig_id', $payslip->desig_id)
                    ->where('status', 'Active')
                    ->where('type', 'Debit')
                    ->get(),
                'other_credits' => Pay_Staff_Details::where('pid', $payslip->id)
                    ->where('type', 'Credit')
                    ->get(),
                'other_debits' => Pay_Staff_Details::where('pid', $payslip->id)
                    ->where('type', 'Debit')
                    ->get(),
            ]);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    public function deletePayslipItem($id)
    {
        $user = auth()->user();
        $hasAccess = $this->isPayrollManager($user->id);
        if ($hasAccess) {
            $detail = Pay_Staff_Details::findOrFail($id);
            $detail->deleted_at = now();

            if ($detail->type == 'Debit') {
                $this->updatePayslipItemDeductions($detail->pid, $detail->amount);
            } else {
                $this->updatePayslipItemGross($detail->pid, $detail->amount);
            }
            $this->updatePayslipNet($detail->pid);
            $detail->update();

            return response()->json(['message' => 'Payslip item deleted successfully.']);
        }
        return response()->json(['message' => 'Access Denied'], 403);
    }

    private function getDefaultGross($uid)
    {
        $empDesig = User::where('id', $uid)->value('designation_id');
        return PayslipDetail::where('desig_id', $empDesig)->where('type', 'Credit')->where('status', 'Active')->sum('amount');
    }

    private function getDefaultDeductions($uid)
    {
        $empDesig = User::where('id', $uid)->value('designation_id');
        return PayslipDetail::where('desig_id', $empDesig)->where('type', 'Debit')->where('status', 'Active')->sum('amount');
    }

    private function updatePayslipGross($pid, $amount)
    {
        Payslip::where('id', $pid)->increment('gross', $amount);
    }

    private function updatePayslipItemGross($pid, $amount)
    {
        Payslip::where('id', $pid)->decrement('gross', $amount);
    }

    private function updatePayslipDeductions($pid, $amount)
    {
        Payslip::where('id', $pid)->increment('deductions', $amount);
    }

    private function updatePayslipItemDeductions($pid, $amount)
    {
        Payslip::where('id', $pid)->decrement('deductions', $amount);
    }

    private function updatePayslipNet($pid)
    {
        $payslip = Payslip::find($pid);
        $net = $payslip->gross - $payslip->deductions;
        $payslip->update(['net' => $net]);
    }



}
