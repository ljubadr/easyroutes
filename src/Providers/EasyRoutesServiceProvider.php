<?php

namespace Ljubadr\EasyRoutes\Providers;

use Illuminate\Support\ServiceProvider;

class EasyRoutesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // https://laravel.com/docs/5.2/packages
        $path = realpath(__DIR__.'/..');

        // register views
        $this->loadViewsFrom($path.'/views', 'easyroutes');

        // register routes
        if (! $this->app->routesAreCached()) {
            require $path.'/routes/web.php';
        }

        // php artisan vendor:publish --provider="Ljubadr\EasyRoutes\EasyRoutesServiceProvider" --tag="config"
        $this->publishes([
            $path.'/config/easyroutes.php' => config_path('easyroutes.php'),
        ], 'config'); // 'config' tag


        // php artisan vendor:publish --provider="Ljubadr\EasyRoutes\EasyRoutesServiceProvider" --tag="assets"
        $this->publishes([
            $path.'/dist' => public_path('vendor/easyroutes'),
        ], 'assets'); // 'public' tag
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $path = realpath(__DIR__.'/..');

        // register controller
        $this->app->make('Ljubadr\EasyRoutes\Controllers\EasyRoutesController');

        // merge default config with user config
        $this->mergeConfigFrom(
             $path.'/config/easyroutes.php', 'easyroutes'
         );
    }
}
