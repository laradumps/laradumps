<?php

use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\BladePayload;

if (!function_exists('ds')) {
    function ds(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Str::uuid()->toString();
        $dump           = new LaraDumps($notificationId, backtrace: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg);
            }
        }

        return $dump;
    }
}

if (!function_exists('phpinfo')) {
    function phpinfo(): LaraDumps
    {
        return ds()->phpinfo();
    }
}

if (!function_exists('dsd')) {
    function dsd(mixed ...$args): void
    {
        ds($args)->die();
    }
}

if (!function_exists('ds1')) {
    function ds1(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Str::uuid()->toString();
        $dump           = new LaraDumps($notificationId, backtrace: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 1');
            }
        }

        return new LaraDumps($notificationId, backtrace: $backtrace);
    }
}

if (!function_exists('ds2')) {
    function ds2(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Str::uuid()->toString();
        $dump           = new LaraDumps($notificationId, backtrace: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 2');
            }
        }

        return new LaraDumps($notificationId, backtrace: $backtrace);
    }
}

if (!function_exists('ds3')) {
    function ds3(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Str::uuid()->toString();
        $dump           = new LaraDumps($notificationId, backtrace: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 3');
            }
        }

        return new LaraDumps($notificationId, backtrace: $backtrace);
    }
}

if (!function_exists('ds4')) {
    function ds4(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Str::uuid()->toString();
        $dump           = new LaraDumps($notificationId, backtrace: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 4');
            }
        }

        return new LaraDumps($notificationId, backtrace: $backtrace);
    }
}

if (!function_exists('ds5')) {
    function ds5(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Str::uuid()->toString();
        $dump           = new LaraDumps($notificationId, backtrace: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 5');
            }
        }

        return new LaraDumps($notificationId, backtrace: $backtrace);
    }
}

if (!function_exists('dsBlade')) {
    function dsBlade(mixed $args): void
    {
        $backtrace = collect(debug_backtrace())
            ->filter(function ($trace) {
                return $trace['function'] === 'render' && $trace['class'] === 'Illuminate\View\View';
            })->first();

        /** @var BladeCompiler $blade
        * @phpstan-ignore-next-line */
        $blade     = $backtrace['object'];
        $viewPath  = $blade->getPath();

        $backtrace      = [
            'file' => $viewPath,
            'line' => 1,
        ];

        $notificationId = Str::uuid()->toString();
        $ds             = new LaraDumps(notificationId: $notificationId, backtrace: $backtrace);
        $ds->send(new BladePayload($args, $viewPath));
    }
}
