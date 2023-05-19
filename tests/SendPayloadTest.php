<?php

use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\DumpPayload;
use LaraDumps\LaraDumps\Support\Dumper;
use PHPUnit\Framework\Constraint\LessThan;

it('curl resets fast when app is not available', function () {
    $maxExecutionTime = 30;

    $startTime = now();

    $this->app['config']->set('laradumps.host', '192.168.0.10');

    $args = Dumper::dump([
        'name' => 'Luan',
    ]);

    (new LaraDumps)->send(new DumpPayload($args));

    $endTime = now();
    $executionTime = $endTime->diffInMilliseconds($startTime);

    $this->assertThat(
        $executionTime,
        new LessThan($maxExecutionTime),
        'The function execution time exceeded the maximum allowed time.'
    );
});
