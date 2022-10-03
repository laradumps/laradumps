<?php

use LaraDumps\LaraDumps\Actions\{ListCodeEditors, ListConfigKeys};

test('matches the config keys snapshot', function () {
    $ListConfigKeys = ListConfigKeys::handle();

    //set all default values to nu
    $ListConfigKeys = $ListConfigKeys->transform(function ($config) {
        $config['current_value'] = null;

        return $config;
    });

    expect($ListConfigKeys)->toMatchArray(
        [
            [
                'config_key'    => 'laradumps.host',
                'env_key'       => 'DS_APP_HOST',
                'title'         => 'Desktop App Host',
                'description'   => 'LaraDumps Desktop App address',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=host',
                'default_value' => '127.0.0.1',
                'current_value' => null,
                'type'          => 'text',
            ],
            [
                'config_key'    => 'laradumps.auto_invoke_app',
                'env_key'       => 'DS_AUTO_INVOKE_APP',
                'title'         => 'Auto-Invoke',
                'description'   => 'Auto-invoke and focus Desktop App on new dumps/events',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=auto-invoke',
                'default_value' => true,
                'current_value' => null,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_queries',
                'env_key'       => 'DS_SEND_QUERIES',
                'title'         => 'Dump SQL',
                'description'   => 'Send SQL queries to the Desktop App',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=sql-queries',
                'default_value' => true,
                'current_value' => null,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_log_applications',
                'env_key'       => 'DS_SEND_LOGS',
                'title'         => 'Dump Logs',
                'description'   => 'Send Laravel Logs to the Desktop App',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=laravel-logs',
                'default_value' => true,
                'current_value' => null,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_livewire_components',
                'env_key'       => 'DS_SEND_LIVEWIRE_COMPONENTS',
                'title'         => 'Livewire components',
                'description'   => 'Track and dump Livewire components',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=livewire-components',
                'default_value' => true,
                'current_value' => null,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_livewire_events',
                'env_key'       => 'DS_LIVEWIRE_EVENTS',
                'title'         => 'Livewire Events',
                'description'   => 'Track and dump Livewire events',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=livewire-events',
                'default_value' => true,
                'current_value' => null,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_livewire_dispatch',
                'env_key'       => 'DS_LIVEWIRE_DISPATCH',
                'title'         => 'Livewire Browser Events',
                'description'   => 'Track and dump Livewire Browser Events',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=livewire-browser-events',
                'default_value' => true,
                'current_value' => null,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_livewire_failed_validation.enabled',
                'env_key'       => 'DS_SEND_LIVEWIRE_FAILED_VALIDATION',
                'title'         => 'Livewire failed validation',
                'description'   => 'Track and dump Livewire failed validations',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=livewire-validation',
                'default_value' => true,
                'current_value' => null,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.auto_clear_on_page_reload',
                'env_key'       => 'DS_AUTO_CLEAR_ON_PAGE_RELOAD',
                'title'         => 'Auto-clear App',
                'description'   => 'Enable Auto-clear the Dektop App when page is reloaded',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=livewire-auto-clear',
                'default_value' => false,
                'current_value' => null,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.preferred_ide',
                'env_key'       => 'DS_PREFERRED_IDE',
                'title'         => 'Default IDE',
                'description'   => 'Select your preferred IDE for this project',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=preferred-ide',
                'default_value' => 'phpstorm',
                'options'       => ListCodeEditors::handle(),
                'type'          => 'select',
                'current_value' => null,
            ],
        ]
    );
});
