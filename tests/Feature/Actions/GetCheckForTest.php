<?php

use LaraDumps\LaraDumps\Actions\GetCheckFor;

it('parses functions from .env', function (string $envKey, array $result) {
    fixtureEnv('ds_env', ['DS_CHECK_FOR' => $envKey]);

    expect(GetCheckFor::handle())->toBe($result);
})->with([
    [
        'ds,->ds1,@directiveX',
        ['@ds', '->ds', '//ds', 'ds(', '@ds1', '->ds1', '//ds1', 'ds1(', '@directiveX', '->directiveX', '//directiveX', 'directiveX('],
    ],
    [
        'ds(,ds1(,', //comma at the end
        ['@ds', '->ds', '//ds', 'ds(', '@ds1', '->ds1', '//ds1', 'ds1('],
    ],
    [
        'ds, ds1,     ds3, @directiveX', //extra space
        ['@ds', '->ds', '//ds', 'ds(', '@ds1', '->ds1', '//ds1', 'ds1(', '@ds3', '->ds3', '//ds3', 'ds3(', '@directiveX', '->directiveX', '//directiveX', 'directiveX('],
    ],
]);
