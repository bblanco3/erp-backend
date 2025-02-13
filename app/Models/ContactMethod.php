<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContactMethod extends Model
{
    use HasFactory;

    // Define contact method types as constants
    public const METHOD_EMAIL = 'email';
    public const METHOD_PHONE = 'phone';
    public const METHOD_MOBILE = 'mobile';
    public const METHOD_FAX = 'fax';

    public const METHODS = [
        self::METHOD_EMAIL,
        self::METHOD_PHONE,
        self::METHOD_MOBILE,
        self::METHOD_FAX,
    ];

    // Define label types as constants
    public const LABEL_WORK = 'work';
    public const LABEL_HOME = 'home';
    public const LABEL_PERSONAL = 'personal';
    public const LABEL_OTHER = 'other';

    public const LABELS = [
        self::LABEL_WORK,
        self::LABEL_HOME,
        self::LABEL_PERSONAL,
        self::LABEL_OTHER,
    ];

    // Specify the table name 
    protected $table = 'contact_methods';

    // Define fillable fields
    protected $fillable = [
        'contactable_type', // stores the model type
        'contactable_id',   // stores the model ID
        'method',          // stores the contact method
        'value',
        'label',
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
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Validation rules
    public static $rules = [
        'contactable_type' => 'required|string',
        'contactable_id' => 'required|integer',
        'method' => 'required|string|in:' . self::METHOD_EMAIL . ',' . self::METHOD_PHONE . ',' . self::METHOD_MOBILE . ',' . self::METHOD_FAX,
        'value' => 'required|string',
        'label' => 'required|string|in:' . self::LABEL_WORK . ',' . self::LABEL_HOME . ',' . self::LABEL_PERSONAL . ',' . self::LABEL_OTHER,
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'nullable|date',
        'created_on' => 'required|date',
        'created_by' => 'required|exists:users,id',
    ];

    /**
     * Get the parent contactable model (Employee, Customer, etc.).
     */
    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this contact method.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include primary contact methods.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope a query to only include verified contact methods.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include active contact methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_deleted', false);
    }

    /**
     * Scope a query to only include contact methods of a specific method.
     */
    public function scopeOfMethod($query, string $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Get all email contacts.
     */
    public function scopeEmails($query)
    {
        return $query->where('method', self::METHOD_EMAIL);
    }

    /**
     * Get all phone contacts (including mobile and fax).
     */
    public function scopePhones($query)
    {
        return $query->whereIn('method', [self::METHOD_PHONE, self::METHOD_MOBILE, self::METHOD_FAX]);
    }

    /**
     * Format the value based on the contact method.
     */
    public function getFormattedValueAttribute()
    {
        switch ($this->method) {
            case self::METHOD_PHONE:
            case self::METHOD_MOBILE:
            case self::METHOD_FAX:
                // Format phone numbers (you can implement your own formatting logic)
                return $this->value;
            
            case self::METHOD_EMAIL:
                return strtolower($this->value);
            
            default:
                return $this->value;
        }
    }
}
