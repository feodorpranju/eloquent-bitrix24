<?php

namespace Pranju\Bitrix24;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

        DB::macro('call', fn() => DB::connection()->call(...func_get_args()));
        DB::macro('cmd', fn() => DB::connection()->cmd(...func_get_args()));
        DB::macro('batch', fn() => DB::connection()->batch(...func_get_args()));
        Str::macro('dot', fn(string $string) => Str::replace('-', '.', Str::kebab($string)));
    }
}
