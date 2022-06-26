<?php

namespace LaraDumps\LaraDumps\Commands\Concerns;

use Illuminate\Support\Facades\File;

trait UpdateEnv
{
    // Based on https://stackoverflow.com/questions/32307426/how-to-change-variables-in-the-env-file-dynamically-in-laravel

    public function updateEnv(string $key, string $value): void
    {
        $filePath  = base_path('.env');

        if (!File::exists($filePath)) {
            return;
        }

        $fileContent = File::get($filePath);

        //Store the key
        $original = [];

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
