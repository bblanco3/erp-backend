<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLaborAssignment extends Model
{
    use HasFactory;

    protected $table = 'event_labor_assignments';

    protected $fillable = [
        'event_labor_group_id',
        'employee_id',
        'actual_hours',
        'pay_rate_type',
        'pay_rate',
        'role',
        'notes',
        'status',
    ];

    protected $casts = [
        'actual_hours' => 'decimal:2',
        'pay_rate' => 'decimal:2',
    ];

    /**
     * Get the labor group that owns the assignment.
     */
    public function labor_group()
    {
        return $this->belongsTo(EventLaborGroup::class, 'event_labor_group_id');
    }

    /**
     * Get the employee assigned.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the event through the labor group.
     */
    public function event()
    {
        return $this->hasOneThrough(
            Event::class,
            EventLaborGroup::class,
            'id',
            'id',
            'event_labor_group_id',
            'event_id'
        );
    }

    /**
     * Get the total cost for this assignment.
     */
    public function getTotalCostAttribute()
    {
        return $this->actual_hours * $this->pay_rate;
    }

    /**
     * Scope a query to only include assignments with a specific role.
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to only include assignments with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include assignments for a specific pay rate type.
     */
    public function scopePayRateType($query, $type)
    {
        return $query->where('pay_rate_type', $type);
    }
}
