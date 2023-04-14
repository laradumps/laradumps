<?php

namespace LaraDumps\LaraDumps\Payloads;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LaraDumps\LaraDumps\Support\Dumper;

class BenchmarkPayload extends Payload
{
    public function __construct(private mixed $args = null)
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
            $label     = self::getLabel($label);

            $results[$label] = [
                'Start Time' => $startsAt->toTimeString(),
                'End Time'   => $endsAt->toTimeString(),
                'Total Time' => $totalTime . ' ms',
                'Result'     => is_object($result) && method_exists($result, 'toArray')
                    ? $result->toArray()
                    : $result,
            ];

            if ($totalTime < $fastestTime) {
                $fastestLabel = $label;
                $fastestTime  = $totalTime;
            }
        }

        $results['Fastest'] = $fastestLabel;

        return [
            'label'  => 'Benchmark',
            'values' => collect($results)->map(fn ($result, $index) => Dumper::dump($result, 2)),
        ];
    }

    public function collectionMacro(): self
    {
        Collection::macro('benchmark', function (...$closures) {
            /** @var Collection $this */
            return BenchmarkPayload::executeMacro($this, $closures);
        });

        return $this;
    }

    public function queryBuilderMacro(): self
    {
        QueryBuilder::macro('benchmark', function (...$closures) {
            /** @var QueryBuilder $this */
            return BenchmarkPayload::executeMacro($this, $closures);
        });

        return $this;
    }

    public function eloquentMacro(): self
    {
        EloquentBuilder::macro('benchmark', function (...$closures) {
            /** @var EloquentBuilder $this */
            return BenchmarkPayload::executeMacro($this, $closures);
        });

        return $this;
    }

    public static function executeMacro(Collection|EloquentBuilder|QueryBuilder $instance, array $closures): Collection|EloquentBuilder|QueryBuilder
    {
        $_instance = $instance;

        /** @var mixed $results */
        $results = [];

        if (count($closures) === 1 && is_array($closures[0])) {
            $closures = $closures[0];
        }

        foreach ($closures as $label => $closure) {
            $closureWithResult = fn () => $closure($instance);

            $label = self::getLabel($label);

            /* @phpstan-ignore-next-line */
            $results[$label] = $closureWithResult;
        }

        ds()->benchmark($results);

        return $_instance;
    }

    protected static function getLabel(int|string $label): string
    {
        return is_int($label) ? 'Closure ' . $label : $label;
    }
}
