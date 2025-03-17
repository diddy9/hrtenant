<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TenantModule; // Import the TenantModule model
use App\Models\Hostname;     // Import the Hostname model

class CheckModuleAcess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $module): Response
    {
        // Get the tenant's ID from the authenticated user
        $tenantId = auth()->user()->tenant_id ?? null;

        if (!$tenantId) {
            return response()->json(['error' => 'Tenant not found'], 403);
        }

        // Check if the tenant has access to the specified module
        $hasAccess = TenantModule::where('tenant_id', $tenantId)
                                 ->whereHas('module', function ($query) use ($module) {
                                     $query->where('name', $module);
                                 })
                                 ->exists();

        if (!$hasAccess) {
            return response()->json([
                'error' => "Access denied. Your tenant does not have access to the {$module} module."
            ], 403);
        }
        return $next($request);
    }
}
