<?php

use Illuminate\Support\Str;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{DumpPayload, ModelPayload};
use LaraDumps\LaraDumps\Support\Dumper;
use LaraDumps\LaraDumps\Tests\Models\Dish;

it('should return the correct payload to dump', function () {
    $args   = [
        'name' => 'Luan',
    ];

    $args           = Dumper::dump($args);
    $notificationId = Str::uuid()->toString();

    $backtrace      = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps      = new LaraDumps($notificationId, backtrack: $backtrace);
    $payload        = $laradumps->send(new DumpPayload($args));

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('dump')
        ->ideHandle->toMatchArray([
            'handler' => 'phpstorm://open?url=file://Test&line=1',
            'path'    => 'Test',
            'line'    => 1,
        ])
        ->and($payload['content']['dump'])
        ->toContain(
            '<span class=sf-dump-key>name</span>',
            '<span class=sf-dump-str title="4 characters">Luan</span>'
        );
});

it('should return the correct payload to model', function () {
    $dish = Dish::query()->first();

    $notificationId = Str::uuid()->toString();

    $backtrace      = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps      = new LaraDumps($notificationId, backtrack: $backtrace);
    $payload        = $laradumps->send(new ModelPayload($dish));

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('model')
        ->ideHandle->toMatchArray([
            'handler' => 'phpstorm://open?url=file://Test&line=1',
            'path'    => 'Test',
            'line'    => 1,
        ])
        ->and($payload['content']['relations'])
        ->toMatchArray([])
        ->and($payload['content']['className'])
        ->toBe('LaraDumps\LaraDumps\Tests\Models\Dish')
        ->and($payload['content']['attributes'])
        ->toContain(
            '<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>1</span>',
            '<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="14 characters">Pastel de Nata</span>',
            '<span class=sf-dump-key>active</span>" => <span class=sf-dump-num>1</span>',
        );
});
