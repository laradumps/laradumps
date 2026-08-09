<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Http\Client\Events\{RequestSending, ResponseReceived};
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\{ProfileManager, Tracing\ProfileSpan};

class HttpCollector
{
    /** @var array<string, array{span: ProfileSpan, metadata: array}> */
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

        $metadata = [
            'method' => $method,
            'url' => $url,
            'host' => $host,
        ];

        $origin = $this->manager->captureBacktrace();
        $requestKey = $this->getRequestKey($request);

        $span = $this->manager->tracer()?->beginSpan('http', $name, $metadata, $origin);

        if ($span !== null) {
            $this->pendingRequests[$requestKey] = ['span' => $span, 'metadata' => $metadata];
        }
    }

    private function handleResponseReceived(ResponseReceived $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        $request = $event->request;
        $response = $event->response;
        $requestKey = $this->getRequestKey($request);

        if (! isset($this->pendingRequests[$requestKey])) {
            return;
        }

        $pending = $this->pendingRequests[$requestKey];
        unset($this->pendingRequests[$requestKey]);

        $metadata = $pending['metadata'];
        $metadata['status'] = $response->status();
        $metadata['success'] = $response->successful();

        $this->manager->tracer()?->endSpan($pending['span'], $metadata);
    }

    private function getRequestKey(Request $request): string
    {
        return md5($request->method().$request->url().spl_object_id($request));
    }
}
