<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Facades\File;

final class SuggestAppHost
{
    /**
     * Suggest App Host
     */
    public static function handle(): string
    {
        $host = '127.0.0.1';

        //Homestead
        if (File::exists(base_path('Homestead.yaml'))) {
            $host = '10.211.55.2';
        }

        //Docker
        if (File::exists(base_path('docker-compose.yml'))) {
            $host = 'host.docker.internal';
        }

        return $host;
    }
}
