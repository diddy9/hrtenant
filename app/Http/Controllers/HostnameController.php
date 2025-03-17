<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use App\Models\Hostname;
use App\Models\TenantModule;
use App\Models\User;
use Carbon\Carbon;
use Validator;
use Auth;
use Symfony\Component\HttpFoundation\Response as ResponseConstant;

class HostnameController extends Controller
{
    public function index(): JsonResponse
    {
        $responseData = Hostname::all();
        $status = ResponseConstant::HTTP_OK;
        return response()->json($responseData, $status);
    }

    public function identifyTenant(Request $request)
    {
        // Define invalid subdomains
        $invalidSubdomains = config('app.invalid_subdomains');

        // Validate request data
        $validatedData = $request->validate([
            'account' => [
                'required',
                'string',
                Rule::notIn($invalidSubdomains),
                'regex:/^[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])$/'
            ],
        ]);

        // Build the full domain
        $fqdn = $validatedData['account'] . '.' . config('app.url_base');
        $hostExists = Hostname::where('fqdn', $fqdn)->exists();

        // Determine port (use env-based logic for flexibility)
        $port = $request->server('SERVER_PORT') == 8000 ? ':8000' : '';

        if ($hostExists) {
            // Store tenant info in session
            session(['tenant_account' => $validatedData['account']]);

            // Debug: Check if the tenant_account is stored in session
            $tenantAccountInSession = session('tenant_account');

            return response()->json([
                'success' => true,
                'message' => 'Tenant account stored in session.',
                'tenant_account' => $tenantAccountInSession,  // Debugging the stored tenant account
                'login_url' => ($request->secure() ? 'https://' : 'http://') . $fqdn . $port . '/login'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Your domain name does not exist. Contact the administrator.'
            ], 404);
        }


    }

    public function view($id): JsonResponse
    {
        $tenant = Hostname::with('tenantModules.module')->find($id);
        
        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant not found.'
            ], ResponseConstant::HTTP_NOT_FOUND);
        }
        
        $modules = $tenant->tenantModules->map(function ($tenantModule) {
            return [
                'id' => $tenantModule->module->id,
                'name' => $tenantModule->module->name
            ];
        });
        
        return response()->json([
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->fqdn,  // Assuming `fqdn` is the tenant name
            'modules' => $modules
        ], ResponseConstant::HTTP_OK);
    }

    public function login(Request $request){
       
        // Check if the tenant exists
        $host = $request->getHost();
        $tenantAccount = explode('.', $host)[0];
        $fqdn = $tenantAccount . '.' . config('app.url_base');
        $hostExists = Hostname::where('fqdn', $fqdn)->exists();

        if (!$hostExists) {
            return response()->json([
                'message' => 'Invalid tenant account.'
            ], 404);  // Tenant not found
        }
    
        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Unauthorized, invalid email or password.'
            ], 401);  // Invalid credentials
        }
    
        // Retrieve user details
        $user = User::where('email', $request['email'])->firstOrFail();
    
        // Generate a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'Hi ' . $user->f_name . ', welcome to home.',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);


    }

    public function logout(){
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'No authenticated user found.'
            ], 401);
        }
        
        $user->tokens()->delete();
        return [
            'message' => 'You have successfully logged out and the token was successfully deleted'
        ];
    } 



}
