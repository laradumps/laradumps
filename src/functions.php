<?php

use LaraDumps\LaraDumps\LaraDumps;
use Ramsey\Uuid\Uuid;

if (!function_exists('ds')) {
    function ds(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId, backtrack: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg);
            }
        }

        return new LaraDumps($notificationId, backtrack: $backtrace);
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

        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId, backtrack: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 1');
            }
        }

        return new LaraDumps($notificationId, backtrack: $backtrace);
    }
}

if (!function_exists('ds2')) {
    function ds2(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId, backtrack: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 2');
            }
        }

        return new LaraDumps($notificationId, backtrack: $backtrace);
    }
}

if (!function_exists('ds3')) {
    function ds3(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId, backtrack: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 3');
            }
        }

        return new LaraDumps($notificationId, backtrack: $backtrace);
    }
}

if (!function_exists('ds4')) {
    function ds4(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId, backtrack: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 4');
            }
        }

        return new LaraDumps($notificationId, backtrack: $backtrace);
    }
}

if (!function_exists('ds5')) {
    function ds5(mixed ...$args): LaraDumps
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId, backtrack: $backtrace);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 5');
            }
        }

        return new LaraDumps($notificationId, backtrack: $backtrace);
    }
}
