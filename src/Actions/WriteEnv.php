<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Facades\File;

// Based on https://stackoverflow.com/questions/32307426/how-to-change-variables-in-the-env-file-dynamically-in-laravel

final class WriteEnv
{
    public static function handle(array $settings, string $filePath = ''): void
    {
        if (empty($filePath)) {
            $filePath  = base_path('.env');
        }

        $fileContent = File::get($filePath);

        foreach ($settings as $key => $value) {
            //Store the key
            $original = [];

            //  if (preg_match('/^[a-zA-Z][a-zA-Z_][a-zA-Z]$/', $key)) {

            if (!preg_match('/^[0-9a-zA-Z_]+$/i', $key)) {
                throw new \Exception("Error: '{$key}' is not a valid .env key.");
            }

            $key = strtoupper($key);

            //Wrap strings
            if ((bool) preg_match('/^\d+$/', strval(str_replace('.', '', $value))) === false
                    && in_array($value, ['true', 'false'])                         === false
                    && $value != '') {
                $value = "\"{$value}\"";
            }

            //Deal with boolean
            if (in_array($value, ['true', 'false'])) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            }

            if (preg_match("/^$key\=.*$/m", strval($fileContent), $original)) {
                //Update
                $fileContent = preg_replace("/^$key\=.*$/m", "$key=$value", strval($fileContent));
            } else {
                //Append the key to the end of file
                $fileContent .= PHP_EOL . "$key=$value";
            }

            File::put($filePath, strval($fileContent));
        }
    }
}
