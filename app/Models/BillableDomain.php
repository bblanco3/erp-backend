<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillableDomain extends Model
{
    use HasFactory;

    // Specify the table name
    protected $table = 'billable_domain';

    // Define fillable fields to allow mass assignment
    protected $fillable = [
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
        'is_active' => 1, 
        'is_deleted' => 0, 
    ];

    // Disable timestamps if the table does not have `created_at` and `updated_at`
    public $timestamps = false;

    // Define custom data types    
    protected $casts = [
        'created_on' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /**
     * Define relationships (if applicable).
     */

    // Example: If 'created_by' relates to a 'User' model
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessors and Mutators (optional, if you need data transformation).
     */

    // Example: Accessor for active status
    public function getIsActiveAttribute()
    {
        return $this->active == 1 ? 'Active' : 'Inactive';
    }
}
