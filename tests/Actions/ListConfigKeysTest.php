<?php

use LaraDumps\LaraDumps\Actions\{ListCodeEditors, ListConfigKeys};

test('matches the config keys snapshot', function () {
    $ListConfigKeys = ListConfigKeys::handle();

    //set all default values to true
    $ListConfigKeys = $ListConfigKeys->transform(function ($config) {
        $config['current_value'] = true;

        return $config;
    });

    expect($ListConfigKeys)->toMatchArray(
        [
            [
                'config_key'    => 'laradumps.auto_invoke_app',
                'env_key'       => 'DS_AUTO_INVOKE_APP',
                'title'         => 'Auto-Invoke',
                'description'   => 'Enable auto-invoking to focus Desktop App whenever a new dump is received.',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=auto-invoke',
                'default_value' => true,
                'current_value' => true,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_queries',
                'env_key'       => 'DS_SEND_QUERIES',
                'title'         => 'Dump SQL',
                'description'   => 'Send Queries to the App',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=sql-queries',
                'default_value' => true,
                'current_value' => true,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_log_applications',
                'env_key'       => 'DS_SEND_LOGS',
                'title'         => 'Dump Logs',
                'description'   => 'Send Logs to the App',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=laravel-logs',
                'default_value' => true,
                'current_value' => true,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_livewire_components',
                'env_key'       => 'DS_SEND_LIVEWIRE_COMPONENTS',
                'title'         => 'Livewire components',
                'description'   => 'Allow dumping Livewire components to the App.',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=livewire-components',
                'default_value' => true,
                'current_value' => true,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_livewire_events',
                'env_key'       => 'DS_LIVEWIRE_EVENTS',
                'title'         => 'Livewire Events',
                'description'   => 'Allow dumping Livewire Events to the App.',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=livewire-events',
                'default_value' => true,
                'current_value' => true,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_livewire_dispatch',
                'env_key'       => 'DS_LIVEWIRE_DISPATCH',
                'title'         => 'Livewire Browser Events',
                'description'   => 'Allow dumping Browser Events dispatch to the Desktop App.',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=livewire-browser-events',
                'default_value' => true,
                'current_value' => true,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.send_livewire_failed_validation.enabled',
                'env_key'       => 'DS_SEND_LIVEWIRE_FAILED_VALIDATION',
                'title'         => 'Livewire failed validation',
                'description'   => 'Allow dumping to the App',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=livewire-validation',
                'default_value' => true,
                'current_value' => true,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.auto_clear_on_page_reload',
                'env_key'       => 'DS_AUTO_CLEAR_ON_PAGE_RELOAD',
                'title'         => 'Auto-clear App',
                'description'   => 'Enable Auto-clear App dumps on page reload.',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=livewire-auto-clear',
                'default_value' => false,
                'current_value' => true,
                'type'          => 'toggle',
            ],
            [
                'config_key'    => 'laradumps.preferred_ide',
                'env_key'       => 'DS_PREFERRED_IDE',
                'title'         => 'Default IDE',
                'description'   => 'Select your preferred IDE for this project.',
                'doc_link'      => 'https://laradumps.dev/#/laravel/get-started/configuration?id=preferred-ide',
                'default_value' => 'phpstorm',
                'options'       => ListCodeEditors::handle(),
                'type'          => 'select',
                'current_value' => true,
            ],
        ]
    );
});
