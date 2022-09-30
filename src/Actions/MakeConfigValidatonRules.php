<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\{Collection};

final class MakeConfigValidatonRules
{
    /**
     * Make Validator rules for Config forms
     *
     */
    public static function handle(): array
    {
        $configKeys  = ListConfigKeys::handle();

        return $configKeys->mapWithKeys(function ($config) {
            if (!isset($config['env_key'])) {
                throw new \Exception('config does not have the index "env_key"');
            }

            $config['env_key'] = str_replace('.', '_', strval($config['env_key']));

            return [
                $config['env_key'] => match (strval($config['type'])) { /** @phpstan-ignore-line */
                    'toggle' => 'required|boolean',
                    'text'   => 'required|min:1',
                    'select' => 'required|in:' . implode(',', array_keys((array) $config['options']))
                },
            ];
        })->toArray();
    }
}
