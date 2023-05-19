<?php

namespace LaraDumps\LaraDumps\Actions;

use LaraDumps\LaraDumps\Payloads\Payload;

final class SendPayload
{
    /**
     * Sends Payload to the Desktop App
     *
     */
    public static function handle(string $appUrl, array|Payload $payload): bool
    {
        $curlRequest = curl_init();

        curl_setopt_array($curlRequest, [
            CURLOPT_POST              => true,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_FOLLOWLOCATION    => true,
            CURLOPT_HTTPHEADER        => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS        => json_encode($payload),
            CURLOPT_URL               => $appUrl,
            CURLOPT_CONNECTTIMEOUT_MS => 10,
        ]);

        curl_close($curlRequest);

        return boolval(curl_exec($curlRequest));
    }
}
