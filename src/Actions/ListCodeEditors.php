<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Facades\File;

final class ListCodeEditors
{
    /**
     * List LaraDumps supported IDE
     *
     */
    public static function handle(): array
    {
        $configFilePath = GetPackageDir::handle('config/laradumps.php');

        if (!File::exists($configFilePath)) {
            throw new \Exception("LaraDumps config file doesn't exist.");
        }

        $ideList = include($configFilePath);

        return collect((array) $ideList['ide_handlers'])
             ->mapWithKeys(function ($ide, $key) {
                 return [$key => ($ide['name'] ?? $key)];
             })->toArray();
    }
}
