<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRole extends Model
{
    use HasFactory;

    // Specify the table name
    protected $table = 'employee_role';

    // Disable Laravel's timestamps
    public $timestamps = false;

    // Define fillable fields
    protected $fillable = [
        'name',
        'employee_department_id',
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
     * Get the department this role belongs to.
     */
    public function department()
    {
        return $this->belongsTo(EmployeeDepartment::class, 'employee_department_id');
    }

    /**
     * Get all employees with this role.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'employee_role_id');
    }

    /**
     * Get the user who created this role.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('is_deleted', false);
    }

    /**
     * Get the role name with department.
     */
    public function getNameWithDepartmentAttribute()
    {
        return "{$this->name} ({$this->department->name})";
    }

    /**
     * Get the role name with the count of employees.
     */
    public function getNameWithCountAttribute()
    {
        return "{$this->name} ({$this->employees()->count()})";
    }

    /**
     * Get the full role name (department and count).
     */
    public function getFullNameAttribute()
    {
        return "{$this->name} - {$this->department->name} ({$this->employees()->count()} employees)";
    }
}
