<?php

namespace AqtIm\Laravel;

use Illuminate\Support\ServiceProvider;

class AqtimServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/aqtim.php', 'aqtim');

        $this->app->singleton(Aqtim::class, fn ($app) => new Aqtim($app['config'], $app->environment()));
    }

    public function boot(): void
    {
        $this->app->make(Aqtim::class)->mode();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/aqtim.php' => config_path('aqtim.php'),
            ], 'aqtim-config');
        }
    }
}
