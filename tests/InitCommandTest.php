<?php

use Illuminate\Support\Facades\File;

it('publishes the config file', function () {
    $configFile = config_path('laradumps.php');

    if (File::exists($configFile)) {
        File::delete($configFile);
    }

    $this->artisan('ds:init --no-interaction --host=127.0.0.1 --port=9191 --send_queries=true --send_logs=true --send_commands=true --send_scheduled_commands=true --send_livewire=true --auto_invoke=true --ide=phpstorm');

    expect(File::exists($configFile))->toBeTrue();
});

it('updates the config non-interactively', function () {
    $this->artisan('ds:init --no-interaction --host=1.2.3.4 --port=2022 --send_queries=true --send_http_client_requests=true --send_jobs=true --send_commands=true --send_scheduled_commands=true --send_cache=true --send_logs=true --livewire_events=true --livewire_validation=true --livewire_autoclear=true --send_livewire=true --auto_invoke=true --ide=atom');

    expect(config('laradumps.host'))->toBe('1.2.3.4')
        ->and(config('laradumps.port'))->toBe('2022')
        ->and(config('laradumps.send_queries'))->toBeTrue()
        ->and(config('laradumps.send_http_client_requests'))->toBeTrue()
        ->and(config('laradumps.send_jobs'))->toBeTrue()
        ->and(config('laradumps.send_commands'))->toBeTrue()
        ->and(config('laradumps.send_scheduled_commands'))->toBeTrue()
        ->and(config('laradumps.send_cache'))->toBeTrue()
        ->and(config('laradumps.send_log_applications'))->toBeTrue()
        ->and(config('laradumps.send_livewire_components'))->toBeTrue()
        ->and(config('laradumps.send_livewire_events'))->toBeTrue()
        ->and(config('laradumps.send_livewire_dispatch'))->toBeTrue()
        ->and(config('laradumps.send_livewire_failed_validation.enabled'))->toBeTrue()
        ->and(config('laradumps.auto_clear_on_page_reload'))->toBeTrue()
        ->and(config('laradumps.auto_invoke_app'))->toBeTrue()
        ->and(config('laradumps.preferred_ide'))->toBe('atom');

    $this->artisan('ds:init --no-interaction --host=5.6.7.8 --port=2023 --send_queries=false --send_http_client_requests=false --send_jobs=false --send_commands=false --send_scheduled_commands=false --send_cache=false --send_logs=false --send_livewire=false --livewire_events=false  --livewire_validation=false --livewire_autoclear=false --auto_invoke=false  --ide=vscode');

    $this->artisan('config:clear');

    expect(config('laradumps.host'))->toBe('5.6.7.8')
        ->and(config('laradumps.port'))->toBe('2023')
        ->and(config('laradumps.send_queries'))->toBeFalse()
        ->and(config('laradumps.send_http_client_requests'))->toBeFalse()
        ->and(config('laradumps.send_jobs'))->toBeFalse()
        ->and(config('laradumps.send_commands'))->toBeFalse()
        ->and(config('laradumps.send_scheduled_commands'))->toBeFalse()
        ->and(config('laradumps.send_cache'))->toBeFalse()
        ->and(config('laradumps.send_gate'))->toBeFalse()
        ->and(config('laradumps.send_log_applications'))->toBeFalse()
        ->and(config('laradumps.send_livewire_components'))->toBeFalse()
        ->and(config('laradumps.send_livewire_events'))->toBeFalse()
        ->and(config('laradumps.send_livewire_dispatch'))->toBeFalse()
        ->and(config('laradumps.send_livewire_failed_validation.enabled'))->toBeFalse()
        ->and(config('laradumps.auto_clear_on_page_reload'))->toBeFalse()
        ->and(config('laradumps.auto_invoke_app'))->toBeFalse()
        ->and(config('laradumps.preferred_ide'))->toBe('vscode');
});

it('updates the config through the wizard', function () {
    $this->artisan('ds:init')
        ->expectsQuestion('The config file <comment>laradumps.php</comment> already exists. Delete it?', true)
        ->expectsQuestion('Select the App host address', '0.0.0.1')
        ->expectsQuestion('Enter the App Port', '1212')
        ->expectsQuestion('Allow dumping <comment>SQL Queries</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>HTTP Client Requests</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Jobs</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Commands</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Scheduled Commands</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Cache</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Gate & Policy</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Laravel Logs</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Livewire components</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Livewire Events</comment> & <comment>Browser Events (dispatch)</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Livewire failed validation</comment> to the App?', true)
        ->expectsQuestion('Enable <comment>Auto-clear</comment> APP History on page reload?', true)
        ->expectsQuestion('Would you like to invoke the App window on every Dump?', true)
        ->expectsQuestion('What is your preferred IDE for this project?', 'phpstorm');

    expect(config('laradumps.host'))->toBe('0.0.0.1')
        ->and(config('laradumps.port'))->toBe('1212')
        ->and(config('laradumps.send_queries'))->toBeTrue()
        ->and(config('laradumps.send_http_client_requests'))->toBeTrue()
        ->and(config('laradumps.send_jobs'))->toBeTrue()
        ->and(config('laradumps.send_commands'))->toBeTrue()
        ->and(config('laradumps.send_scheduled_commands'))->toBeTrue()
        ->and(config('laradumps.send_cache'))->toBeTrue()
        ->and(config('laradumps.send_gate'))->toBeTrue()
        ->and(config('laradumps.send_log_applications'))->toBeFalse()
        ->and(config('laradumps.send_livewire_components'))->toBeTrue()
        ->and(config('laradumps.send_livewire_failed_validation.enabled'))->toBeTrue()
        ->and(config('laradumps.auto_clear_on_page_reload'))->toBeTrue()
        ->and(config('laradumps.auto_invoke_app'))->toBeTrue()
        ->and(config('laradumps.preferred_ide'))->toBe('phpstorm');

    $this->artisan('ds:init')
        ->expectsQuestion('The config file <comment>laradumps.php</comment> already exists. Delete it?', true)
        ->expectsQuestion('Select the App host address', 'other')
        ->expectsQuestion('Enter the App Host', '5.7.9.11')
        ->expectsQuestion('Enter the App Port', '5555')
        ->expectsQuestion('Allow dumping <comment>SQL Queries</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>HTTP Client Requests</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Jobs</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Commands</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Scheduled Commands</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Cache</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Gate & Policy</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Laravel Logs</comment> to the App?', true)
        ->expectsQuestion('Allow dumping <comment>Livewire components</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Livewire Events</comment> & <comment>Browser Events (dispatch)</comment> to the App?', false)
        ->expectsQuestion('Allow dumping <comment>Livewire failed validation</comment> to the App?', false)
        ->expectsQuestion('Enable <comment>Auto-clear</comment> APP History on page reload?', false)
        ->expectsQuestion('Would you like to invoke the App window on every Dump?', false)
        ->expectsQuestion('What is your preferred IDE for this project?', 'atom');

    expect(config('laradumps.host'))->toBe('5.7.9.11')
        ->and(config('laradumps.port'))->toBe('5555')
        ->and(config('laradumps.send_queries'))->toBeFalse()
        ->and(config('laradumps.send_http_client_requests'))->toBeFalse()
        ->and(config('laradumps.send_jobs'))->toBeFalse()
        ->and(config('laradumps.send_commands'))->toBeFalse()
        ->and(config('laradumps.send_scheduled_commands'))->toBeFalse()
        ->and(config('laradumps.send_cache'))->toBeFalse()
        ->and(config('laradumps.send_gate'))->toBeFalse()
        ->and(config('laradumps.send_log_applications'))->toBeTrue()
        ->and(config('laradumps.send_livewire_components'))->toBeFalse()
        ->and(config('laradumps.send_livewire_failed_validation.enabled'))->toBeFalse()
        ->and(config('laradumps.auto_clear_on_page_reload'))->toBeFalse()
        ->and(config('laradumps.auto_invoke_app'))->toBeFalse()
        ->and(config('laradumps.preferred_ide'))->toBe('atom');
});
