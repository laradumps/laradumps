<?php

use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\BladePayload;

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
        $ds             = new LaraDumps(notificationId: $notificationId, trace: $trace);
        $ds->send(new BladePayload($args, $viewPath));
    }
}
