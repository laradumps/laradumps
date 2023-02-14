<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CI Check
    |--------------------------------------------------------------------------
    |
    | List of directories and text to be searched when running the
    |  "php artisan ds:check" command.
    |
    */

    'ci_check' => [
        'directories' => [
            base_path('app'),
            base_path('resources'),
        ],
        'ignore_line_when_contains_text' => [
            //'ads()'
        ],
        'text_to_search' => [
            'ds(',
            'dsq(',
            'dsd(',
            'ds1(',
            'ds2(',
            'ds3(',
            'ds4(',
            'ds5(',
            'dsAutoClearOnPageReload',
        ],
    ],
];
