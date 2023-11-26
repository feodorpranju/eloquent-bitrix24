<?php

namespace Feodorpranju\Eloquent\Bitrix24;

use Illuminate\Support\ServiceProvider;

class Bitrix24ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
//        Model::setConnectionResolver($this->app['db']);
//
//        Model::setEventDispatcher($this->app['events']);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        // Add database driver.
        $this->app->resolving('db', function ($db) {
            $db->extend('bitrix24', function ($config, $name) {
                $config['name'] = $name;

                return new Connection($config);
            });
        });
    }
}
