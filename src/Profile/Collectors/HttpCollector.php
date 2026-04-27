<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Http\Client\Events\{RequestSending, ResponseReceived};
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\{ProfileEntry, ProfileManager};

class HttpCollector
{
    private array $pendingRequests = [];

    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    public function register(): void
    {
        Event::listen(RequestSending::class, function (RequestSending $event) {
            $this->handleRequestSending($event);
        });

        Event::listen(ResponseReceived::class, function (ResponseReceived $event) {
            $this->handleResponseReceived($event);
        });
    }

    private function handleRequestSending(RequestSending $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('http')) {
            return;
        }

        $request = $event->request;
        $url = (string) $request->url();
        $method = $request->method();

        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? $url;

        $name = "http({$method} {$host})";

        $entry = new ProfileEntry(
            type: 'http',
            name: $name,
            startMs: $this->manager->getElapsedMs(),
            durationMs: null,
            parentId: $this->manager->getCurrentParentId(),
            metadata: [
                'method' => $method,
                'url' => $url,
                'host' => $host,
            ],
            origin: $this->manager->captureBacktrace()
        );

        $requestKey = $this->getRequestKey($request);
        $this->pendingRequests[$requestKey] = $entry;

        $this->manager->addEntry($entry);
    }

    private function handleResponseReceived(ResponseReceived $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        $request = $event->request;
        $response = $event->response;
        $requestKey = $this->getRequestKey($request);

        if (isset($this->pendingRequests[$requestKey])) {
            $entry = $this->pendingRequests[$requestKey];
            $entry->stop($this->manager->getElapsedMs());
            $entry->metadata['status'] = $response->status();
            $entry->metadata['success'] = $response->successful();
            unset($this->pendingRequests[$requestKey]);
        }
    }

    private function getRequestKey($request): string
    {
        return md5($request->method().$request->url().spl_object_id($request));
    }
}
