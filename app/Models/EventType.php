<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    use HasFactory;

    protected $table = 'event_types';

    protected $fillable = [
        'name',
        'description',
        'requires_labor_tracking',
        'color_code',
        'icon',
    ];

    protected $casts = [
        'requires_labor_tracking' => 'boolean',
    ];

    /**
     * Get all events of this type.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Scope a query to only include types that require labor tracking.
     */
    public function scopeRequiresLabor($query)
    {
        return $query->where('requires_labor_tracking', true);
    }
}
