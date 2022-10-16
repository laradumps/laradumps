<?php

use Illuminate\Support\Facades\File;

it('publishes the config file', function () {
    $configFile = config_path('laradumps.php');

    if (File::exists($configFile)) {
        File::delete($configFile);
    }

    $this->artisan('ds:init --no-interaction --host=127.0.0.1 --send_queries=true --send_logs=true --send_livewire=true --auto_invoke=true --ide=phpstorm');

    expect(File::exists($configFile))->toBeTrue();
});

it('updates the config non-interactively', function () {
    $this->artisan('ds:init --no-interaction --host=1.2.3.4 --send_queries=true --send_logs=true --livewire_events=true --livewire_dispatch=true --livewire_validation=true --livewire_autoclear=true --send_livewire=true --auto_invoke=true --ide=atom');

    expect(config('laradumps.host'))->toBe('1.2.3.4');
    expect(config('laradumps.send_queries'))->toBeTrue();
    expect(config('laradumps.send_log_applications'))->toBeTrue();
    expect(config('laradumps.send_livewire_components'))->toBeTrue();
    expect(config('laradumps.send_livewire_events'))->toBeTrue();
    expect(config('laradumps.send_livewire_dispatch'))->toBeTrue();
    expect(config('laradumps.send_livewire_failed_validation.enabled'))->toBeTrue();
    expect(config('laradumps.auto_clear_on_page_reload'))->toBeTrue();
    expect(config('laradumps.auto_invoke_app'))->toBeTrue();
    expect(config('laradumps.preferred_ide'))->toBe('atom');

    $this->artisan('ds:init --no-interaction --host=5.6.7.8 --send_queries=false --send_logs=false --send_livewire=false --livewire_events=false --livewire_dispatch=false --livewire_validation=false --livewire_autoclear=false --auto_invoke=false  --ide=vscode');
    $this->artisan('config:clear');

    expect(config('laradumps.host'))->toBe('5.6.7.8');
    expect(config('laradumps.port'))->toBe(9191);
    expect(config('laradumps.send_queries'))->toBeFalse();
    expect(config('laradumps.send_log_applications'))->toBeFalse();
    expect(config('laradumps.send_livewire_components'))->toBeFalse();
    expect(config('laradumps.send_livewire_events'))->toBeFalse();
    expect(config('laradumps.send_livewire_dispatch'))->toBeFalse();
    expect(config('laradumps.send_livewire_failed_validation.enabled'))->toBeFalse();
    expect(config('laradumps.auto_clear_on_page_reload'))->toBeFalse();
    expect(config('laradumps.auto_invoke_app'))->toBeFalse();
    expect(config('laradumps.preferred_ide'))->toBe('vscode');
});

it('updates the config through the wizard', function () {
    $this->artisan('ds:init')
        ->expectsQuestion('The config file <comment>laradumps.php</comment> already exists. Delete it?', true)
        ->expectsQuestion('Select the App host address', '0.0.0.1')
        ->expectsQuestion('What is your preferred IDE for this project?', 'phpstorm');

    expect(config('laradumps.host'))->toBe('0.0.0.1');
    expect(config('laradumps.preferred_ide'))->toBe('phpstorm');

    $this->artisan('ds:init')
        ->expectsQuestion('The config file <comment>laradumps.php</comment> already exists. Delete it?', true)
        ->expectsQuestion('Select the App host address', 'other')
        ->expectsQuestion('Enter the App Host', '5.7.9.11')
        ->expectsQuestion('What is your preferred IDE for this project?', 'atom');

    expect(config('laradumps.host'))->toBe('5.7.9.11');
    expect(config('laradumps.preferred_ide'))->toBe('atom');
});

test('init sets default parameters', function () {
    $configFile = config_path('laradumps.php');

    if (File::exists($configFile)) {
        File::delete($configFile);
    }

    $this->artisan('ds:init')
        ->expectsQuestion('Select the App host address', '0.0.0.1')
        ->expectsQuestion('What is your preferred IDE for this project?', 'phpstorm');

    expect(config('laradumps.port'))->toBe(9191);
    expect(config('laradumps.send_queries'))->toBeFalse();
    expect(config('laradumps.send_log_applications'))->toBeFalse();
    expect(config('laradumps.send_livewire_components'))->toBeTrue();
    expect(config('laradumps.send_livewire_failed_validation.enabled'))->toBeTrue();
    expect(config('laradumps.auto_clear_on_page_reload'))->toBeFalse();
    expect(config('laradumps.auto_invoke_app'))->toBeTrue();
});
