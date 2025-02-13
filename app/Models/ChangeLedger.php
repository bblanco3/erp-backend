<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeLedger extends Model
{
    protected $fillable = [
        'tenant_id',
        'model_type',
        'model_id',
        'action',
        'user_id',
        'changes',
        'processed',
        'processed_at'
    ];

    protected $casts = [
        'changes' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime'
    ];

    /**
     * Get the user who made the change.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the changed model.
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include unprocessed changes.
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope a query to only include changes for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
