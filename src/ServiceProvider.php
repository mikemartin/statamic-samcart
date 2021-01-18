<?php

namespace Mikemartin\Samcart;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
    ];
    
    public function boot()
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
