<?php

namespace LaraDumps\LaraDumps\Payloads;

use Carbon\Carbon;
use LaraDumps\LaraDumps\Support\Dumper;

class BenchmarkPayload extends Payload
{
    public function __construct(private mixed $args)
    {
    }

    public function type(): string
    {
        return 'table-v2';
    }

    public function content(): array
    {
        $results      = [];
        $fastestLabel = '';
        $fastestTime  = PHP_INT_MAX;

        /** @var array  $closures */
        $closures = $this->args;

        if (count($closures) === 1 && is_array($closures[0])) {
            $closures = $closures[0];
        }

        foreach ($closures as $label => $closure) {
            $startsAt = Carbon::now();
            /** @var callable $result */
            $result   = $closure();
            $endsAt   = Carbon::now();

            $totalTime = $endsAt->diffInMilliseconds($startsAt);
            $label     = is_int($label) ? 'Closure ' . $label : $label;

            $results[$label] = [
                'Start Time' => $startsAt->toDateTimeString(),
                'End Time'   => $endsAt->toDateTimeString(),
                'Total Time' => $totalTime . ' ms',
                'Result'     => $result,
            ];

            if ($totalTime < $fastestTime) {
                $fastestLabel = $label;
                $fastestTime  = $totalTime;
            }
        }

        $results['Fastest'] = $fastestLabel;

        return [
            'label'  => 'Benchmark',
            'values' => collect($results)->map(fn ($result, $index) => Dumper::dump($result)),
        ];
    }
}
