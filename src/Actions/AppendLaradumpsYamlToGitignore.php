<?php

namespace LaraDumps\LaraDumps\Actions;

final class AppendLaradumpsYamlToGitignore
{
    public static function handle(): bool
    {

        if (! file_exists('.gitignore') || str_contains(file_get_contents('.gitignore'), 'laradumps.yaml')) {
            return false;
        }

        file_put_contents('.gitignore', 'laradumps.yaml'.PHP_EOL, FILE_APPEND);

        return true;

    }
}
