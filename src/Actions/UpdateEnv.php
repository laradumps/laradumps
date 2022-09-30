<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Facades\File;

final class UpdateEnv
{
    // Based on https://stackoverflow.com/questions/32307426/how-to-change-variables-in-the-env-file-dynamically-in-laravel

    public static function handle(string $key, string|int|bool $value, string $filename = '.env'): void
    {
        $filePath  = base_path($filename);

        if (!File::exists($filePath)) {
            return;
        }

        if (is_string($value)) {
            $value = '"' . str_replace('"', '\"', $value) . '"';
        }

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        $fileContent = File::get($filePath);

        //Store the key
        $original = [];
        $key      = strtoupper($key);

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
