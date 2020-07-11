<?php

namespace Bavix\Entry;

use Bavix\Entry\Commands\BulkWrite;
use Bavix\Entry\Services\BulkService;
use Illuminate\Support\ServiceProvider;

class EntryServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([BulkWrite::class]);
        if (function_exists('config_path')) {
            $this->publishes([
                dirname(__DIR__) . '/config/config.php' => config_path('entry.php'),
            ], 'laravel-entry-config');
        }
    }

    /**
     * Register our singleton's
     */
    public function register(): void
    {
        $this->mergeConfigFrom(\dirname(__DIR__) . '/config/config.php', 'entry');
        $this->app->singleton(BulkService::class);
    }

}
