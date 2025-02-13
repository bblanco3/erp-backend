<?php

namespace App\Enums;

enum AssociableType: string
{
    case PROJECT = 'App\Models\Project';
    case CONTACT = 'App\Models\Contact';
    case SUPPLIER = 'App\Models\Supplier';
    case CUSTOMER = 'App\Models\Customer';
    case EMPLOYEE = 'App\Models\Employee';
    case VEHICLE = 'App\Models\Vehicle';

    /**
     * Get a human-readable label for the type.
     */
    public function label(): string
    {
        return match($this) {
            self::PROJECT => 'Project',
            self::CONTACT => 'Contact',
            self::SUPPLIER => 'Supplier',
            self::CUSTOMER => 'Customer',
            self::EMPLOYEE => 'Employee',
            self::VEHICLE => 'Vehicle',
        };
    }

    /**
     * Get valid roles for this associable type.
     */
    public function validRoles(): array
    {
        return match($this) {
            self::PROJECT => ['primary', 'related'],
            self::CONTACT => ['client', 'attendee', 'supervisor'],
            self::SUPPLIER => ['labor_source', 'material_source', 'equipment_source'],
            self::CUSTOMER => ['client', 'property_owner', 'billing'],
            self::EMPLOYEE => ['supervisor', 'worker', 'driver'],
            self::VEHICLE => ['transport', 'equipment'],
        };
    }

    /**
     * Check if a role is valid for this type.
     */
    public function isValidRole(string $role): bool
    {
        return in_array($role, $this->validRoles());
    }

    /**
     * Get the model class for this type.
     */
    public function getModelClass(): string
    {
        return $this->value;
    }
}
