<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Facades\Artisan;

final class UpdateConfigFromForm
{
    /**
     * Update config from page form
     *
     * @param array<string,string> $updatedConfig
     * @throws \Exception
     */
    public static function handle(array $updatedConfig): void
    {
        $configKeys  = ListConfigKeys::handle();

        collect($updatedConfig)->each(
            function ($value, $field) use ($configKeys) {
                $originalKey = $configKeys->firstWhere('env_key', $field);

                if (is_null($originalKey)) {
                    throw new \Exception('Could not find a key for ' . $field);
                }

                $value = match ($originalKey['type']) {
                    'toggle' => boolval($value),
                    default  => $value
                };

                UpdateEnv::handle($field, $value);

                config()->set(strval($originalKey['config_key']), $value);

                Artisan::call('config:clear');
            }
        );
    }
}
