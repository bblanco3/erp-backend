<?php

namespace App\Models;

use App\Enums\AssociableType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class EventAssociation extends Model
{
    use HasFactory;

    protected $table = 'event_associations';

    protected $fillable = [
        'event_id',
        'associatable_type',
        'associatable_id',
        'address_id',
        'role',
    ];

    protected $casts = [
        'associatable_type' => AssociableType::class
    ];

    /**
     * Get the event that owns the association.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the associated model (Project, Contact, Supplier, Customer, etc.).
     */
    public function associatable()
    {
        return $this->morphTo();
    }

    /**
     * Get the address associated with this association.
     */
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Validate the role based on the associatable type.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($association) {
            if (!$association->associatable_type->isValidRole($association->role)) {
                throw new InvalidArgumentException(
                    "Invalid role '{$association->role}' for associatable type '{$association->associatable_type->label()}'"
                );
            }
        });
    }

    /**
     * Scope a query to only include associations of a specific role.
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to only include associations of a specific type.
     */
    public function scopeOfType($query, AssociableType $type)
    {
        return $query->where('associatable_type', $type);
    }

    /**
     * Get the human-readable label for the associatable type.
     */
    public function getTypeLabel(): string
    {
        return $this->associatable_type->label();
    }

    /**
     * Get all valid roles for the current associatable type.
     */
    public function getValidRoles(): array
    {
        return $this->associatable_type->validRoles();
    }
}
