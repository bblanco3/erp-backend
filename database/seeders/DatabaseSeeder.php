<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a default tenant
        $tenant = Tenant::create([
            'name' => 'Default Tenant',
            'entity' => 'Default Entity',
            'is_active' => true,
        ]);

        // Create a default account
        $account = Account::create([
            'name' => 'Default Account',
            'email' => 'admin@synergy.com',
            'password' => Hash::make('password123'),
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        // Create a test user
        User::create([
            'name' => 'Test User',
            'email' => 'bebblanco3@gmail.com',
            'password' => Hash::make('password123'),
            'tenant_id' => $tenant->id,
            'account_id' => $account->id,
            'is_active' => true,
        ]);
    }
}
