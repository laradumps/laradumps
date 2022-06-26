<?php

use Illuminate\Support\Facades\File;

it('publishes the config file', function () {
    $configFile = config_path('laradumps.php');

    if (!File::exists($configFile)) {
        File::delete($configFile);
    }

    $this->artisan('ds:init --no-interaction --host=127.0.0.1 --port=9191 --send_queries=true --send_logs=true --send_livewire=true --ide=phpstorm');

    expect(File::exists($configFile))->toBeTrue();
});

it('updates the config non-interactively', function () {
    $this->artisan('ds:init --no-interaction --host=1.2.3.4 --port=2022 --send_queries=true --send_logs=true --send_livewire=true --ide=atom');

    expect(config('laradumps.host'))->toBe('1.2.3.4');
    expect(config('laradumps.port'))->toBe('2022');
    expect(config('laradumps.send_queries'))->toBeTrue();
    expect(config('laradumps.send_log_applications'))->toBeTrue();
    expect(config('laradumps.send_livewire_components'))->toBeTrue();
    expect(config('laradumps.preferred_ide'))->toBe('atom');

    $this->artisan('ds:init --no-interaction --host=5.6.7.8 --port=2023 --send_queries=false --send_logs=false --send_livewire=false --ide=vscode');
    $this->artisan('config:clear');

    expect(config('laradumps.host'))->toBe('5.6.7.8');
    expect(config('laradumps.port'))->toBe('2023');
    expect(config('laradumps.send_queries'))->toBeFalse();
    expect(config('laradumps.send_log_applications'))->toBeFalse();
    expect(config('laradumps.send_livewire_components'))->toBeFalse();
    expect(config('laradumps.preferred_ide'))->toBe('vscode');
});

it('updates the config through the wizard', function () {
    $this->artisan('ds:init')
        ->expectsQuestion('Select the App host address', '0.0.0.1')
        ->expectsQuestion('Enter the App Port', '1212')
        ->expectsQuestion('Allow dumping <comment>SQL Queries</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Laravel Logs</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Livewire components</comment> to the App?', true)
        ->expectsQuestion('What is your preferred for this project?', 'phpstorm');

    expect(config('laradumps.host'))->toBe('0.0.0.1');
    expect(config('laradumps.port'))->toBe('1212');
    expect(config('laradumps.send_queries'))->toBeTrue();
    expect(config('laradumps.send_log_applications'))->toBeFalse();
    expect(config('laradumps.send_livewire_components'))->toBeTrue();
    expect(config('laradumps.preferred_ide'))->toBe('phpstorm');

    $this->artisan('ds:init')
        ->expectsQuestion('Select the App host address', 'other')
        ->expectsQuestion('Enter the App Host', '5.7.9.11')
        ->expectsQuestion('Enter the App Port', '1212')
        ->expectsQuestion('Allow dumping <comment>SQL Queries</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Laravel Logs</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Livewire components</comment> to the App?', true)
        ->expectsQuestion('What is your preferred for this project?', 'phpstorm');

    expect(config('laradumps.host'))->toBe('5.7.9.11');
});
