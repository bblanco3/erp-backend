<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IdentifyTenant
{
    protected TenantManager $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    public function handle(Request $request, Closure $next)
    {
        $tenantId = $this->tenantManager->getTenantIdFromRequest($request);

        if (!$tenantId) {
            throw new NotFoundHttpException('Tenant not found');
        }

        $tenant = Tenant::where('entity', $tenantId)->first();

        if (!$tenant) {
            throw new NotFoundHttpException('Tenant not found');
        }

        $this->tenantManager->setTenant($tenant);

        return $next($request);
    }
}
