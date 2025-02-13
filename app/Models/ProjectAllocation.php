<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAllocation extends Model
{
    use HasFactory;

    // Specify the table name 
    protected $table = 'project_allocation';

    // Define fillable fields
    protected $fillable = [
        'project_id',
        'employee_id',
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
     * Get the project that this allocation belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
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