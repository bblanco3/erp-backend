<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    // Use the default mysql connection that we're switching
    protected $connection = 'mysql';

    // Specify the table name 
    protected $table = 'projects';

    // Define fillable fields to allow mass assignment
    protected $fillable = [
        'customer_id',
        'name',
        'details',
        'due_date',
        'status',
        'created_on',
        'created_by',
        'completed_on',
        'is_active',
        'is_deleted',
    ];

    // Define attributes with default values
    protected $attributes = [
        'status' => 'active',
        'is_active' => true,
        'is_deleted' => false,
    ];

    // Define casts for specific fields
    protected $casts = [
        'due_date' => 'date',
        'created_on' => 'datetime',
        'completed_on' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Disable timestamps since the table does not have `created_at` and `updated_at`
    public $timestamps = false;

    // Define appended attributes
    protected $appends = ['client'];

    // DomainEvents created
    private array $domain_events = [];
    
    /**
     * Accessor for client attribute
     */
    public function getClientAttribute()
    {
        return $this->customer?->name ?? 'N/A';
    }

    /**
     * Define relationships (if applicable).
     */

    // Example: If 'customer_id' relates to a 'Customer' model
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Example: If 'created_by' relates to a 'User' model
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Example: If 'tenant_id' relates to a 'Tenant' model
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope for active projects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('is_deleted', false);
    }

    /**
     * Scope for projects belonging to a specific tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
