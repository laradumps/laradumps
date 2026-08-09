<?php

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

it('parses the config yaml base file', function () {
    expect(Yaml::parseFile('src/Commands/laradumps-base.yaml'))
        ->not->toThrow(ParseException::class)
        ->toBe(
            [
                'observers' => [
                    'dump' => false,
                    'original_dump' => true,
                    'auto_invoke_app' => false,
                    'enabled_in_testing' => false,
                    'queries' => false,
                    'slow_queries' => false,
                    'mail' => false,
                    'logs' => true,
                    'http' => false,
                    'jobs' => false,
                    'commands' => false,
                    'scheduled_commands' => false,
                    'gate' => false,
                    'cache' => false,
                    'brain' => false,
                    'profiler' => false,
                ],
                'logs' => [
                    'info' => true,
                    'warning' => true,
                    'emergency' => true,
                    'alert' => false,
                    'debug' => true,
                    'error' => true,
                    'critical' => true,
                    'notice' => true,
                    'vendor' => true,
                    'deprecated_message' => true,
                    'boost_info' => false,
                ],
                'slow_queries' => [
                    'threshold_in_ms' => 500,
                ],
                'extra' => [
                    'context' => false,
                ],
                'queries' => [
                    'explain' => false,
                ],
                'profiler' => [
                    'auto_middleware' => false,
                    'max_entries' => 300,
                    'xhprof' => true,
                    'capture' => [
                        'app' => true,
                        'events' => true,
                        'queries' => true,
                        'eloquent' => true,
                        'views' => true,
                        'controller' => true,
                        'http' => true,
                        'cache' => true,
                        'jobs' => true,
                        'method' => true,
                    ],
                ],
            ],
        );
});
