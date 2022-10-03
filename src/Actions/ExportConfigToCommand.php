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
        return self::makeCommand([
            'host'                => config('laradumps.host'),
            'port'                => config('laradumps.port'),
            'send_queries'        => config('laradumps.send_queries'),
            'send_logs'           => config('laradumps.send_log_applications'),
            'send_livewire'       => config('laradumps.send_livewire_components'),
            'livewire_events'     => config('laradumps.send_livewire_events'),
            'livewire_validation' => config('laradumps.send_livewire_failed_validation.enabled'),
            'livewire_autoclear'  => config('laradumps.auto_clear_on_page_reload'),
            'auto_invoke'         => config('laradumps.auto_invoke_app'),
            'ide'                 => config('laradumps.preferred_ide'),
        ]);
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
