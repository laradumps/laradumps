<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumpsCore\Payloads\Payload;

class InstallationPayload extends Payload
{
    public function type(): string
    {
        return 'install';
    }

    public function content(): array
    {
        return [
            'environment' => Config::getAvailableConfig(),
            'env_path'    => rtrim(strval(getcwd()), '\/') . DIRECTORY_SEPARATOR . '.env',
            'ide_list'    => [
                [
                    'id'                 => 'phpstorm',
                    'label'              => 'PhpStorm',
                    'defaultEnvironment' => 'phpstorm://open?file={filepath}&line={line}',
                ],
                [
                    'id'                 => 'atom',
                    'label'              => 'Atom',
                    'defaultEnvironment' => 'atom://core/open/file?filename={filepath}&line={line}',
                ],
                [
                    'id'                 => 'sublime',
                    'label'              => 'Sublime',
                    'defaultEnvironment' => 'subl://open?url=file://{filepath}&line={line}',
                ],
                [
                    'id'                 => 'vs_code',
                    'label'              => 'Vs Code',
                    'defaultEnvironment' => 'vscode://file/{filepath}:{line}',
                ],
                [
                    'id'                 => 'vs_code_remote',
                    'label'              => 'Vs Code Remote',
                    'defaultEnvironment' => 'vscode://vscode-remote/',
                ],
                [
                    'id'                 => 'custom',
                    'label'              => 'Custom',
                    'defaultEnvironment' => '',
                ],
            ],
        ];
    }
}
