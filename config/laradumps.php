<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Host
    |--------------------------------------------------------------------------
    |
    | Dumps App Host address. By default: '127.0.0.1',
    | Uncomment the line below according to your environment.
    |
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
    | If true, Dumps will start listening to your database queries and send
    | them to Dumps App whenever "->showQueries()" method is invoked.
    |
    */

    'send_queries' => env('DS_SEND_QUERIES', false),

    /*
    |--------------------------------------------------------------------------
    | Log Applications
    |--------------------------------------------------------------------------
    |
    | If true, Dumps will start listening to your application logs and send
    | them to Dumps App.
    |
    */

    'send_log_applications' => env('DS_SEND_LOGS', false),

    /*
    |--------------------------------------------------------------------------
    | Color in Screen
    |--------------------------------------------------------------------------
    |
    | If true, Dumps will separate colors into screens with the name of the
    | submitted color.
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
    | Definition of Tailwind CSS class for LaraDumps color tag.
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
    | If true, Dumps will start sending the state of the current browser
    | components.
    |
    */

    'send_livewire_components' => env('DS_SEND_LIVEWIRE_COMPONENTS', false),

    /*
    |--------------------------------------------------------------------------
    | Livewire Except Components
    |--------------------------------------------------------------------------
    |
    | LaraDumps will not listen to the Livewire Components listed here.
    |
    */

    'ignore_livewire_components' => [
        // \App\Http\Livewire\Counter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire Components
    |--------------------------------------------------------------------------
    |
    | List of Livewire Components allowed to be Dumped to the Desktop App.
    |
    */

    'livewire_components' => env('DS_LIVEWIRE_COMPONENTS', ''),

    /*
    |--------------------------------------------------------------------------
    | Livewire Failed Validation
    |--------------------------------------------------------------------------
    |
    | If enabled, LaraDumps will start listening for failed validations and
    | send them to a specific screen.
    | You can also specify an interval in milliseconds between each dump sent
    | to the application.
    |
    */

    'send_livewire_failed_validation' => [
        'enabled' => env('DS_SEND_LIVEWIRE_FAILED_VALIDATION', false),
        'sleep'   => env('DS_SEND_LIVEWIRE_FAILED_VALIDATION_SLEEP', 400),
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
    | IDE Handlers
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
        'vscode_remote' => [
            'handler'        => 'vscode://vscode-remote/',
            'line_separator' => ':',
            'local_path'     => 'wsl+' . env('DS_PREFERRED_WSL_DISTRO', 'Ubuntu20.04LTS'),
            'remote_path'    => '/var/www/html',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore Route Contains
    |--------------------------------------------------------------------------
    |
    | You can specify a list of words that a route can count. Can specify part
    | of a text
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
    | Sleep
    |--------------------------------------------------------------------------
    |
    | You can specify an interval in 'seconds' between each dump sent to the
    | app.
    |
    */

    'sleep' => env('DS_SLEEP'),

    /*
    |--------------------------------------------------------------------------
    | Auto Invoke App
    |--------------------------------------------------------------------------
    |
    | By default the LaraDumps app will always be invoked on every dump.
    | Set 'false' to disable this behavior.
    |
    */

    'auto_invoke_app' => env('DS_AUTO_INVOKE_APP', true),

    /*
    |--------------------------------------------------------------------------
    | CI Check
    |--------------------------------------------------------------------------
    |
    | Check if you forgot any ds() in your files,
    | run "php artisan ds:check" in your pipeline.
    |
    */

    'ci_check' => [
        'directories' => [
            base_path('app'),
            base_path('resources'),
        ],
        'ignore_line_when_contains_text' => [
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

    /*
    |--------------------------------------------------------------------------
    | Auto-Clear on page reload
    |--------------------------------------------------------------------------
    |
    | When debugging Livewire, you need to clear your LaraDumps APP history
    | every time the page is reloaded to keep track of your components.
    | Set auto_clear_on_page_reload to true so LaraDumps will clear history
    | automatically on page reload.
    |
    */

    'auto_clear_on_page_reload' => env('DS_AUTO_CLEAR_ON_PAGE_RELOAD', false),

];
