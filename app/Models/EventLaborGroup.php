<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLaborGroup extends Model
{
    use HasFactory;

    protected $table = 'event_labor_groups';

    protected $fillable = [
        'event_id',
        'name',
        'vehicle_id',
        'start_time',
        'end_time',
        'notes',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the event that owns the labor group.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the vehicle assigned to this labor group.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get all labor assignments for this group.
     */
    public function labor_assignments()
    {
        return $this->hasMany(EventLaborAssignment::class, 'event_labor_group_id');
    }

    /**
     * Get the duration of the labor group in hours.
     */
    public function getDurationAttribute()
    {
        // If no specific times set, use event times
        if (!$this->start_time || !$this->end_time) {
            return $this->event->duration;
        }

        return $this->start_time->diffInHours($this->end_time);
    }

    /**
     * Get all employees assigned to this group.
     */
    public function employees()
    {
        return $this->hasManyThrough(
            Employee::class,
            EventLaborAssignment::class,
            'event_labor_group_id',
            'id',
            'id',
            'employee_id'
        );
    }

    /**
     * Scope a query to only include groups with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get the total labor cost for this group.
     */
    public function getTotalLaborCostAttribute()
    {
        return $this->labor_assignments->sum(function ($assignment) {
            return $assignment->actual_hours * $assignment->pay_rate;
        });
    }
}
