<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TenantManager
{
    protected ?Tenant $currentTenant = null;

    public function setTenant(?Tenant $tenant): void
    {
        $this->currentTenant = $tenant;

        if ($tenant) {
            $this->switchDatabase($tenant);
        }
    }

    public function getTenant(): ?Tenant
    {
        return $this->currentTenant;
    }

    public function switchDatabase(Tenant $tenant): void
    {
        Config::set('database.connections.tenant.database', $tenant->database_name);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function getTenantIdFromRequest($request): ?string
    {
        // Try to get tenant from subdomain
        $hostname = $request->getHost();
        $parts = explode('.', $hostname);
        $subdomain = count($parts) >= 3 ? $parts[0] : null;

        // If no subdomain, try to get from JWT token
        if (!$subdomain) {
            try {
                $token = $request->bearerToken();
                if ($token) {
                    $payload = \Firebase\JWT\JWT::decode(
                        $token,
                        config('jwt.secret'),
                        ['HS256']
                    );
                    return $payload->current_tenant ?? null;
                }
            } catch (\Exception $e) {
                return null;
            }
        }

        return $subdomain;
    }
}
