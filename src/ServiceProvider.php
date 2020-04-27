<?php

namespace Mikemartin\Samcart;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
    ];
}
