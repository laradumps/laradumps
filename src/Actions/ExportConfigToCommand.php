<?php

namespace LaraDumps\LaraDumps\Actions;

use Exception;

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
            if (empty($key['param']) || empty($key['config_key'])) {
                throw new Exception('missing param or config_key value');
            }

            $param = strval($key['param']);
            $value = strval(config(strval($key['config_key'])));

            if (is_bool($key['default_value'])) {
                $value = boolval(filter_var($value, FILTER_VALIDATE_BOOLEAN));
            }

            return [(string) $param => $value];
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
