<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDatabase extends Command
{
    protected $signature = 'db:create';
    protected $description = 'Create the master database if it does not exist';

    public function handle()
    {
        $masterDatabase = config('database.connections.master.database');
        $masterHost = config('database.connections.master.host');
        $masterUsername = config('database.connections.master.username');
        $masterPassword = config('database.connections.master.password');

        try {
            $pdo = new \PDO(
                "mysql:host={$masterHost}",
                $masterUsername,
                $masterPassword
            );

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$masterDatabase}`");
            $this->info("Database '{$masterDatabase}' created successfully.");
        } catch (\Exception $e) {
            $this->error("Failed to create database: " . $e->getMessage());
        }
    }
}
