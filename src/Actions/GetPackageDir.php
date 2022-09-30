<?php

namespace LaraDumps\LaraDumps\Actions;

final class GetPackageDir
{
    /**
     * Returns Full file path inside LaraDumps
     *
     * @param string $path File Path inside LaraDumps
     */
    public static function handle(string $path): string
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', __DIR__ . '/../../' . $path);
    }
}
