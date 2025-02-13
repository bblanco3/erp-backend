<?php

namespace App\Providers;

use App\Services\TenantManager;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TenantManager::class, function () {
            return new TenantManager();
        });

        $this->app->alias(TenantManager::class, 'tenant.manager');
    }

    public function boot()
    {
        //
    }
}
