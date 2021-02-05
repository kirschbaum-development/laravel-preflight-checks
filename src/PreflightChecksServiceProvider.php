<?php

namespace Kirschbaum\PreflightChecks;

use Illuminate\Support\ServiceProvider;
use Kirschbaum\PreflightChecks\Commands\PreflightCheckCommand;

class PreflightChecksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/preflight_checks.php' => $this->publishPath('preflight_checks.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                PreflightCheckCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/preflight_checks.php',
            'preflight_checks'
        );
    }

    private function publishPath($configFile)
    {
        if (function_exists('config_path')) {
            return config_path($configFile);
        } else {
            return base_path('config/' . $configFile);
        }
    }
}
