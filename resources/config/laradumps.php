<?php

return [
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
