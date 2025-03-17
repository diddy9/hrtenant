<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Profile;
use Carbon\Carbon;
use Validator;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response as ResponseConstant;

class ProfileController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(): JsonResponse {
        $user = auth()->user()->load('profile');
        return response()->json($user, ResponseConstant::HTTP_OK);
    }

    public function updateProfile(Request $request): JsonResponse{
        $request->validate([
            'avatar' => 'nullable|string',
            'gender' => 'nullable|string',
            'start_date' => 'nullable|date',
            'nhf_no' => 'nullable|string',
            'pfa_id' => 'nullable|integer',
            'rsa_pin_no' => 'nullable|string',
            'grade' => 'nullable|string',
            'r_address' => 'nullable|string',
            'p_address' => 'nullable|string',
            'title' => 'nullable|string',
            'phone' => 'nullable|string',
            'd_o_b' => 'nullable|date',
            'p_o_b' => 'nullable|string',
            'nationality' => 'nullable|string',
            'state_of_origin' => 'nullable|string',
            'home_town' => 'nullable|string',
            'local_govt' => 'nullable|string',
            'marital_status' => 'nullable|string',
            'religion' => 'nullable|string',
            'name_of_spouse' => 'nullable|string',
            'maiden_name' => 'nullable|string',
            'spouse_phone' => 'nullable|string',
            'address_of_spouse' => 'nullable|string',
            'next_of_kin_ben' => 'nullable|string',
            'relationship_ben' => 'nullable|string',
            'address_ben' => 'nullable|string',
            'tel_ben' => 'nullable|string',
            'next_of_kin_em' => 'nullable|string',
            'relationship_em' => 'nullable|string',
            'address_em' => 'nullable|string',
            'tel_em' => 'nullable|string',
            'disability' => 'nullable|string',
            'height' => 'nullable|string',
            'weight' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'genotype' => 'nullable|string',
            'hobbies' => 'nullable|string',
            'languages' => 'nullable|string',
            'indebted' => 'nullable|boolean',
            'debt_details' => 'nullable|string',
            'intention' => 'nullable|string',
            'convict' => 'nullable|boolean',
            'crime_details' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'account_no' => 'nullable|string',
            'sort_code' => 'nullable|string',
            'salary_basis' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'payment_type' => 'nullable|string',
            'cv' => 'nullable|string',
            'contract_letter' => 'nullable|string'
        ]);
        
        $user = auth()->user();
        
        // Ensure the profile exists
        if (!$user->profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        // Update the profile with validated data
        $user->profile->update($request->all());
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $user->profile
        ], 200);
    }


    public function passwordUpdate(Request $request): JsonResponse{
        $request->validate([
            'old' => 'required|string',
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
            ],
        ]);
        
        $user = auth()->user();
        
        // Check if the old password matches
        if (!Hash::check($request->old, $user->password)) {
            return response()->json([
                'error' => 'The current password is incorrect'
            ], 400);
        }
        
        // Update password
        $user->password = Hash::make($request->password);
        $user->default_pass = 'NO';
        $user->save();
        
        return response()->json([
            'message' => 'Password updated successfully. Please log in again.'
        ], 200);
    }



}
