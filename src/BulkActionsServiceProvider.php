<?php

namespace FriendsOfCat\BulkActions;

use Illuminate\Support\ServiceProvider;

class BulkActionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bulk-actions.php', 'bulk-actions');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->publishes([
                __DIR__ . '/../config/bulk-actions.php' => $this->app->configPath('bulk-actions.php'),
            ], 'bulk-actions-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'bulk-actions-migrations');
        }
    }
}
