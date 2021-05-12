<?php

namespace Michavie\Bearhub;

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

    public function boot()
    {
        parent::boot();
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'bearhub');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bearhub.php', 'bearhub');
    }
}
