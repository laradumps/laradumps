<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Http\Client\Events\{RequestSending, ResponseReceived};
use Illuminate\Http\Client\{Request, Response};
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{Payload, TableV2Payload};

class HttpClientObserver extends BaseObserver
{
    public function register(): void
    {
        Event::listen(RequestSending::class, fn (RequestSending $event) => $this->handleRequestEvent($event));
        Event::listen(ResponseReceived::class, fn (ResponseReceived $event) => $this->handleResponseEvent($event));
    }

    private function handleRequestEvent(RequestSending $event): void
    {
        if (! $this->isEnabled('http')) {
            return;
        }

        $payload = $this->createRequestPayload($event->request);

        $this->sendPayload($payload);
    }

    private function handleResponseEvent(ResponseReceived $event): void
    {
        if (! $this->isEnabled('http')) {
            return;
        }

        $payload = $this->createResponsePayload($event->request, $event->response);

        $this->sendPayload($payload);
    }

    private function createRequestPayload(Request $request): Payload
    {
        return new TableV2Payload(
            [
                'Method' => $request->method(),
                'URL' => $request->url(),
                'Headers' => $request->headers(),
                'Data' => $request->data(),
                'Body' => $request->body(),
                'Type' => $this->getRequestType($request),
            ],
            screen: 'http',
            label: $this->label ?? 'request'
        );
    }

    private function createResponsePayload(Request $request, Response $response): Payload
    {
        $stats = $response->handlerStats();

        return new TableV2Payload(
            [
                'URL' => $request->url(),
                'Real Request' => ! empty($stats),
                'Success' => $response->successful(),
                'Status' => $response->status(),
                'Headers' => Dumper::dump($response->headers())[0],
                'Body' => $this->getResponseBody($response),
                'Cookies' => Dumper::dump($response->cookies())[0],
                'Size' => $stats['size_download'] ?? null,
                'Connection time' => $stats['connect_time'] ?? null,
                'Duration' => $stats['total_time'] ?? null,
                'Request Size' => $stats['request_size'] ?? null,
            ],
            screen: 'http',
            label: $this->label ?? 'response'
        );
    }

    private function getRequestType(Request $request): string
    {
        return match (true) {
            $request->isJson() => 'Json',
            $request->isMultipart() => 'Multipart',
            default => 'Form',
        };
    }

    private function getResponseBody(Response $response): mixed
    {
        return rescue(
            fn () => $response->json(),
            Dumper::dump($response->body())[0],
            report: false
        );
    }

    private function sendPayload(Payload $payload): void
    {
        (new LaraDumps())->send($payload);
    }
}
