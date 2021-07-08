<?php

namespace Michavie\Bearhub;

use Statamic\Statamic;
use Illuminate\Support\Facades\Artisan;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        \Michavie\Bearhub\Commands\SyncBearHubCommand::class,
    ];

    protected $widgets = [
        \Michavie\Bearhub\Widgets\BearHubWidget::class,
    ];

    protected $routes = [
        'cp'      => __DIR__.'/../routes/cp.php',
    ];

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'bearhub');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/bearhub.php' => config_path('bearhub.php'),
            ], 'bearhub');
        }

        Statamic::afterInstalled(function () {
            Artisan::call('vendor:publish --tag=bearhub');
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bearhub.php', 'bearhub');
    }
}
