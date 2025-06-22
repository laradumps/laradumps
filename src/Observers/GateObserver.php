<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Auth\Access\Events\GateEvaluated;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\Payloads\TableV2Payload;

class GateObserver extends BaseObserver
{
    protected string $label = 'Gate';

    public function register(): void
    {
        Event::listen(GateEvaluated::class, fn (GateEvaluated $event) => $this->handle($event));
    }

    public function handle(GateEvaluated $event): void
    {
        if (! $this->isEnabled('gate')) {
            return;
        }

        $user = $event->user instanceof Authenticatable
            ? Dumper::dump($event->user->toArray())[0]
            : null;

        $arguments = collect($event->arguments)
            ->map(fn ($argument) => $argument instanceof Model ? $this->formatModel($argument) : $argument)
            ->toArray();

        $payload = new TableV2Payload([
            'Ability' => $event->ability,
            'Result' => $this->gateResult($event->result),
            'Arguments' => Dumper::dump($arguments)[0],
            'User' => $user,
        ], screen: 'gate', label: $this->label);

        $dumps = new LaraDumps();
        $dumps->toScreen('gate');
        $dumps->send($payload);
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
        $keys = $model instanceof Pivot && ! $model->incrementing
            ? [
                $model->getAttribute($model->getForeignKey()),
                $model->getAttribute($model->getRelatedKey()),
            ]
            : $model->getKey();

        $encodedKeys = array_map(fn ($value) => $this->normalizeKey($value), Arr::wrap($keys));

        return get_class($model).':'.implode('_', $encodedKeys);
    }

    private function normalizeKey(mixed $value): mixed
    {
        if (PHP_VERSION_ID > 80100 && $value instanceof \BackedEnum) {
            return $value->value;
        }

        return $value;
    }
}
