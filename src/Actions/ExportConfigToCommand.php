<?php

namespace LaraDumps\LaraDumps\Actions;

final class ExportConfigToCommand
{
    /**
     * Export config to command
     *
     */
    public static function handle(): string
    {
        $configKeys = ListConfigKeys::handle();

        // Set arguments
        $arguments = $configKeys->mapWithKeys(function ($key) {
            return [$key['param'] => config($key['config_key'])];
        })->toArray();

        return self::makeCommand($arguments);
    }

    private static function makeCommand(array $options): string
    {
        $command = 'php artisan ds:init --no-interaction ';

        foreach ($options as $option => $value) {
            if (is_null($value)) {
                $value = '';
            }

            if (is_bool($value)) {
                $value = ($value ? 'true' : 'false');
            }

            $command .= " --{$option}={$value} ";
        }

        return ltrim(rtrim($command));
    }
}
