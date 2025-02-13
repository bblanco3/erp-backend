<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmployeeAllocation extends Model
{
    use HasFactory;

    // Define role types as constants
    public const ROLE_TYPES = [
        'sales_agent',
        'project_manager',
        'developer',
        'team_lead',
        'qa_engineer'
    ];

    // Validation rules
    public static $rules = [
        'employee_id' => 'required|exists:employee,id',
        'allocated_class' => 'required|string',
        'allocated_class_id' => 'required|integer',
        'role_type' => 'required|string|in:' . self::ROLE_TYPES,
        'created_by' => 'required|exists:users,id',
    ];

    // Specify the table name 
    protected $table = 'employee_allocation';

    // Define fillable fields
    protected $fillable = [
        'employee_id',
        'allocated_class',
        'allocated_class_id',
        'role_type',
        'created_on',
        'created_by',
        'is_active',
        'is_deleted'
    ];

    // Define attributes with default values
    protected $attributes = [
        'is_active' => 1,
        'is_deleted' => 0,
    ];

    // Disable timestamps
    public $timestamps = false;

    // Define custom data types    
    protected $casts = [
        'created_on' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /**
     * Get the parent allocatable model (Project, ProjectSegment, etc.).
     */
    public function allocated_model(): MorphTo
    {
        return $this->morphTo('allocated_class', 'allocated_class', 'allocated_class_id');
    }

    /**
     * Get the employee assigned in this allocation.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the user who created this allocation.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
