<?php

test('config page loads', function () {
    $this->get(route('laradumps.index'))->assertOk();
});

test('config does not load in production', function () {
    app()->detectEnvironment(function () {
        return 'production';
    });

    $this->get(route('laradumps.index'))->assertStatus(404);
});
