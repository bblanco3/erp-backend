<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'event';

    protected $fillable = [
        'event_type_id',
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'is_all_day',
        'is_completed',
        'created_by',
        'created_on',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_on' => 'datetime',
        'is_all_day' => 'boolean',
        'is_completed' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_all_day' => false,
        'is_completed' => false,
    ];

    /**
     * Get the event type associated with this event.
     */
    public function event_type()
    {
        return $this->belongsTo(EventType::class);
    }

    /**
     * Get the user who created this event.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all associations for this event.
     */
    public function associations()
    {
        return $this->hasMany(EventAssociation::class);
    }

    /**
     * Get all labor groups for this event.
     */
    public function labor_groups()
    {
        return $this->hasMany(EventLaborGroup::class);
    }

    /**
     * Get all labor assignments for this event through labor groups.
     */
    public function labor_assignments()
    {
        return $this->hasManyThrough(EventLaborAssignment::class, EventLaborGroup::class);
    }

    /**
     * Scope a query to only include active events.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include completed events.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc');
    }

    /**
     * Get the duration of the event in hours.
     */
    public function getDurationAttribute()
    {
        if ($this->is_all_day) {
            return 24;
        }

        return $this->start_time->diffInHours($this->end_time);
    }
}
