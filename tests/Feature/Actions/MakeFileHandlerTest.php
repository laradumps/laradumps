<?php

use LaraDumps\LaraDumps\Actions\MakeFileHandler;

it('returns empty string when there is no .env keys ', function () {
    fixtureEnv('ds_env', ['DS_FILE_HANDLER' => null]);
    expect(MakeFileHandler::handle(['file' => '/home/index.php', 'line' => 2]))->toBe('');
});

it('returns empty string when no file is given ', function () {
    fixtureEnv('ds_env');
    expect(MakeFileHandler::handle(['file' => '', 'line' => null]))->toBe('');
});

it('parses default env', function () {
    fixtureEnv('ds_env');
    expect(MakeFileHandler::handle(['file' => '/home/index.php', 'line' => 2]))->toBe('phpstorm://open?file=/Users/jamesbond/bomb-disarmer/index.php&line=2');
});

it('DS_PROJECT_PATH works', function (array $data) {
    fixtureEnv('ds_env', ['DS_PROJECT_PATH' => $data['ds_project_path']]);

    expect(MakeFileHandler::handle(['file' => '/Users/dan/projects/my-app/dan.php', 'line' => 2]))->toBe($data['expected_result']);
})
->with([
    'there is a trailing slash' => [[
        'ds_project_path' => '/Users/juca/projects/my-app',
        'expected_result' => 'phpstorm://open?file=/Users/juca/projects/my-app/dan.php&line=2',
    ]],

    'there is NOT a trailing slash' => [[
        'ds_project_path' => '/Users/juca/projects/my-app/',
        'expected_result' => 'phpstorm://open?file=/Users/juca/projects/my-app/dan.php&line=2',
    ]],

]);

it('parses file handler from .env', function (array $data) {
    fixtureEnv('ds_env', ['DS_FILE_HANDLER' => $data['ds_file_handler'], 'DS_PROJECT_PATH' => $data['ds_project_path']]);

    expect(MakeFileHandler::handle(['file' => $data['trace_file'], 'line' => 2]))->toBe($data['expected_result']);
})
->with([

    'PHPStorm' => [[
        'trace_file'      => '/Users/dan/projects/my-app/dan.php',
        'ds_file_handler' => 'phpstorm://open?file={filepath}&line={line}',
        'ds_project_path' => null,
        'expected_result' => 'phpstorm://open?file=/Users/dan/projects/my-app/dan.php&line=2',
    ]],

    'PHPStorm (project path without trailing slash)' => [[
        'trace_file'      => '/Users/dan/projects/my-app/dan.php',
        'ds_file_handler' => 'phpstorm://open?file={filepath}&line={line}',
        'ds_project_path' => '/Users/juca/projects/my-app',
        'expected_result' => 'phpstorm://open?file=/Users/juca/projects/my-app/dan.php&line=2',
    ]],

    'PHPStorm (project path with trailing slash)' => [[
        'trace_file'      => '/Users/dan/projects/my-app/dan.php',
        'ds_file_handler' => 'phpstorm://open?file={filepath}&line={line}',
        'ds_project_path' => '/Users/juca/projects/my-app/',
        'expected_result' => 'phpstorm://open?file=/Users/juca/projects/my-app/dan.php&line=2',
    ]],
    'PHPStorm Windows' => [[
        'trace_file'      => 'C:\dan\projects\dan.php',
        'ds_file_handler' => 'phpstorm://open?url=file={filepath}&line={line}',
        'ds_project_path' => null,
        'expected_result' => 'phpstorm://open?url=file=C:\dan\projects\dan.php&line=2',
    ]],

    'VSCode' => [[
        'trace_file'      => '/Users/dan/projects/my-app/dan.php',
        'ds_file_handler' => 'vscode://file/{filepath}:{line}',
        'ds_project_path' => null,
        'expected_result' => 'vscode://file//Users/dan/projects/my-app/dan.php:2',
    ]],

    'VScode Windows' => [[
        'trace_file'      => 'C:\dan\projects\dan.php',
        'ds_file_handler' => 'vscode://file/{filepath}:{line}',
        'ds_project_path' => null,
        'expected_result' => 'vscode://file/C:\dan\projects\dan.php:2',
    ]],

    'VScode Docker' => [[
        'trace_file'      => '/var/www/dan.php',
        'ds_file_handler' => 'vscode://file/{filepath}:{line}',
        'ds_project_path' => '/Users/dan/projects/my-app/',
        'expected_result' => 'vscode://file//Users/dan/projects/my-app/dan.php:2',
    ]],

    'VScode Remote WSL (custom path)' => [[
        'trace_file'      => '/var/www/dan.php',
        'ds_file_handler' => 'vscode://vscode-remote/wsl+Ubuntu{filepath}:{line}',
        'ds_project_path' => 'C:\dan\projects\\',
        'expected_result' => 'vscode://vscode-remote/wsl+UbuntuC:\dan\projects\dan.php:2',
    ]],

    'Sublime Windows' => [[
        'trace_file'      => '/Users/dan/projects/my-app/dan.php',
        'ds_file_handler' => 'subl://open?url=file://{filepath}&line={line}',
        'ds_project_path' => null,
        'expected_result' => 'subl://open?url=file:///Users/dan/projects/my-app/dan.php&line=2',
    ]],

    'Sublime' => [[
        'trace_file'      => 'C:\dan\projects\dan.php',
        'ds_file_handler' => 'subl://open?url=file://{filepath}&line={line}',
        'ds_project_path' => null,
        'expected_result' => 'subl://open?url=file://C:\dan\projects\dan.php&line=2',
    ]],
]);
