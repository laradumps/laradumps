<?php

use LaraDumps\LaraDumps\Actions\MakeConfigValidatonRules;

test('matches the config validation snapshot', function () {
    expect(MakeConfigValidatonRules::handle())
        ->toMatchArray([
            'DS_AUTO_INVOKE_APP'                 => 'required|boolean',
            'DS_SEND_QUERIES'                    => 'required|boolean',
            'DS_SEND_LOGS'                       => 'required|boolean',
            'DS_SEND_LIVEWIRE_COMPONENTS'        => 'required|boolean',
            'DS_LIVEWIRE_EVENTS'                 => 'required|boolean',
            'DS_LIVEWIRE_DISPATCH'               => 'required|boolean',
            'DS_SEND_LIVEWIRE_FAILED_VALIDATION' => 'required|boolean',
            'DS_AUTO_CLEAR_ON_PAGE_RELOAD'       => 'required|boolean',
            'DS_PREFERRED_IDE'                   => 'required|in:atom,phpstorm,sublime,vscode,vscode_remote',
        ]);
});
