<?php

use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\Payloads\{MailablePayload, MarkdownPayload, ModelPayload};
use LaraDumps\LaraDumps\Tests\Models\Dish;
use LaraDumps\LaraDumps\Tests\Support\Classes\TestMail;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, TableV2Payload};
use LaraDumps\LaraDumpsCore\Support\Dumper;
use Ramsey\Uuid\Uuid;

beforeEach(function () {
    putenv("DS_RUNNING_IN_TESTS=true");
});

it('should return the correct payload to dump', function () {
    fixtureEnv('ds_env');

    $args = [
        'name' => 'Luan',
    ];

    [$args, $id]    = Dumper::dump($args);
    $notificationId = Uuid::uuid4()->toString();

    $trace = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps(notificationId: $notificationId, trace: $trace);
    $payload   = $laradumps->send(new DumpPayload($args));

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

    $notificationId = Uuid::uuid4()->toString();

    Config::set('preferred_ide', 'phpstorm');

    $trace = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps($notificationId, trace: $trace);
    $payload   = $laradumps->send(new ModelPayload($dish));

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
})->skip('v2');

it('should return the correct payload to mailable', function () {
    $mailable = new TestMail();

    $notificationId = Uuid::uuid4()->toString();

    $trace = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps($notificationId, trace: $trace);
    $payload   = $laradumps->send(new MailablePayload($mailable));

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

    $trace = [
        'file' => 'Test',
        'line' => 1,
    ];

    $notificationId = Uuid::uuid4()->toString();

    $laradumps = new LaraDumps($notificationId, trace: $trace);
    $payload   = $laradumps->send(new TableV2Payload($data));

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('table-v2')
        ->and($payload['content']['values']['Name'])
        ->toContain('Anand Pilania')
        ->and($payload['content']['values']['Email'])
        ->toContain('pilaniaanand@gmail.com')
        ->and($payload['content']['values']['Stack'][0])
        ->toContain('Laravel');
})->group('table-v2');

it('should return the correct markdown payload to dump', function () {
    fixtureEnv('ds_env');

    $args = '# Hi, Anand Pilania!';

    $notificationId = Uuid::uuid4()->toString();

    $trace = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps(notificationId: $notificationId, trace: $trace);
    $payload   = $laradumps->send(new MarkdownPayload($args));

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
            '<h1>Hi, Anand Pilania!</h1>'
        );
});
