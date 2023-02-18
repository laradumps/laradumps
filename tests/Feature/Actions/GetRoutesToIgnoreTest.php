<?php

use LaraDumps\LaraDumps\Actions\GetRoutesToIgnore;

it('parses functions from .env', function (string $envKey, array $result) {
    fixtureEnv('ds_env', ['DS_IGNORE_ROUTES' => $envKey]);

    expect(GetRoutesToIgnore::handle())->toBe($result);
})->with([
    ['/admin/secret,debugbar,ignition,horizon,livewire', ['/admin/secret', 'debugbar', 'ignition', 'horizon', 'livewire']],
    ['/admin/secret         ,    debugbar, ignition , horizon , livewire', ['/admin/secret', 'debugbar', 'ignition', 'horizon', 'livewire']],

]);
