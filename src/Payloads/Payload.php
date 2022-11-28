<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumps\Support\IdeHandle;

abstract class Payload
{
    private string $notificationId;

    private array $trace = [];

    protected array $typesWithTrace = [
        'table',
        'validate',
        'query',
        'queries',
        'dump',
        'events',
        'diff',
        'model',
        'time-track',
        'coffee',
        'json',
        'log',
    ];

    private ?bool $autoInvokeApp = null;

    private ?string $dumpId = null;

    abstract public function type(): string;

    public function trace(array $trace): void
    {
        $this->trace = $trace;
    }

    public function notificationId(string $notificationId): void
    {
        $this->notificationId = $notificationId;
    }

    public function content(): array
    {
        return [];
    }

    public function ideHandle(): array
    {
        $trace = new IdeHandle(trace: $this->trace);

        return $trace->ideHandle();
    }

    public function customHandle(): array
    {
        return [];
    }

    public function autoInvokeApp(?bool $enable = null): void
    {
        $this->autoInvokeApp = $enable;
    }

    public function dumpId(string $id): void
    {
        $this->dumpId = $id;
    }

    public function toArray(): array
    {
        $ideHandle = $this->customHandle();
        if (in_array($this->type(), $this->typesWithTrace)) {
            $ideHandle = $this->ideHandle();
        }

        $pusherConfig = '';
        if (boolval(config('laradumps.send_livewire_components_highlight'))) {
            $pusherConfig = config('broadcasting.connections.pusher');
        }

        return [
            'id'         => $this->notificationId,
            'request_id' => LARADUMPS_REQUEST_ID,
            'dumpId'     => $this->dumpId,
            'type'       => $this->type(),
            'meta'       => [
                'laradumps_version' => $this->getInstalledVersion(),
                'auto_invoke_app'   => $this->autoInvokeApp ?? boolval(config('laradumps.auto_invoke_app')),
            ],
            'content'   => $this->content(),
            'ideHandle' => $ideHandle,
            'dateTime'  => now()->format('H:i:s'),
            'pusher'    => $pusherConfig,
        ];
    }

    public function getInstalledVersion(): ?string
    {
        if (class_exists(\Composer\InstalledVersions::class)) {
            try {
                return \Composer\InstalledVersions::getVersion('laradumps/laradumps');
            } catch (\Exception) {
                return '0.0.0';
            }
        }

        return '0.0.0';
    }
}
