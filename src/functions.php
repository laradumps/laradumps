<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{BladePayload, ModelPayload};
use LaraDumps\LaraDumpsCore\Support\Dumper;
use Spatie\Backtrace\Backtrace;

if (!function_exists('dsBlade')) {
    function dsBlade(mixed $args): void
    {
        $frame = collect(debug_backtrace())
            ->filter(function ($frame) {
                /** @var array $frame */
                return $frame['function'] === 'render' && $frame['class'] === 'Illuminate\View\View';
            })->first();

        /** @var BladeCompiler $blade
        * @phpstan-ignore-next-line */
        $blade    = $frame['object'];
        $viewPath = $blade->getPath();

        $backtrace = Backtrace::create();
        $backtrace = $backtrace->applicationPath(appBasePath());
        $frame     = app(LaraDumps::class)->parseFrame($backtrace);

        $frame = [
            'file' => $viewPath,
            'line' => data_get($frame, 'lineNumber'),
        ];

        $notificationId = Str::uuid()->toString();
        $laradumps      = new LaraDumps(notificationId: $notificationId);

        if ($args instanceof Model) {
            $payload = new ModelPayload($args);
            $payload->setDumpId(uniqid());
        } else {
            [$pre, $id] = Dumper::dump($args);

            $payload = new BladePayload($pre);
            $payload->setDumpId($id);
        }

        $payload->setFrame($frame);

        $laradumps->send($payload, withFrame: false);
    }
}
