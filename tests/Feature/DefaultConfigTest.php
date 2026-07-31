<?php

use LaraDumps\LaraDumps\Actions\DefaultConfig;

it('merges the core and package base templates into the full schema', function () {
    $schema = DefaultConfig::toArray();

    expect($schema)->toHaveKeys(['app', 'config', 'xdebug', 'code_snippet']);

    expect($schema)->toHaveKeys(['observers', 'logs', 'slow_queries', 'extra', 'queries', 'profile']);

    expect($schema['config'])->toHaveKey('color_in_screen');

    expect($schema['observers'])->toHaveKeys(['dump', 'original_dump', 'queries', 'logs', 'profile', 'enabled_in_testing']);

    expect($schema['profile']['capture'])->toHaveKey('eloquent');
});
