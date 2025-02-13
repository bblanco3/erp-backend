<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSegment extends Model
{
    use HasFactory;

    // Specify the table name (optional if it follows Laravel's naming convention)
    protected $table = 'project_segment';

    // Define fillable fields to allow mass assignment
    protected $fillable = [
        'project_id',
        'name',
        'due_date',
        'current_phase',
        'address',
        'city',
        'state',
        'zip',
        'details',
        'completed_on',
        'is_active',
        'is_deleted',
    ];

    // Define attributes with default values
    protected $attributes = [
        'is_active' => 1,
        'is_deleted' => 0,
    ];

    // Disable timestamps if the table does not have `created_at` and `updated_at`
    public $timestamps = false;

    /**
     * Define relationships (if applicable).
     */

    // Relationship to Project
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Accessors and Mutators (optional, if you need data transformation).
     */

    // Example: Accessor for is_active status
    public function getIsActiveAttribute($value)
    {
        return $value == 1 ? 'Active' : 'Inactive';
    }

    // Example: Accessor for is_deleted status
    public function getIsDeletedAttribute($value)
    {
        return $value == 1 ? 'Deleted' : 'Not Deleted';
    }

    /**
     * Get all employee allocations for this project segment.
     */
    public function employee_allocations()
    {
        return $this->morphMany(EmployeeAllocation::class, 'allocated_class');
    }

    /**
     * Get the team lead allocated to this project segment.
     */
    public function team_lead()
    {
        return $this->employee_allocations()
            ->where('role_type', 'team_lead')
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->first()
            ->employee();
    }

    /**
     * Get all employees allocated to this project segment with a specific role.
     */
    public function get_allocated_employees(string $role_type = null)
    {
        $query = $this->employee_allocations()
            ->where('is_active', true)
            ->where('is_deleted', false);
            
        if ($role_type) {
            $query->where('role_type', $role_type);
        }
        
        return $query->get()->map(function ($allocation) {
            return $allocation->employee;
        });
    }

    /**
     * Get percentage of project segment completed by a given date 
     */
    public function get_completed_percent(string $date = null)
    {
       
        // ensure that date is set 
        if (!$date) {
            $date = now();
        }

        // get actual spent hours
        $actual_hours = $this->employee_allocations()->sum('total_hours');

        // get estimated hours spent 
        $estimated_hours = $this->employee_allocations()->sum('estimated_hours');

        // get difference between actual and estimated hours
        $remaining_hours = $estimated_hours - $actual_hours;

        // get percentage of completed hours
        $percentage = ($remaining_hours / $estimated_hours) * 100;

        return $percentage;
    }


}