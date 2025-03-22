<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
    
    public function index($slug){
        $user = User::with([
            'department',
            'unit',
            'designation',
            'supervisor:id,f_name,l_name',
            'profile'
        ])->where('slug', $slug)->first();
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json(['user' => $user], 200);
    }

    // Create User
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string',
            'l_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'department_id' => 'required|string',
            'unit_id' => 'required|string',
            'designation_id' => 'required|string',
            'supervisor_id' => 'required|string',
            'password' => 'required|string|min:8',
            'start_date' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $validatedData['tenant_id'] = auth()->user()->tenant_id;

        // Generate initial slug
        $slug = Str::slug($request->f_name . ' ' . $request->o_name . ' ' . $request->l_name);

        // Ensure uniqueness by appending numbers if needed
        $existingSlugCount = User::where('slug', 'like', "{$slug}%")->count();
        if ($existingSlugCount > 0) {
            $slug = "{$slug}-" . ($existingSlugCount + 1);
        }

        $validatedData['slug'] = $slug;

        $user = User::create(array_merge(
            $validatedData,
            ['password' => Hash::make($request->password)]
        ));

        Profile::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'emp_id' => $request->emp_id,
            'start_date' => $request->start_date,
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    // Get All Users with References
    public function getAllUsers()
    {
        $users = User::with([
            'department',
            'unit',
            'designation',
            'supervisor:id,f_name,l_name',
            'profile'
        ])->get();

        return response()->json(['users' => $users], 200);
    }

    // Soft Delete User
    public function deleteUser($id){
        $user = User::with('profile')->findOrFail($id);
        
        // Soft delete the profile if it exists
        if ($user->profile) {
            $user->profile->delete();
        }

        // Soft delete the user
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function editUser(Request $request, $id){
        $validator = Validator::make($request->all(), [
            // User table fields
            'f_name' => 'sometimes|string',
            'l_name' => 'sometimes|string',
            'o_name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'department_id' => 'sometimes|string',
            'unit_id' => 'sometimes|string',
            'designation_id' => 'sometimes|string',
            'supervisor_id' => 'sometimes|string',

            // Profile table fields
            'avatar' => 'sometimes|string',
            'gender' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'nhf_no' => 'sometimes|string',
            'pfa_id' => 'sometimes|string',
            'rsa_pin_no' => 'sometimes|string',
            'grade' => 'sometimes|string',
            'r_address' => 'sometimes|string',
            'p_address' => 'sometimes|string',
            'title' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'd_o_b' => 'sometimes|date',
            'p_o_b' => 'sometimes|string',
            'nationality' => 'sometimes|string',
            'state_of_origin' => 'sometimes|string',
            'home_town' => 'sometimes|string',
            'local_govt' => 'sometimes|string',
            'marital_status' => 'sometimes|string',
            'religion' => 'sometimes|string',
            'name_of_spouse' => 'sometimes|string',
            'maiden_name' => 'sometimes|string',
            'spouse_phone' => 'sometimes|string',
            'address_of_spouse' => 'sometimes|string',
            'next_of_kin_ben' => 'sometimes|string',
            'relationship_ben' => 'sometimes|string',
            'address_ben' => 'sometimes|string',
            'tel_ben' => 'sometimes|string',
            'next_of_kin_em' => 'sometimes|string',
            'relationship_em' => 'sometimes|string',
            'address_em' => 'sometimes|string',
            'tel_em' => 'sometimes|string',
            'disability' => 'sometimes|string',
            'height' => 'sometimes|string',
            'weight' => 'sometimes|string',
            'blood_group' => 'sometimes|string',
            'genotype' => 'sometimes|string',
            'hobbies' => 'sometimes|string',
            'languages' => 'sometimes|string',
            'indebted' => 'sometimes|string',
            'debt_details' => 'sometimes|string',
            'intention' => 'sometimes|string',
            'convict' => 'sometimes|string',
            'crime_details' => 'sometimes|string',
            'bank_name' => 'sometimes|string',
            'account_no' => 'sometimes|string',
            'sort_code' => 'sometimes|string',
            'salary_basis' => 'sometimes|string',
            'salary' => 'sometimes|string',
            'payment_type' => 'sometimes|string',
            'cv' => 'sometimes|string',
            'contract_letter' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // Update User
        $user = User::findOrFail($id);
        $user->update(array_filter($validatedData)); // Only non-null values are updated

        // Update Profile
        $profileData = array_intersect_key($validatedData, array_flip((new Profile)->getFillable()));
        if ($user->profile) {
            $user->profile->update($profileData);
        } else {
            Profile::create(array_merge($profileData, [
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id
            ]));
        }
        
        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    


}
