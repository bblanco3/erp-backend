<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    // Specify the table name
    protected $table = 'contacts';

    // Set primary key
    protected $primaryKey = 'id';

    // Disable Laravel's timestamps
    public $timestamps = false;

    // Define fillable fields
    protected $fillable = [
        'contactable_id',
        'contactable_type',
        'nickname',
        'name',
        'role',
        'position',
        'department',
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
        'is_active' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    /**
     * Get the parent contactable model (customer or supplier).
     */
    public function get_contactable()
    {
        return $this->morphTo();
    }

    /**
     * Get the contact methods for this contact.
     */
    public function contact_methods()
    {
        return $this->morphMany(ContactMethod::class, 'contactable');
    }

    /**
     * Get the addresses for this contact.
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the user who created this contact.
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'createdby');
    }

    /**
     * Get the contact's primary email.
     */
    public function primary_email()
    {
        return $this->contact_methods()
            ->where('method', ContactMethod::METHOD_EMAIL)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get the contact's primary phone.
     */
    public function primary_phone()
    {
        return $this->contact_methods()
            ->where('method', ContactMethod::METHOD_PHONE)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get the contact's primary address.
     */
    public function primary_address()
    {
        return $this->addresses()
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Scope a query to only include active contacts.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get the contact's display name with position.
     */
    public function getDisplayWithPositionAttribute()
    {
        if ($this->position) {
            return "{$this->display} ({$this->position})";
        }
        return $this->display;
    }

    /**
     * Get the contact's full display information.
     */
    public function getFullDisplayAttribute()
    {
        $parts = [$this->display];
        
        if ($this->position) {
            $parts[] = $this->position;
        }
        
        if ($this->department) {
            $parts[] = $this->department;
        }

        if ($this->fullname && $this->fullname !== $this->display) {
            $parts[] = $this->fullname;
        }
        
        // Add parent organization name
        if ($this->contactable) {
            $parts[] = "at {$this->contactable->display}";
        }
        
        return implode(' - ', $parts);
    }
}
