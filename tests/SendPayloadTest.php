<?php

use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\DumpPayload;
use LaraDumps\LaraDumps\Support\Dumper;
use PHPUnit\Framework\Constraint\LessThan;

test('curl resets fast when app is not available', function () {
    $this->app['config']->set('laradumps.host', '192.168.0.10');

    $maxExecutionTime = 100;

    $args = Dumper::dump([
        'name' => 'Luan',
    ]);

    $startTime = now();
    (new LaraDumps())->send(new DumpPayload($args));
    $endTime   = now();

    $executionTime = $endTime->diffInMilliseconds($startTime);

    $this->assertThat($executionTime, new LessThan($maxExecutionTime));
});
