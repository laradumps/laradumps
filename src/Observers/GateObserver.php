<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Auth\Access\Events\GateEvaluated;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{Event};
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumpsCore\Concerns\Traceable;
use LaraDumps\LaraDumpsCore\Payloads\TableV2Payload;
use LaraDumps\LaraDumpsCore\Support\Dumper;

class GateObserver
{
    use Traceable;

    protected ?string $label = null;

    private bool $enabled = false;

    public function register(): void
    {
        Event::listen(GateEvaluated::class, function (GateEvaluated $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $dumps = new LaraDumps(trace: $this->trace);
            $user  = $event->user;

            $dumps->send(
                new TableV2Payload([
                    'Ability'   => $event->ability,
                    'Result'    => $this->gateResult($event->result),
                    'Arguments' => Dumper::dump(collect($event->arguments)->map(function ($argument) {
                        return $argument instanceof Model ? $this->formatModel($argument) : $argument;
                    })->toArray()),
                    'User' => Dumper::dump($user instanceof Authenticatable ? $user->toArray() : null),
                ])
            );

            if (!empty($this->label)) {
                $dumps->label($this->label);
            }
        });
    }

    public function enable(string $label = null): void
    {
        $this->label = $label ?? 'Gate';

        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        $this->trace = array_slice($this->findSource(), 0, 5)[0] ?? [];

        if (!boolval(config('send_http_client'))) {
            return $this->enabled;
        }

        return boolval(Config::get('send_http_client'));
    }

    private function gateResult(null|bool|Response $result): string
    {
        if ($result instanceof Response) {
            return $result->allowed() ? 'allowed' : 'denied';
        }

        return $result ? 'allowed' : 'denied';
    }

    private function formatModel(Model $model): string
    {
        $keys = $model instanceof Pivot && !$model->incrementing
            ? [
                $model->getAttribute($model->getForeignKey()),
                $model->getAttribute($model->getRelatedKey()),
            ]
            : $model->getKey();

        return get_class($model) . ':' . implode('_', array_map(function ($value) {
            if (PHP_VERSION_ID > 80100) {
                return $value instanceof \BackedEnum ? $value->value : $value;
            }

            return $value;
        }, Arr::wrap($keys)));
    }
}
