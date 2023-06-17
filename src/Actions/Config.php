<?php

namespace LaraDumps\LaraDumps\Actions;

final class Config extends \LaraDumps\LaraDumpsCore\Actions\Config
{
    protected static function getEnvironment(): array
    {
        return array_merge(parent::getEnvironment(), [
            'send_queries'              => 'DS_SEND_QUERIES',
            'send_logs_applications'    => 'DS_SEND_LOGS_APPLICATIONS',
            'send_logs_vendors'         => 'DS_SEND_LOGS_VENDOR',
            'send_http_client_requests' => 'DS_SEND_HTTP_CLIENT_REQUESTS',
            'send_jobs'                 => 'DS_SEND_JOBS',
            'send_cache'                => 'DS_SEND_CACHE',
            'send_commands'             => 'DS_SEND_COMMANDS',
            'send_http_client'          => 'DS_SEND_HTTP',
            'send_scheduled_commands'   => 'DS_SEND_SCHEDULED_COMMANDS',
            'send_gate'                 => 'DS_SEND_GATE',
            'send_logs_deprecated'      => 'DS_SEND_LOGS_DEPRECATED',
            'send_mail'                 => 'DS_SEND_MAIL',
        ]);
    }
}
