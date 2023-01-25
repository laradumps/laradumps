<?php

namespace LaraDumps\LaraDumps\Observers;

use LaraDumps\LaraDumps\Support\Dumper;
use Illuminate\Http\Client\Events\{RequestSending, ResponseReceived};
use Illuminate\Http\Client\{Request, Response};
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{Payload, TableV2Payload};

class HttpClientObserver
{
    private bool $enabled = false;

    private ?string $label = null;

    private array $trace = [];

    public function register(): void
    {
        Event::listen(RequestSending::class, function (RequestSending $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $this->sendPayload(
                $this->handleRequest($event->request)
            );
        });

        Event::listen(ResponseReceived::class, function (ResponseReceived $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $this->sendPayload(
                $this->handleResponse($event->request, $event->response)
            );
        });
    }

    public function enable(string $label = null): void
    {
        $this->label = $label;

        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        $this->trace = array_slice($this->findSource(), 0, 5)[0] ?? [];

        if (!boolval(config('laradumps.send_http_client'))) {
            return $this->enabled;
        }

        return true;
    }

    public function setTrace(array $trace): array
    {
        return $this->trace = $trace;
    }

    protected function getRequestType(Request $request): string
    {
        if ($request->isJson()) {
            return 'Json';
        }

        if ($request->isMultipart()) {
            return 'Multipart';
        }

        return 'Form';
    }

    protected function handleRequest(Request $request): Payload
    {
        return new TableV2Payload([
            'Method'  => $request->method(),
            'URL'     => $request->url(),
            'Headers' => $request->headers(),
            'Data'    => $request->data(),
            'Body'    => $request->body(),
            'Type'    => $this->getRequestType($request),
        ], 'Http', 'http-client');
    }

    protected function handleResponse(Request $request, Response $response): Payload
    {
        return new TableV2Payload([
            'URL'             => $request->url(),
            'Real Request'    => !empty($response->handlerStats()),
            'Success'         => $response->successful(),
            'Status'          => $response->status(),
            'Headers'         => Dumper::dump($response->headers()),
            'Body'            => rescue(function () use ($response) {
                return $response->json();
            }, Dumper::dump($response->body()), false),
            'Cookies'         => Dumper::dump($response->cookies()),
            'Size'            => $response->handlerStats()['size_download'] ?? null,
            'Connection time' => $response->handlerStats()['connect_time']  ?? null,
            'Duration'        => $response->handlerStats()['total_time']    ?? null,
            'Request Size'    => $response->handlerStats()['request_size']  ?? null,
        ], 'Http', 'http-client');
    }

    protected function findSource(): array
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50);

        $sources = [];

        foreach ($stack as $trace) {
            $sources[] = $this->parseTrace($trace);
        }

        return array_filter($sources);
    }

    protected function parseTrace(array $trace): array
    {
        if (isset($trace['class']) && isset($trace['file'])) {
            return $trace;
        }

        return [];
    }

    private function sendPayload(Payload $payload): void
    {
        $dumps = new LaraDumps(trace: $this->trace);

        $dumps->send($payload);

        if ($this->label) {
            $dumps->label($this->label);
        }
    }
}
