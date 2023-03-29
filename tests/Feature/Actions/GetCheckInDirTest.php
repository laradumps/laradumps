<?php

use LaraDumps\LaraDumps\Actions\GetCheckInDir;

it('parses folders from .env', function (string $envKey, array $result) {
    fixtureEnv('ds_env', ['DS_CHECK_IN_DIR' => $envKey]);

    expect(GetCheckInDir::handle())->toBe($result);
})->with([
    ['app,src,routes,resources', fn () => [base_path('app'), base_path('src'), base_path('routes'), base_path('resources')]],
    ['app,          src', fn () => [base_path('app'), base_path('src')]],
    ['/user/password,src', fn () => [str_replace('/', DIRECTORY_SEPARATOR, base_path('user/password')), base_path('src')]],
]);
