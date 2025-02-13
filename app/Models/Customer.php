<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    // Entity type constants
    public const ENTITY_TYPE_INDIVIDUAL = 'individual';
    public const ENTITY_TYPE_COMPANY = 'company';

    // Specify the table name
    protected $table = 'customer';

    // Set primary key
    protected $primaryKey = 'id';

    // Disable Laravel's timestamps
    public $timestamps = false;

    // Define fillable fields
    protected $fillable = [
        'name',
        'nickname',
        'entity_type',
        'created_on',
        'created_by',
        'is_active',
        'is_deleted',
    ];

    // Define attributes with default values
    protected $attributes = [
        'is_active' => true,
        'is_deleted' => false,
        'entity_type' => self::ENTITY_TYPE_INDIVIDUAL
    ];

    // Define custom data types
    protected $casts = [
        'created_on' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    // Define appends for accessors
    protected $appends = ['display_name', 'entity_details'];

    /**
     * Get the contact methods for this customer.
     */
    public function contact_methods()
    {
        return $this->morphMany(ContactMethod::class, 'contactable');
    }

    /**
     * Get the addresses for this customer.
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the contacts associated with this customer.
     */
    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    /**
     * Get the user who created this customer.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the customer's primary email.
     */
    public function primary_email()
    {
        return $this->contact_methods()
            ->where('method', ContactMethod::METHOD_EMAIL)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get the customer's primary phone.
     */
    public function primary_phone()
    {
        return $this->contact_methods()
            ->where('method', ContactMethod::METHOD_PHONE)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get the customer's primary address.
     */
    public function primary_address()
    {
        return $this->addresses()
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Scope a query to only include active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include individual customers.
     */
    public function scopeIndividuals($query)
    {
        return $query->where('entity_type', self::ENTITY_TYPE_INDIVIDUAL);
    }

    /**
     * Scope a query to only include company customers.
     */
    public function scopeCompanies($query)
    {
        return $query->where('entity_type', self::ENTITY_TYPE_COMPANY);
    }

    /**
     * Check if the customer is a company.
     */
    public function isCompany(): bool
    {
        return $this->entity_type === self::ENTITY_TYPE_COMPANY;
    }

    /**
     * Check if the customer is an individual.
     */
    public function isIndividual(): bool
    {
        return $this->entity_type === self::ENTITY_TYPE_INDIVIDUAL;
    }

    /**
     * Get the display name based on entity type and nickname.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->nickname) {
            return $this->nickname;
        }

        return $this->name;
    }

    /**
     * Get entity details based on type.
     */
    public function getEntityDetailsAttribute(): array
    {
        return [
            'type' => $this->entity_type,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'display_name' => $this->display_name,
        ];
    }

    /**
     * Set the entity type and ensure proper field usage.
     */
    public function setEntityType(string $type): void
    {
        if (!in_array($type, [self::ENTITY_TYPE_INDIVIDUAL, self::ENTITY_TYPE_COMPANY])) {
            throw new \InvalidArgumentException('Invalid entity type');
        }

        $this->entity_type = $type;
        $this->save();
    }

    /**
     * Convert from individual to company.
     */
    public function convertToCompany(string $companyName): void
    {
        $this->name = $companyName;
        $this->entity_type = self::ENTITY_TYPE_COMPANY;
        $this->save();
    }

    /**
     * Convert from company to individual.
     */
    public function convertToIndividual(string $fullname): void
    {
        $this->name = $fullname;
        $this->entity_type = self::ENTITY_TYPE_INDIVIDUAL;
        $this->save();
    }

    /**
     * Scope a query to only include non-deleted customers.
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }
}
