<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Supplier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define the morph map to use model names instead of full class names
        Relation::morphMap([
            'Account' => Account::class,
            'Customer' => Customer::class,
            'Supplier' => Supplier::class,
        ]);
    }
}
