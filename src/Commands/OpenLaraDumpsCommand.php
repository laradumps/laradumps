<?php

namespace LaraDumps\LaraDumps\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\{Exception\ProcessTimedOutException, ExecutableFinder, Process};

class OpenLaraDumpsCommand extends Command
{
    protected $signature = 'ds:open';

    protected string $url = 'laradumps://';

    protected string $systemOs = PHP_OS_FAMILY;

    protected $description = 'Open Laradumps app automatically';

    public function handle(): int
    {
        $defaultMessage = 'Could not open LaraDumps app automatically.';

        try {
            $timeout = intval(config('laradumps.auto_start_with_deeplink.timout', 10));

            $binary = $this->getCommand();

            $process = tap(Process::fromShellCommandline(command: escapeshellcmd("{$binary} {$this->url}"), timeout: $timeout))->run();

            if (!$process->isSuccessful()) {
                echo $defaultMessage;
            }
        } catch (ProcessTimedOutException) {
            echo $defaultMessage;
        }

        return Command::SUCCESS;
    }

    private function getCommand(): ?string
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
