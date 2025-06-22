<?php

use Illuminate\Log\Events\MessageLogged;
use LaraDumps\LaraDumps\Payloads\{LogPayload, MailablePayload, MarkdownPayload, ModelPayload};
use LaraDumps\LaraDumps\Tests\Support\Classes\TestMail;
use LaraDumps\LaraDumps\Tests\Support\Models\Dish;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, TableV2Payload};
use Ramsey\Uuid\Uuid;

it('should return the correct payload to dump', function () {
    $args = [
        'name' => 'Luan',
    ];

    [$args] = Dumper::dump($args);
    $notificationId = Uuid::uuid4()->toString();

    $frame = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps(notificationId: $notificationId);
    $payload = new DumpPayload($args);
    $payload->setFrame($frame);

    $payload = $laradumps->send($payload, withFrame: false)->toArray();

    expect($payload)
        ->id->toBeUuid()
        ->type->toBe('dump')
        ->code_snippet->toBeArray()
        ->and($payload['ide_handle']['real_path'])
        ->toBe('Test')
        ->and($payload['ide_handle']['line'])
        ->toBe('1')
        ->and($payload['dump']['dump'])
        ->toContain(
            '<span class=sf-dump-key>name</span>',
            '<span class=sf-dump-str title="4 characters">Luan</span>'
        );
});

it('should return the correct payload to model', function () {
    $dish = Dish::query()->first();

    $frame = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps();
    $payload = new ModelPayload($dish);
    $payload->setFrame($frame);

    $payload = $laradumps->send($payload, withFrame: false)->toArray();

    expect($payload)
        ->id->toBeUuid()
        ->type->toBe('model')
        ->code_snippet->toBeArray()
        ->and($payload['ide_handle']['real_path'])
        ->toBe('Test')
        ->and($payload['ide_handle']['line'])
        ->toBe('1')
        ->and($payload['model']['relations'])
        ->toMatchArray([])
        ->and($payload['model']['className'])
        ->toBe('LaraDumps\LaraDumps\Tests\Support\Models\Dish')
        ->and($payload['model']['attributes'][0])
        ->toContain(
            '<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>1</span>',
            '<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="14 characters">Pastel de Nata</span>',
            '<span class=sf-dump-key>active</span>" => <span class=sf-dump-num>1</span>',
            '<span class=sf-dump-key>created_at</span>" => <span class=sf-dump-const>null</span>',
            '<span class=sf-dump-key>updated_at</span>" => <span class=sf-dump-const>null</span>'
        );
});

it('should return the correct payload to mailable', function () {
    $mailable = new TestMail();

    $frame = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps();
    $payload = new MailablePayload($mailable);
    $payload->setFrame($frame);

    $payload = $laradumps->send($payload, withFrame: false)->toArray();

    expect($payload)
        ->id->toBeUuid()
        ->type->toBe('mailable')
        ->and($payload['mailable']['subject'])
        ->toContain('An test mail')
        ->and($payload['mailable']['from'][0]['email'])
        ->toContain('from@example.com')
        ->and($payload['mailable']['to'][0]['email'])
        ->toContain('to@example.com');
})->group('mailable');

it('should return the correct payload to table_v2', function () {
    $data = [
        'Name' => 'Anand Pilania',
        'Email' => 'pilaniaanand@gmail.com',
        'Stack' => [
            'Laravel',
            'Flutter',
        ],
    ];

    $frame = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps();
    $payload = new TableV2Payload($data);
    $payload->setFrame($frame);

    $payload = $laradumps->send($payload, withFrame: false)->toArray();

    expect($payload)
        ->type->toBe('table_v2')
        ->and($payload['table_v2']['values']['Name'])
        ->toContain('Anand Pilania')
        ->and($payload['table_v2']['values']['Email'])
        ->toContain('pilaniaanand@gmail.com')
        ->and($payload['table_v2']['values']['Stack'][0])
        ->toContain('Laravel');
})->group('table_v2');

it('should return the correct markdown payload to dump', function () {
    $args = '# Hi, Anand Pilania!';

    $frame = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps();
    $payload = new MarkdownPayload($args);
    $payload->setFrame($frame);

    $payload = $laradumps->send($payload, withFrame: false)->toArray();

    expect($payload)
        ->id->toBeUuid()
        ->type->toBe('dump')
        ->code_snippet->toBeArray()
        ->and($payload['ide_handle']['real_path'])
        ->toBe('Test')
        ->and($payload['ide_handle']['line'])
        ->toBe('1')
        ->and($payload['dump']['dump'])
        ->toContain(
            '<h1>Hi, Anand Pilania!</h1>'
        );
});

it('should return the correct logs to bump', function () {
    $exception = new \Exception('This is a test exception.');

    $message = new MessageLogged(
        'error',
        'A critical error occurred.',
        ['exception' => $exception]
    );

    $frame = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps();

    $log = [
        'message' => $message->message,
        'level' => $message->level,
        'context' => [],
    ];

    $payload = new LogPayload($log);
    $payload->setFrame($frame);

    $payload = $laradumps->send($payload, withFrame: false)->toArray();

    expect($payload)
        ->id->toBeUuid()
        ->type->toBe('log_application')
        ->log_application->toBe([
            'message' => 'A critical error occurred.',
            'level' => 'error',
            'context' => [],
        ])
        ->code_snippet->toBeArray()
        ->and($payload['ide_handle']['real_path'])
        ->toBe('Test')
        ->and($payload['ide_handle']['line'])
        ->toBe('1');
});
