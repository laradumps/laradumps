<?php

namespace LaraDumps\LaraDumps\Actions;

use LaraDumps\LaraDumps\Support\IdeHandle;

final class GetConfigFileLink
{
    /**
     * Returns Full file and handler
     * path for LaraDumps config
     *
     */
    public static function handle(): string
    {
        return IdeHandle::makeFileHandler(
            str_replace(DIRECTORY_SEPARATOR, '/', base_path('/config/laradumps.php')),
            1
        );
    }
}
