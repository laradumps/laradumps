<?php

namespace LaraDumps\LaraDumps\Tests;

use Illuminate\Support\Facades\File;
use LaraDumps\LaraDumps\LaraDumpsServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected static bool $isRunningTests = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearViewsCache();
    }

    /**
     * Delete PowerGrid cached views
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
            LivewireServiceProvider::class,
            LaraDumpsServiceProvider::class,
        ];
    }
}
