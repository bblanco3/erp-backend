<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'account_id',
        'is_online',
        'is_active',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_online' => 'boolean',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /**
     * Get the account that owns the user.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the tenant that owns the user.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get roles for a specific tenant.
     */
    public function get_roles_for_tenant($tenant_id)
    {
        return $this->roles()->where('tenant_id', $tenant_id)->get();
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('is_deleted', false);
    }

    /**
     * Check if the user belongs to a specific tenant.
     */
    public function belongsToTenant($tenant_id): bool
    {
        return $this->tenant_id == $tenant_id;
    }

    /**
     * Get the user's full information including tenant.
     */
    public function getFullInfoAttribute()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'tenant' => $this->tenant,
            'account' => $this->account,
            'is_online' => $this->is_online,
            'roles' => $this->roles,
        ];
    }
}
