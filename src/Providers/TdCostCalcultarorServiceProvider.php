<?php

namespace TronderData\TdCostCalcultaror\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use TronderData\TdCostCalcultaror\Http\Middleware\SetLocale;

class TdCostCalcultarorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Config
        if (file_exists(__DIR__.'/../../config/td-cost-calcultaror.php')) {
            $this->mergeConfigFrom(__DIR__.'/../../config/td-cost-calcultaror.php', 'td-cost-calcultaror');
        }
        // For backward compatibility
        if (file_exists(__DIR__.'/../../config/td-cost-calculator.php')) {
            $this->mergeConfigFrom(__DIR__.'/../../config/td-cost-calculator.php', 'td-cost-calculator');
        }
    }

    public function boot(): void
    {
        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('td-cost-calcultaror-locale', SetLocale::class);
        // For backwards compatibility
        $router->aliasMiddleware('td-cost-calculator-locale', SetLocale::class);
        
        // Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'td-cost-calcultaror');

        // Migrations
        if (is_dir(__DIR__.'/../../database/migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }

        // Routes
        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
        if (file_exists(__DIR__.'/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }
        
        // Languages
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'td-cost-calcultaror');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'td-cost-calculator'); // Bakoverkompatibilitet
        
        // Publish resources
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/td-cost-calcultaror'),
        ], 'td-cost-calcultaror-lang');
        
        // Register translation publishing with TaskHub's lang:sync command if available
        if (class_exists('\TaskHub\Lang\Commands\SyncCommand')) {
            $this->app->make('events')->listen('taskhub.lang.sync', function($event) {
                $event->registerModule('td-cost-calcultaror', __DIR__.'/../resources/lang');
            });
        }
        
        $this->publishes([
            __DIR__.'/../../config/td-cost-calculator.php' => config_path('td-cost-calculator.php'),
        ], 'td-cost-calcultaror-config');
    }
}
