<?php

use LaraDumps\LaraDumps\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function getLaravelDir(): string
{
    return __DIR__ . '/../vendor/orchestra/testbench-core/laravel/';
}
