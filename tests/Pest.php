<?php

use LaraDumps\LaraDumps\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function getLaravelDir(): string
{
    return __DIR__ . '/../vendor/orchestra/testbench-core/laravel/';
}

function requiresLaravel9()
{
    if (version_compare(app()->version(), '9.2.0', '<')) {
        test()->markTestSkipped('This test requires Laravel 9.2');
    }

    return test();
}
