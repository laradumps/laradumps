<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Facades\File;

final class Config
{
    protected static string $file = __DIR__ . '/../../config/laradumps.php';

    protected static array $map = [
        'host'                      => 'DS_APP_HOST',
        'port'                      => 'DS_APP_PORT',
        'sleep'                     => 'DS_SLEEP',
        'send_queries'              => 'DS_SEND_QUERIES',
        'send_log_applications'     => 'DS_SEND_LOGS',
        'send_livewire_components'  => 'DS_SEND_LIVEWIRE_COMPONENTS',
        'send_livewire_events'      => 'DS_LIVEWIRE_EVENTS',
        'auto_clear_on_page_reload' => 'DS_AUTO_CLEAR_ON_PAGE_RELOAD',
        'auto_invoke_app'           => 'DS_AUTO_INVOKE_APP',
        'preferred_ide'             => 'DS_PREFERRED_IDE',
        'send_http_client_requests' => 'DS_SEND_HTTP_CLIENT_REQUESTS',
        'send_jobs'                 => 'DS_SEND_JOBS',
        'send_cache'                => 'DS_SEND_CACHE',
        'send_commands'             => 'DS_SEND_COMMANDS',
    ];

    public static function get(string $key): mixed
    {
        $array = include self::$file;

        $value = data_get($array, $key);

        return match ($value) {
            'false' => false,
            'true'  => true,
            default => $value,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        if (!isset(self::$map[$key])) {
            return;
        }

        $_ENV[self::$map[$key]] = $value;

        self::updateEnv(self::$map[$key], strval($value));
    }

    // Based on https://stackoverflow.com/questions/32307426/how-to-change-variables-in-the-env-file-dynamically-in-laravel
    public static function updateEnv(string $key, string $value, $filePath = ''): void
    {
        if (empty($filePath)) {
            $filePath  = base_path('.env');
        }

        $fileContent = File::get($filePath);

        //Store the key
        $original = [];

        if ((bool) preg_match('/^\d+$/', str_replace('.', '', $value)) === false && in_array($value, ['true', 'false']) === false) {
            $value = "\"{$value}\"";
        }

        if (preg_match("/^$key=(.+)$/m", $fileContent, $original)) {
            //Update
            $fileContent = preg_replace("/^$key=.+$/m", "$key=$value", $fileContent);
        } else {
            //Append the key to the end of file
            $fileContent .= PHP_EOL . "$key=$value";
        }

        File::put($filePath, strval($fileContent));
    }
}
