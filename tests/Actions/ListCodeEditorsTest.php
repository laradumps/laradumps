<?php

use LaraDumps\LaraDumps\Actions\ListCodeEditors;

test('matches the editors snapshot', function () {
    expect(ListCodeEditors::handle())
        ->toMatchArray([
            'atom'          => 'Atom',
            'phpstorm'      => 'PhpStorm',
            'sublime'       => 'Sublime',
            'vscode'        => 'VS Code',
            'vscode_remote' => 'VS Code (Remote/WSL)',
        ]);
});
