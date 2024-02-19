<?php

namespace Genero\Sage\Polylang\Providers;

use Illuminate\Support\ServiceProvider;

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
            __DIR__ . '/../../publishes/config/polylang.php' => $this->app->configPath('polylang.php'),
        ], 'Polylang Config');

        $this->publishes([
            __DIR__ . '/../../publishes/Composers' => $this->app->path('View/Composers'),
        ], 'Polylang Composers');
    }
}
