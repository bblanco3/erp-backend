<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTenantDatabase extends Command
{
    protected $signature = 'tenant:create-database {tenant_name}';
    protected $description = 'Create a new tenant database and grant necessary permissions';

    public function handle()
    {
        try {
            // Switch to master database for creating new tenant
            DB::statement("USE master");

            // Create the tenant database
            DB::statement("CREATE DATABASE IF NOT EXISTS `tenant_db`");

            // Grant permissions to Laravel user
            DB::statement("GRANT ALL PRIVILEGES ON `tenant_db`.* TO 'laravel'@'%'");
            
            // Refresh privileges
            DB::statement('FLUSH PRIVILEGES');

            $this->info("Successfully created tenant database and granted permissions");
            
            // Run migrations on the tenant database
            $this->call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true
            ]);
        } catch (\Exception $e) {
            $this->error("Failed to create tenant database: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
