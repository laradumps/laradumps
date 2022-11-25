<?php

namespace LaraDumps\LaraDumps\Actions;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\{ExecutableFinder, Process};

class OpenLaraDumps
{
    protected static string $url = 'laradumps://';

    public static function execute(): void
    {
        $defaultMessage = 'Could not open LaraDumps app automatically.';

        try {
            $timeout = intval(config('laradumps.auto_start_with_deeplink.timeout', 10));

            $binary = self::getCommand();

            $url = self::$url;

            $process = tap(Process::fromShellCommandline(command: escapeshellcmd("{$binary} {$url}"), timeout: $timeout))->run();

            if (!$process->isSuccessful()) {
                dump($defaultMessage);
            }
        } catch (ProcessTimedOutException) {
            dump($defaultMessage);
        }
    }

    private static function getCommand(): ?string
    {
        $customCommand = strval(config('laradumps.auto_start_with_deeplink.command'));

        if (filled($customCommand)) {
            return $customCommand;
        }

        return collect(match (PHP_OS_FAMILY) {
            'Windows' => ['start'],
            'Darwin'  => ['open'],
            'Linux'   => ['xdg-open', 'wslview'],
            default   => ['xdg-open'],
        })->first(fn ($binary) => (new ExecutableFinder())->find($binary) !== null);
    }
}
