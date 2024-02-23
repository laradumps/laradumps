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

        $frame = [
            'file' => $viewPath,
            'line' => 1,
        ];

        $notificationId = Str::uuid()->toString();
        $laradumps      = new LaraDumps(notificationId: $notificationId);

        if ($args instanceof Model) {
            $payload = new ModelPayload($args);
            $payload->setDumpId(uniqid());
        } else {
            [$pre, $id] = Dumper::dump($args);

            $payload = new BladePayload($pre, $viewPath);
            $payload->setDumpId($id);
        }

        $payload->setFrame($frame);

        $laradumps->send($payload);
    }
}
