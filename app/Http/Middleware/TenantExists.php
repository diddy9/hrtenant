<?php

namespace App\Http\Middleware;

use Closure;
use URL;
use Illuminate\Http\Request;
use Hyn\Tenancy\Models\Hostname;

class TenantExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ( $request->user() == null ) {
            $fqdn = $request->getHost();
            if ( ! $this->tenantExists( $fqdn ) && $fqdn != "localhost" && $fqdn != "127.0.0.1") {
                abort(403,'Nope.');
            }
        }
        return $next($request);
    }

    private function tenantExists( $fqdn ) {
        return Hostname::where( 'fqdn', $fqdn )->exists();
    }
}
