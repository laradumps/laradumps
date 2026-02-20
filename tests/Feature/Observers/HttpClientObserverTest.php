<?php

use Illuminate\Http\Client\Response;
use LaraDumps\LaraDumps\Observers\HttpClientObserver;

it('handles non-seekable stream responses without errors', function () {
    $stream = Mockery::mock(\Psr\Http\Message\StreamInterface::class);
    $stream->shouldReceive('isSeekable')->andReturn(false);

    $psrResponse = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
    $psrResponse->shouldReceive('getBody')->andReturn($stream);

    $response = Mockery::mock(Response::class)->makePartial();
    $response->shouldReceive('toPsrResponse')->andReturn($psrResponse);

    $observer = new HttpClientObserver();

    $reflection = new ReflectionClass($observer);
    $method = $reflection->getMethod('getResponseBody');

    $result = $method->invoke($observer, $response);

    expect($result)->toBe('Stream Response');
});

it('rewinds seekable streams after reading', function () {
    $stream = Mockery::mock(\Psr\Http\Message\StreamInterface::class);
    $stream->shouldReceive('isSeekable')->andReturn(true);
    $stream->shouldReceive('rewind')->once();

    $psrResponse = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
    $psrResponse->shouldReceive('getBody')->andReturn($stream);

    $response = Mockery::mock(Response::class);
    $response->shouldReceive('toPsrResponse')->andReturn($psrResponse);
    $response->shouldReceive('json')->andReturn(['data' => 'test']);
    $response->shouldReceive('body')->andReturn('{"data":"test"}');

    $observer = new HttpClientObserver();

    $reflection = new ReflectionClass($observer);
    $method = $reflection->getMethod('getResponseBody');

    $result = $method->invoke($observer, $response);

    expect($result)->toBe(['data' => 'test']);
});

it('falls back to body dump when json parsing fails', function () {
    // Create a mock seekable stream
    $stream = Mockery::mock(\Psr\Http\Message\StreamInterface::class);
    $stream->shouldReceive('isSeekable')->andReturn(true);
    $stream->shouldReceive('rewind')->once();

    // Create a mock PSR response
    $psrResponse = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
    $psrResponse->shouldReceive('getBody')->andReturn($stream);

    // Create a mock Laravel Response
    $response = Mockery::mock(Response::class)->makePartial();
    $response->shouldReceive('toPsrResponse')->andReturn($psrResponse);
    $response->shouldReceive('json')->andThrow(new \Exception('Invalid JSON'));
    $response->shouldReceive('body')->andReturn('plain text response');

    // Create observer instance
    $observer = new HttpClientObserver();

    // Use reflection to call the private getResponseBody method
    $reflection = new ReflectionClass($observer);
    $method = $reflection->getMethod('getResponseBody');

    $result = $method->invoke($observer, $response);

    expect($result)->toBeString();
});
