<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;

class TenantController extends Controller
{
    public function switch_tenant(Request $request)
    {
        $new_tenant_id = $request->input('tenant_id');
        $user = $request->user();

        // Verify user has access to the tenant
        $tenant = Tenant::where('entity', $new_tenant_id)
            ->whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->firstOrFail();

        // Get user's roles for all tenants
        $tenant_list = $user->tenants->mapWithKeys(function ($tenant) use ($user) {
            return [$tenant->entity => [
                'user_id' => $user->id,
                'roles' => $user->get_roles_for_tenant($tenant->id)->pluck('name')->toArray()
            ]];
        });

        // Create JWT payload
        $payload = [
            'account_id' => $user->id,
            'current_user' => $user->id,
            'current_tenant' => $tenant->entity,
            'tenant_list' => $tenant_list
        ];

        // Generate new token
        $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

        return response()->json([
            'token' => $token,
            'tenant' => $tenant->only(['name', 'entity'])
        ]);
    }
}
