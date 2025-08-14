<?php

namespace TronderData\TdCostCalcultaror\Provider;

use Illuminate\Support\ServiceProvider;

class TdCostCalcultarorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Config
        if (file_exists(__DIR__.'/../../config/td-cost-calculator.php')) {
            $this->mergeConfigFrom(__DIR__.'/../../config/td-cost-calculator.php', 'td-cost-calculator');
        }
    }

    public function boot(): void
    {
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
    }
}
