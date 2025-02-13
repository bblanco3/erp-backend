<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    use HasFactory;

    // Define address types as constants
    public const TYPE_RESIDENTIAL = 'residential';
    public const TYPE_COMMERCIAL = 'commercial';
    public const TYPE_INDUSTRIAL = 'industrial';
    public const TYPE_POSTAL = 'postal';

    public const TYPES = [
        self::TYPE_RESIDENTIAL,
        self::TYPE_COMMERCIAL,
        self::TYPE_INDUSTRIAL,
        self::TYPE_POSTAL,
    ];

    // Define label types as constants
    public const LABEL_HOME = 'home';
    public const LABEL_WORK = 'work';
    public const LABEL_BILLING = 'billing';
    public const LABEL_SHIPPING = 'shipping';
    public const LABEL_SITE = 'site';
    public const LABEL_OTHER = 'other';

    public const LABELS = [
        self::LABEL_HOME,
        self::LABEL_WORK,
        self::LABEL_BILLING,
        self::LABEL_SHIPPING,
        self::LABEL_SITE,
        self::LABEL_OTHER,
    ];

    // Specify the table name 
    protected $table = 'addresses';

    // Define fillable fields
    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'type',
        'label',
        'street_address',
        'unit_number',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'is_primary',
        'is_verified',
        'verified_at',
        'created_on',
        'created_by',
        'is_active',
        'is_deleted'
    ];

    // Define attributes with default values
    protected $attributes = [
        'country' => 'Mexico',  // Default country
        'is_primary' => false,
        'is_verified' => false,
        'is_active' => true,
        'is_deleted' => false,
    ];

    // Disable Laravel's timestamps
    public $timestamps = false;

    // Define custom data types    
    protected $casts = [
        'created_on' => 'datetime',
        'verified_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Validation rules
    public static $rules = [
        'addressable_type' => 'required|string',
        'addressable_id' => 'required|integer',
        'type' => 'required|string|in:' . self::TYPE_RESIDENTIAL . ',' . self::TYPE_COMMERCIAL . ',' . self::TYPE_INDUSTRIAL . ',' . self::TYPE_POSTAL,
        'label' => 'required|string|in:' . self::LABEL_HOME . ',' . self::LABEL_WORK . ',' . self::LABEL_BILLING . ',' . self::LABEL_SHIPPING . ',' . self::LABEL_SITE . ',' . self::LABEL_OTHER,
        'street_address' => 'required|string|max:255',
        'unit_number' => 'nullable|string|max:50',
        'city' => 'required|string|max:100',
        'state' => 'required|string|max:100',
        'postal_code' => 'required|string|max:20',
        'country' => 'required|string|max:100',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'nullable|date',
        'created_on' => 'required|date',
        'created_by' => 'required|exists:users,id',
    ];

    /**
     * Get the parent addressable model (Employee, Customer, etc.).
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this address.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include primary addresses.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope a query to only include verified addresses.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include active addresses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_deleted', false);
    }

    /**
     * Scope a query to only include addresses of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the full address as a string.
     */
    public function getFullAddressAttribute()
    {
        $parts = [
            $this->street_address,
            $this->unit_number,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ];

        return implode(', ', array_filter($parts));
    }

    /**
     * Get the coordinates as a [lat, lng] array.
     */
    public function getCoordinatesAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return [$this->latitude, $this->longitude];
        }
        return null;
    }
}
