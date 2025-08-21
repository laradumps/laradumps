<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ignored Queries Patterns
    |--------------------------------------------------------------------------
    |
    | Define patterns for SQL queries and routes that LaraDumps should ignore.
    | Useful for preventing logs of repetitive or unnecessary queries/routes.
    |
    */
    'queries' => [
        'ignore_sql_patterns' => [
            '', // select * from `*` where `id` = 1
        ],
        'ignore_routes_patterns' => [
            'horizon/*',
            'telescope/*',
        ],
    ],
];
