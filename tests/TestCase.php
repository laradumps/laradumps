<?php

namespace LaraDumps\LaraDumps\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use LaraDumps\LaraDumps\Actions\SendPayload;
use LaraDumps\LaraDumps\LaraDumpsServiceProvider;
use LaraDumps\LaraDumps\Tests\Actions\TestDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected static bool $isRunningTests = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearViewsCache();

        TestDatabase::up();

        $sendPayload = \Mockery::mock('overload:' . SendPayload::class);

        $sendPayload->shouldReceive('handle')
                ->andReturn(md5(uniqid(rand(), true)));
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('laradumps.sleep', null);
        $app['config']->set('laradumps.host', '127.0.0.1');
        $app['config']->set('laradumps.port', 8181);

        $app['config']->set('app.key', 'base64:RygUQvaR926QuH4d5G6ZDf9ToJEEeO2p8qDSCq6emPk=');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => env('DB_DRIVER'),
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_DATABASE'),
            'prefix'   => '',
        ]);
    }

    /**
     * Delete cached views
     *
     * @return void
     */
    protected function clearViewsCache(): void
    {
        if (self::$isRunningTests === true) {
            return;
        }

        $viewsFolder = base_path() . '/resources/views/vendor/laradumps/';

        $viewsFolderPath = str_replace('/', DIRECTORY_SEPARATOR, $viewsFolder);

        File::deleteDirectory($viewsFolderPath);

        self::$isRunningTests = true;
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaraDumpsServiceProvider::class,
        ];
    }
}
