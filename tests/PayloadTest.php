<?php

use Illuminate\Support\Str;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{DumpPayload, MailablePayload, ModelPayload};
use LaraDumps\LaraDumps\Support\Dumper;
use LaraDumps\LaraDumps\Tests\Mail\TestMail;
use LaraDumps\LaraDumps\Tests\Models\Dish;

it('should return the correct payload to dump', function () {
    $args   = [
        'name' => 'Luan',
    ];

    $args           = Dumper::dump($args);
    $notificationId = Str::uuid()->toString();

    $trace      = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps      = new LaraDumps(notificationId: $notificationId, trace: $trace);
    $payload        = $laradumps->send(new DumpPayload($args));

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('dump')
        ->ideHandle->toMatchArray([
            'handler' => 'phpstorm://open?file=Test&line=1',
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

    $trace      = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps      = new LaraDumps($notificationId, trace: $trace);
    $payload        = $laradumps->send(new ModelPayload($dish));

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('model')
        ->ideHandle->toMatchArray([
            'handler' => 'phpstorm://open?file=Test&line=1',
            'path'    => 'Test',
            'line'    => 1,
        ])
        ->and($payload['content']['relations'])
        ->toMatchArray([])
        ->and($payload['content']['className'])
        ->toBe('LaraDumps\LaraDumps\Tests\Models\Dish')
        ->and($payload['content']['attributes'])
        ->toContain(
            '<span class=sf-dump-key>id</span>',
            '<span class=sf-dump-key>name</span>',
            '<span class=sf-dump-key>active</span>',
        );
});

it('should return the correct payload to mailable table', function () {
    $mailable = new TestMail();

    $notificationId = Str::uuid()->toString();

    $trace      = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps      = new LaraDumps($notificationId, trace: $trace);
    $payload        = $laradumps->send(MailablePayload::forMailableTable($mailable));

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('table')
        ->and($payload['content']['label'])
        ->toBe('Mailable')
        ->and($payload['content']['values'][0]['value'])
        ->toContain('test mail')
        ->and($payload['content']['values'][1]['value'][0])
        ->toContain('from@example.com')
        ->and($payload['content']['values'][2]['value'][0])
        ->toContain('to@example.com');
})->group('mailable');

it('should return the correct payload to mailable preview', function () {
    $mailable = new TestMail();

    $notificationId = Str::uuid()->toString();

    $trace      = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps      = new LaraDumps($notificationId, trace: $trace);
    $payload        = $laradumps->send(MailablePayload::forMailable($mailable));

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('dump')
        ->and($payload['content']['dump'])
        ->toContain('test mail');
})->group('mailable');
