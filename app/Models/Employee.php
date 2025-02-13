<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Employee extends Model
{
    use HasFactory;

    // Specify the table name
    protected $table = 'employees';

    // Primary key
    protected $primaryKey = 'emp';

    // Disable Laravel's timestamps
    public $timestamps = false;

    // Define fillable fields
    protected $fillable = [
        'fullname',
        'employee_role_id',
        'date_of_birth',
        'pay_type',
        'image',
        'color',
        'is_active',
        'hired_date',
        'resigned_date',
        'created_on',
        'created_by',
        'is_cash_only',
        'is_cash_overtime',
        'is_citizen',
        'is_felon'
    ];

    // Define attributes with default values
    protected $attributes = [
        'is_active' => 0,
        'color' => '#CBCBCB',
        'is_cash_only' => 0,
        'is_cash_overtime' => 0
    ];

    // Define custom data types
    protected $casts = [
        'date_of_birth' => 'date',
        'hired_date' => 'date',
        'resigned_date' => 'date',
        'created_on' => 'datetime',
        'is_active' => 'boolean',
        'is_cash_only' => 'boolean',
        'is_cash_overtime' => 'boolean',
        'is_citizen' => 'boolean',
        'is_felon' => 'boolean'
    ];

    /**
     * Get the employee role that this employee belongs to.
     */
    public function employee_role()
    {
        return $this->belongsTo(EmployeeRole::class, 'employee_role_id');
    }

    /**
     * Get the user who created this employee.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all contact methods for this employee.
     */
    public function contact_methods(): MorphMany
    {
        return $this->morphMany(ContactMethod::class, 'contactable');
    }

    /**
     * Get all addresses for this employee.
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get all project allocations for this employee.
     */
    public function project_allocations()
    {
        return $this->hasMany(EmployeeAllocation::class, 'employee_id');
    }

    /**
     * Get the primary email for this employee.
     */
    public function primary_email()
    {
        return $this->contact_methods()
            ->where('method', ContactMethod::METHOD_EMAIL)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get the primary phone for this employee.
     */
    public function primary_phone()
    {
        return $this->contact_methods()
            ->whereIn('method', [ContactMethod::METHOD_PHONE, ContactMethod::METHOD_MOBILE])
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get the primary address for this employee.
     */
    public function primary_address()
    {
        return $this->addresses()
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Scope a query to only include active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include employees who haven't resigned.
     */
    public function scopeCurrentlyEmployed($query)
    {
        return $query->whereNull('resigned_date');
    }

    /**
     * Get the employee's full name with their ID.
     */
    public function getFullNameWithIdAttribute()
    {
        return "{$this->fullname} ({$this->emp})";
    }
}