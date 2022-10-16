<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\{Collection, Str};

final class ListConfigKeys
{
    private const DOC_URL = 'https://laradumps.dev/#/laravel/get-started/configuration?id=';

    /**
     * List LaraDumps configuration keys and values
     * for the configuration page
     *
     * @return Collection<int, non-empty-array<string, array|bool|string>>
     */
    public static function handle(): Collection
    {
        $keys = include GetPackageDir::handle('config/config_keys.php');

        $config = new Collection($keys);

        return $config->transform(function ($config, $key) {
            $config['doc_link']       = strval(Str::of(strval($config['doc_link']))->prepend(self::DOC_URL));
            $config['current_value']  = env($config['env_key']);

            return $config;
        });
    }
}
