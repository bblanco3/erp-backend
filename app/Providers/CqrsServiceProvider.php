<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\CQRS\CommandBus;
use App\CQRS\QueryBus;
use App\CQRS\Commands\ProjectCommand;
use App\CQRS\Queries\ProjectQuery;
use App\CQRS\Handlers\Commands\ProjectCommandHandler;
use App\CQRS\Handlers\Queries\ProjectQueryHandler;

class CqrsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CommandBus::class, function ($app) {
            $bus = new CommandBus();
            
            // Register command handlers
            $bus->register(ProjectCommand::class, ProjectCommandHandler::class);
            
            return $bus;
        });

        $this->app->singleton(QueryBus::class, function ($app) {
            $bus = new QueryBus();
            
            // Register query handlers
            $bus->register(ProjectQuery::class, ProjectQueryHandler::class);
            
            return $bus;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
