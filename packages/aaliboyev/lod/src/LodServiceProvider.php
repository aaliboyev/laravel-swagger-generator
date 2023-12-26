<?php

namespace Aaliboyev\Lod;

use Aaliboyev\Lod\Services\OpenApiGenerator;
use Illuminate\Support\ServiceProvider;
use Aaliboyev\Lod\Contracts\OpenApiGenerator as OpenApiGeneratorContract;


class LodServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(OpenApiGeneratorContract::class, function ($app) {
            return new OpenApiGenerator(config('openapi.api_prefix'));
        });
        $this->mergeConfigFrom(
            __DIR__.'/config.php', 'openapi'
        );
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/aaliboyev/lod'),
        ], 'views');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'lod');
        $this->publishes([
            __DIR__.'/config.php' => config_path('openapi.php'),
        ], 'openapi-config');
    }
}
