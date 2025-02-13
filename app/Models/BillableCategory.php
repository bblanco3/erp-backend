<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillableCategory extends Model
{
    use HasFactory;

    // Specify the table name (optional if it follows Laravel's naming convention)
    protected $table = 'billable_category';

    // Define fillable fields to allow mass assignment
    protected $fillable = [
        'billable_domain_id',
        'rank',
        'code',
        'name',
        'description',
        'created_by',
        'created_on',
        'is_active',
        'is_deleted',
    ];

    // Define attributes with default values
    protected $attributes = [
        'is_active' => 1, // Default value for the is_active field
        'is_deleted' => 0, // Default value for the is_deleted field
    ];

    // Disable timestamps if the table does not have `created_at` and `updated_at`
    public $timestamps = false;
    
    // Define custom data types    
    protected $casts = [
        'created_on' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Relationship to BillableDomain
    public function billable_domain()
    {
        return $this->belongsTo(BillableDomain::class, 'billable_domain_id');
    }

    // Example: If 'created_by' relates to a 'User' model
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessors and Mutators.
     */

    // Example: Accessor for is_active status
    public function getIsActiveAttribute($value)
    {
        return $value == 1 ? 'Active' : 'Inactive';
    }
}
