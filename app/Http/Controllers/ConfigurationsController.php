<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Unit;
use App\Models\Role;
use App\Models\UserRole;
use Carbon\Carbon;
use Validator;

class ConfigurationsController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function department_store(Request $request){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:departments,name',
        ]);

        // Add tenant_id to the validated data
        $validated['tenant_id'] = auth()->user()->tenant_id;

        $department = Department::create($validated);
        return response()->json(['message' => 'Department created successfully.', 'data' => $department]);
    }

    public function department_show(){
        if (!(auth()->user()->roles->pluck('id')->contains(1) || auth()->user()->roles->pluck('id')->contains(3))) {
            return response()->json(['message' => 'Unauthorized. Access restricted to users with role 3.'], 403);
        }
        
        // Fetch all departments
        $departments = Department::all();
        
        return response()->json([
            'message' => 'All departments fetched successfully.',
            'data' => $departments
        ], 200);
    }

    public function department_update(Request $request, $id){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $department = Department::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:departments,name,' . $department->id,
        ]);

        $department->update($validated);

        return response()->json(['message' => 'Department updated successfully.', 'data' => $department]);
    }

    public function department_delete($id){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $department = Department::findOrFail($id);
        $department->delete(); // Soft delete

        return response()->json(['message' => 'Department deleted successfully.']);

    }

    public function department_restore($id){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $department = Department::withTrashed()->findOrFail($id);
        $department->restore();
        return response()->json(['message' => 'Department restored successfully.']);

    }

    public function unit_store(Request $request){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:units,name',
            'department_id' => 'required|exists:departments,id',
        ]);

        // Add tenant_id to the validated data
        $validated['tenant_id'] = auth()->user()->tenant_id;

        $unit = Unit::create($validated);
        return response()->json(['message' => 'Unit created successfully.', 'data' => $unit]);
    }

    public function unit_show(){
        if (!(auth()->user()->roles->pluck('id')->contains(1) || auth()->user()->roles->pluck('id')->contains(3))) {
            return response()->json(['message' => 'Unauthorized. Access restricted to users with role 3.'], 403);
        }
        
        // Fetch all units with their assigned department
        $units = Unit::with('department')->get();
        
        return response()->json([
            'message' => 'All units with their assigned department fetched successfully.',
            'data' => $units
        ], 200);
    }

    // Update Unit
    public function unit_update(Request $request, $id){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $unit = Unit::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:units,name,' . $unit->id,
        ]);

        $unit->update($validated);

        return response()->json(['message' => 'Unit updated successfully.', 'data' => $unit]);
    }

    // Delete Unit
    public function unit_delete($id){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $unit = Unit::findOrFail($id);
        $unit->delete(); // Soft delete

        return response()->json(['message' => 'Unit deleted successfully.']);
    }

    // Restore Unit
    public function unit_restore($id){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $unit = Unit::withTrashed()->findOrFail($id);
        $unit->restore();

        return response()->json(['message' => 'Unit restored successfully.']);
    }

    // Store Designation
    public function designation_store(Request $request)
    {
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:designations,name',
        ]);

        // Add tenant_id to the validated data
        $validated['tenant_id'] = auth()->user()->tenant_id;

        $designation = Designation::create($validated);
        return response()->json(['message' => 'Designation created successfully.', 'data' => $designation]);
    }

    public function designation_show(){
        if (!(auth()->user()->roles->pluck('id')->contains(1) || auth()->user()->roles->pluck('id')->contains(3))) {
            return response()->json(['message' => 'Unauthorized. Access restricted to users with role 3.'], 403);
        }
        
        // Fetch all departments
        $designations = Designation::all();
        
        return response()->json([
            'message' => 'All designations fetched successfully.',
            'data' => $designations
        ], 200);
    }

    // Update Designation
    public function designation_update(Request $request, $id){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $designation = Designation::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:designations,name,' . $designation->id,
        ]);

        $designation->update($validated);

        return response()->json(['message' => 'Designation updated successfully.', 'data' => $designation]);
    }

    // Delete Designation
    public function designation_delete($id){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $designation = Designation::findOrFail($id);
        $designation->delete(); // Soft delete

        return response()->json(['message' => 'Designation deleted successfully.']);
    }

    // Restore Designation
    public function designation_restore($id){
        if (!auth()->user()->roles->pluck('id')->contains(1)) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $designation = Designation::withTrashed()->findOrFail($id);
        $designation->restore();
        
        return response()->json(['message' => 'Designation restored successfully.']);
    }


    public function role_add(Request $request)
    {
        if (!auth()->user()->roles->pluck('id')->containsStrict(1)) {
            return response()->json(['message' => 'Unauthorized. Admin or Manager access required.'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        // Check if the role already exists for the user
        $existingRole = UserRole::where('user_id', $validated['user_id'])
            ->where('role_id', $validated['role_id'])
            ->withTrashed()
            ->first();

        if ($existingRole) {
            if ($existingRole->trashed()) {
                $existingRole->restore(); // Restore if previously deleted
                return response()->json(['message' => 'Role restored successfully.']);
            }

            return response()->json(['message' => 'User already has this role.'], 409);
        }

        // Create new role assignment
        $userRole = UserRole::create($validated);
        return response()->json(['message' => 'Role assigned successfully.', 'data' => $userRole]);
    }

    public function role_show()
    {
        if (!auth()->user()->roles->pluck('id')->containsStrict(1) &&
            !auth()->user()->roles->pluck('id')->containsStrict(3)) {
            return response()->json(['message' => 'Unauthorized. Admin or Manager access required.'], 403);
        }

        $userRoles = UserRole::with(['role:id,name', 'user:id,name,email'])
        ->get();

        return response()->json(['message' => 'Roles with assigned users retrieved successfully.', 'data' => $userRoles]);
    }

    // Delete Role from User
    public function role_delete($id)
    {
        if (!auth()->user()->roles->pluck('id')->containsStrict(1)) {
            return response()->json(['message' => 'Unauthorized. Admin or Manager access required.'], 403);
        }

        $userRole = UserRole::find($id);
        
        if (!$userRole) {
            return response()->json(['message' => 'Role not found for this user.'], 404);
        }
        
        $userRole->delete(); // Soft delete
        return response()->json(['message' => 'Role deleted successfully.']);
    }






}
