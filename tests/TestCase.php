<?php

namespace Feodorpranju\Eloquent\Bitrix24\Tests;

use Feodorpranju\Eloquent\Bitrix24\Bitrix24ServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use function Orchestra\Testbench\workbench_path;

class TestCase extends OrchestraTestCase
{
    /**
     * Get application providers.
     *
     * @param Application $app
     *
     * @return array
     */
    protected function getApplicationProviders($app)
    {
        return parent::getApplicationProviders($app);
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            Bitrix24ServiceProvider::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // reset base path to point to our package's src directory
        //$app['path.base'] = __DIR__ . '/../src';


        $app['config']->set('app.key', 'ZsZewWyUJ5FsKp9lMwv4tYbNlegQilM7');

        $config = require __DIR__.'/config/database.php';
        $app['config']['database'] = array_replace_recursive($app['config']['database'], $config);
        $app['config']['bitrix24'] = require __DIR__.'/config/database.php';
    }
}
