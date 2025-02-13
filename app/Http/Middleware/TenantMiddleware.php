<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    protected $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get subdomain from request
        $host = $request->getHost();
        $parts = explode('.', $host);
        $subdomain = count($parts) > 2 ? $parts[0] : 'default';

        // Find tenant by subdomain
        $tenant = Tenant::where('domain', $subdomain)->first();
        
        if (!$tenant) {
            // For development, create a tenant if it doesn't exist
            $tenant = Tenant::create([
                'name' => ucfirst($subdomain),
                'domain' => $subdomain,
                'database' => $subdomain
            ]);
        }

        // Set the tenant and switch to its database
        $this->tenantManager->setTenant($tenant);
        
        // Set tenant in request for use in controllers
        $request->merge(['tenant_id' => $tenant->id]);
        
        return $next($request);
    }
}
