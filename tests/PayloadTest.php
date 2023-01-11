<?php

use Illuminate\Support\Str;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{DumpPayload, MailablePayload, ModelPayload, TableV2Payload};
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
})->skip('v2');;

it('should return the correct payload to mailable', function () {
    $mailable = new TestMail();

    $notificationId = Str::uuid()->toString();

    $trace      = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps      = new LaraDumps($notificationId, trace: $trace);
    $payload        = $laradumps->send(new MailablePayload($mailable));

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('mailable')
        ->and($payload['content']['subject'])
        ->toContain('An test mail')
        ->and($payload['content']['from'][0]['email'])
        ->toContain('from@example.com')
        ->and($payload['content']['to'][0]['email'])
        ->toContain('to@example.com');
})->group('mailable');

it('should return the correct payload to table-v2', function () {
    $data = [
        'Name'  => 'Anand Pilania',
        'Email' => 'pilaniaanand@gmail.com',
        'Stack' => [
            'Laravel',
            'Flutter',
        ],
    ];

    $notificationId = Str::uuid()->toString();

    $trace      = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps      = new LaraDumps($notificationId, trace: $trace);
    $payload        = $laradumps->send(new TableV2Payload($data));

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('table-v2')
        ->and($payload['content']['values']['Name'])
        ->toContain('Anand Pilania')
        ->and($payload['content']['values']['Email'])
        ->toContain('pilaniaanand@gmail.com')
        ->and($payload['content']['values']['Stack'])
        ->toContain('Laravel');
})->group('table-v2');
