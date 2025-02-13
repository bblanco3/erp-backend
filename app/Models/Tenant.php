<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $table = 'tenants';
    
    protected $fillable = [
        'name',
        'entity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all users associated with this tenant
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
