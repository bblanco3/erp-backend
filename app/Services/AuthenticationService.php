<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthenticationService
{
    /**
     * Attempt to authenticate a user
     *
     * @param array $credentials
     * @return array
     */
    public function attemptLogin(array $credentials): array
    {
        try {
            // Configure the database connection
            $this->configureMasterConnection();

            // Double-check our connection settings
            Log::info('Database config:', [
                'master_config' => config('database.connections.master')
            ]);

            Log::info('Attempting authentication with connection info:', [
                'connection' => DB::connection()->getName(),
                'database' => DB::connection()->getDatabaseName(),
                'host' => config('database.connections.master.host'),
                'email' => $credentials['email']
            ]);

            // Attempt authentication with master connection
            if (!Auth::attempt($credentials)) {
                Log::info('Authentication failed');
                return [
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'status' => 401
                ];
            }

            $account = Auth::user();
            Log::info('Authentication successful', [
                'account_id' => $account->id
            ]);

            return [
                'success' => true,
                'account' => $account,
                'status' => 200
            ];

        } catch (\Exception $e) {
            Log::error('Authentication error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            report($e);
            return [
                'success' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    /**
     * Configure the master database connection
     */
    private function configureMasterConnection(): void
    {
        // Explicitly set all master connection parameters
        Config::set('database.connections.master', [
            'driver' => 'mysql',
            'host' => 'erp-db',
            'port' => 3306,
            'database' => 'master',
            'username' => 'laravel',
            'password' => 'Syn-r-g5!5laravel',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        // Set master as default connection
        Config::set('database.default', 'master');
        
        // Clear the connection cache
        DB::purge('master');
        DB::reconnect('master');

        // Configure auth to use Account model
        Config::set('auth.defaults.guard', 'web');
        Config::set('auth.guards.web.provider', 'users');
        Config::set('auth.providers.users.model', Account::class);
    }
}
