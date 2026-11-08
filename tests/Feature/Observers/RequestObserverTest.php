<?php

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessing;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, Payload};

$requestId = fn () => (new LaraDumps())->send(new DumpPayload('x'), withFrame: false)->toArray()['request_id'];

it('keeps the request_id for sync jobs and starts a new one for queued jobs', function () use ($requestId) {
    Payload::startRequest();
    $before = $requestId();

    event(new JobProcessing('sync', Mockery::mock(Job::class, ['payload' => []])));
    expect($requestId())->toBe($before);

    event(new JobProcessing('redis', Mockery::mock(Job::class, ['payload' => []])));
    expect($requestId())->not->toBe($before);
});

it('starts a new request_id for each Octane request', function () use ($requestId) {
    $before = $requestId();

    event('Laravel\Octane\Events\RequestReceived');

    expect($requestId())->not->toBe($before);
});
