<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Host
    |--------------------------------------------------------------------------
    |
    | Dumps App Host address. By default: '127.0.0.1',
    | Uncomment the line below according to your environment.
    */

    'host' => env('DS_APP_HOST', '127.0.0.1'),

    //'host' => 'host.docker.internal',    //Docker on Mac or Windows
    //'host' => '127.0.0.1',               //Homestead with the VirtualBox provider,
    //'host' => '10.211.55.2',             //Homestead with the Parallels provider,

    /*
    |--------------------------------------------------------------------------
    | Port
    |--------------------------------------------------------------------------
    |
    | Dumps App port. By default: 9191
    |
    */

    'port' => env('DS_APP_PORT', 9191),

    /*
    |--------------------------------------------------------------------------
    | Queries
    |--------------------------------------------------------------------------
    |
    | If true, Dumps will start listening to your database queries and send them to Dumps App whenever
    | ->showQueries() method is invoked.
    |
    */

    'send_queries' => env('DS_SEND_QUERIES', false),

    /*
    |--------------------------------------------------------------------------
    | Log Applications
    |--------------------------------------------------------------------------
    |
    | If true, Dumps will start listening to your application logs and send them to Dumps App.
    |
    */

    'send_log_applications' => env('DS_SEND_LOGS', false),

    /*
    |--------------------------------------------------------------------------
    | Color in Screen
    |--------------------------------------------------------------------------
    |
    | If true, Dumps will separate colors into screens with the name of the submitted color
    |
    */

    'send_color_in_screen' => env('DS_SEND_COLOR_IN_SCREEN', false),

    'screen_btn_colors_map' => [
        'default' => [
            'default' => 'btn-white',
        ],
        'danger' => [
            'default' => 'btn-danger',
        ],
        'info' => [
            'default' => 'btn-info',
        ],
        'success' => [
            'default' => 'btn-success',
        ],
        'warning' => [
            'default' => 'btn-warning',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Level Log Colors Map
    |--------------------------------------------------------------------------
    |
    | If true, Dumps will start listening to your application logs and send them to Dumps App.
    |
    */
    'level_log_colors_map' => [
        'error'     => env('DS_LOG_COLOR_ERROR', 'bg-red-600'),
        'critical'  => env('DS_LOG_COLOR_CRITICAL', 'bg-red-600'),
        'alert'     => env('DS_LOG_COLOR_ALERT', 'bg-red-600'),
        'emergency' => env('DS_LOG_COLOR_EMERGENCY', 'bg-red-600'),
        'warning'   => env('DS_LOG_COLOR_WARNING', 'bg-orange-300'),
        'notice'    => env('DS_LOG_COLOR_NOTICE', 'bg-blue-300'),
        'info'      => env('DS_LOG_COLOR_INFO', 'bg-gray-300'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire Components
    |--------------------------------------------------------------------------
    |
    | If true, Dumps will start sending the state of the current browser components
    |
    */

    'send_livewire_components' => env('DS_SEND_LIVEWIRE_COMPONENTS', false),

    /*
    |--------------------------------------------------------------------------
    | Livewire Except Components
    |--------------------------------------------------------------------------
    |
    | Dumps will ignore all components listed inside the array
    |
    */

    'ignore_livewire_components' => [
        // \App\Http\Livewire\Counter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire Failed Validation
    |--------------------------------------------------------------------------
    |
    | If true, LaraDumps will start listening for failed validations and send it to a specific screen
    |
    */

    'send_livewire_failed_validation' => [
        'enabled' => env('DS_SEND_LIVEWIRE_FAILED_VALIDATION', false),
        'sleep'   => env('DS_SEND_LIVEWIRE_FAILED_VALIDATION_SLEEP', 400), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Preferred IDE
    |--------------------------------------------------------------------------
    |
    | Configure your preferred IDE to be used in Dumps App file links.
    |
    */

    'preferred_ide' => env('DS_PREFERRED_IDE', 'phpstorm'),

    /*
    |--------------------------------------------------------------------------
    |  IDE Handlers
    |--------------------------------------------------------------------------
    |
    | Dumps already ships with pre-configured IDE protocol handlers.
    | You may adjust the handler or include custom ones, if needed.
    |
    */

    'ide_handlers' => [
        'atom' => [
            'handler'        => 'atom://core/open/file?filename=',
            'line_separator' => '&line=',
        ],
        'phpstorm' => [
            'handler'        => 'phpstorm://open?url=file://',
            'line_separator' => '&line=',
        ],
        'sublime' => [
            'handler'        => 'subl://open?url=file://',
            'line_separator' => '&line=',
        ],
        'vscode' => [
            'handler'        => 'vscode://file/',
            'line_separator' => ':',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    |  Ignore Route Contains
    |--------------------------------------------------------------------------
    |
    |  You can specify a list of words that a route can count. Can specify part of a text
    |
    */
    'ignore_route_contains' => [
        'debugbar',
        'ignition',
        'horizon',
        'livewire',
    ],

    /*
    |--------------------------------------------------------------------------
    |  Sleep
    |--------------------------------------------------------------------------
    |
    | You can specify an interval in 'seconds' between each dump sent to the app
    |
    */

    'sleep' => env('DS_SLEEP'),

    /*
    |--------------------------------------------------------------------------
    |  CI Check
    |--------------------------------------------------------------------------
    |
    | Check if you forgot any ds() in your files,
    | run "php artisan ds:check" in your pipeline
    |
    */

    'ci_check' => [
        'directories' => [
            base_path('config'),
        ],
        'ignore_line_when_contains_text' => [
        ],
        'text_to_search' => [
            ' ds(',
            ' dsd(',
            ' ds1(',
            ' ds2(',
            ' ds3(',
            ' ds4(',
            ' ds5(',
            '@ds(',
        ],
    ],
];
