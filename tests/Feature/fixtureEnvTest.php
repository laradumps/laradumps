<?php

it('changes the env on the fly', function () {
    $name = fake()->name;

    fixtureEnv('ds_env', ['name' => $name]);

    expect(env('name'))->toBe($name);
});
