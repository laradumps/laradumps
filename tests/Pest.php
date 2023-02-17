<?php

use Dotenv\Dotenv;
use Illuminate\Support\Env;
use LaraDumps\LaraDumps\Tests\TestCase;

uses(TestCase::class)->in('Feature');

function laravel_path($path = ''): string
{
    return  str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/../vendor/orchestra/testbench-core/laravel/' . $path);
}

function fixtureEnv(string $filename, $replace = []): void
{
    $fixturePath = str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/Fixtures/');

    if (!file_exists($fixturePath . $filename)) {
        throw new \Exception(sprintf('The fixture %s does not exist', $fixturePath . $filename));
    }

    $env = Dotenv::parse(file_get_contents($fixturePath . $filename));

    $env = array_merge($env, $replace);

    foreach ($env as $key => $value) {
        setEnvKey($key, $value);
    }

    // (Dotenv::createImmutable(laravel_path()))->load();
}

#https://stackoverflow.com/questions/66775841/laravel-vlucas-phpdotenv-changing-env-values-dynamically

function setEnvKey($name, $value)
{
    $environmentRepository = Env::getRepository();

    $fn = function () use ($name, $value) {
        $fn = function () use ($name, $value) {
            $this->writer->write($name, $value);
        };
        $fn->call($this->writer);
    };
    $fn->call($environmentRepository);
}
