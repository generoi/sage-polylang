<?php

namespace Genero\Sage\Polylang\Providers;

use Roots\Acorn\ServiceProvider;

class PolylangServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/polylang.php' => $this->app->configPath('polylang.php'),
        ], 'config');
    }
}
