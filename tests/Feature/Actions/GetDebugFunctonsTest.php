<?php

use LaraDumps\LaraDumps\Actions\GetDebugFunctons;

it('parses functions from .env', function (string $envKey, array $result) {
    fixtureEnv('ds_env', ['DS_DEBUG_FUNCTIONS' => $envKey]);

    expect(GetDebugFunctons::handle())->toBe($result);
})->with([
    ['ds(,ds1(', ['ds(', 'ds1(']],
    ['ds,ds1', ['ds(', 'ds1(']],
    ['ds, ds1,     ds3', ['ds(', 'ds1(', 'ds3(']],
]);
