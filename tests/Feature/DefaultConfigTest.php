<?php

use LaraDumps\LaraDumps\Actions\DefaultConfig;

it('merges the core and package base templates into the full schema', function () {
    $schema = DefaultConfig::toArray();

    expect($schema)->toHaveKeys(['app', 'config', 'xdebug', 'code_snippet'])
        ->and($schema)->toHaveKeys(['observers', 'logs', 'slow_queries', 'extra', 'queries', 'profiler'])
        ->and($schema['config'])->toHaveKey('color_in_screen')
        ->and($schema['observers'])->toHaveKeys(['dump', 'original_dump', 'queries', 'logs', 'profiler', 'enabled_in_testing'])
        ->and($schema['profiler']['capture'])->toHaveKey('eloquent');
});
