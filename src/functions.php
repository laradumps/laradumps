<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{BladePayload, ModelPayload};
use LaraDumps\LaraDumpsCore\Support\Dumper;

if (!function_exists('dsBlade')) {
    function dsBlade(mixed $args): void
    {
        $trace = collect(debug_backtrace())
            ->filter(function ($trace) {
                /** @var array $trace */
                return $trace['function'] === 'render' && $trace['class'] === 'Illuminate\View\View';
            })->first();

        /** @var BladeCompiler $blade
        * @phpstan-ignore-next-line */
        $blade    = $trace['object'];
        $viewPath = $blade->getPath();

        $trace = [
            'file' => $viewPath,
            'line' => 1,
        ];

        $notificationId = Str::uuid()->toString();
        $laradumps      = new LaraDumps(notificationId: $notificationId, trace: $trace);

        if ($args instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            if (!$args->items()[0] instanceof Model) {
                return;
            }

            $models = [];

            /** @var Model $item */
            foreach ($args->items() as $item) {
                $models[] = [
                    'className'  => get_class($item),
                    'attributes' => $item->attributesToArray(),
                    'relations'  => $item->relationsToArray(),
                ];
            }

            $args->setCollection(collect($models));
        }

        if ($args instanceof Model) {
            $payload = new ModelPayload($args);
            $payload->setDumpId(uniqid());
        } else {
            [$pre, $id] = Dumper::dump($args);

            $payload = new BladePayload($pre, $viewPath);
            $payload->setDumpId($id);
        }

        $laradumps->send($payload);
    }
}
