<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDepartment extends Model
{
    use HasFactory;

    // Specify the table name
    protected $table = 'employee_department';

    // Disable Laravel's timestamps
    public $timestamps = false;

    // Define fillable fields
    protected $fillable = [
        'name',
        'description',
        'created_on',
        'created_by',
        'is_active',
        'is_deleted'
    ];

    // Define attributes with default values
    protected $attributes = [
        'is_active' => 1,
        'is_deleted' => 0
    ];

    // Define custom data types
    protected $casts = [
        'created_on' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    /**
     * Get all roles in this department.
     */
    public function roles()
    {
        return $this->hasMany(EmployeeRole::class, 'employee_department_id');
    }

    /**
     * Get all employees in this department through roles.
     */
    public function employees()
    {
        return $this->hasManyThrough(
            Employee::class,
            EmployeeRole::class,
            'employee_department_id', // Foreign key on EmployeeRole table
            'employee_role_id', // Foreign key on Employee table
            'id', // Local key on EmployeeDepartment table
            'id'  // Local key on EmployeeRole table
        );
    }

    /**
     * Get the user who created this department.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('is_deleted', false);
    }

    /**
     * Get the department name with the count of employees.
     */
    public function getNameWithCountAttribute()
    {
        return "{$this->name} ({$this->employees()->count()} employees)";
    }

    /**
     * Get the department name with the count of roles.
     */
    public function getNameWithRolesAttribute()
    {
        return "{$this->name} ({$this->roles()->count()} roles)";
    }

    /**
     * Get the full department name with both counts.
     */
    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->roles()->count()} roles, {$this->employees()->count()} employees)";
    }
}
