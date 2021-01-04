<?php

namespace Habib\Hosting\Providers;

use Habib\Hosting\Base\Hosting;
use Habib\Hosting\Host\Cpanel\Cpanel;
use Habib\Hosting\Support\Manager\HostingManger;

class HostingServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('hosting', function ($app) {
            return new HostingManger($app);
        });

        $this->app->singleton(Cpanel::class, function () {
            return new Cpanel(
                $this->app->make('hosting')->getDefaultDriver()
            );
        });
    }

    public function boot()
    {

    }
}
