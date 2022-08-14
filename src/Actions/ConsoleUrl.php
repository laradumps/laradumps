<?php

namespace LaraDumps\LaraDumps\Actions;

use Exception;

final class ConsoleUrl
{
    /**
     * Open a given URL from console
     *
     * @param string $url
     * @return void
     */
    public static function open(string $url): void
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new Exception('Invalid URL');
        }

        switch (PHP_OS_FAMILY) {
            case 'Darwin':
                $command = 'open';

                break;
            case 'Windows':
                $command = 'start';

                break;
            case 'Linux':
                $command = 'xdg-open';

                break;
            default:
                return;
        }

        exec($command . ' ' . $url);
    }
}
