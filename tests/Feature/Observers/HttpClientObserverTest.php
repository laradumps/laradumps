<?php

use Illuminate\Http\Client\Response;
use LaraDumps\LaraDumps\Observers\HttpClientObserver;

it('handles non-seekable stream responses without errors', function () {
    // Create a mock non-seekable stream
    $stream = Mockery::mock(\Psr\Http\Message\StreamInterface::class);
    $stream->shouldReceive('isSeekable')->andReturn(false);

    // Create a mock PSR response
    $psrResponse = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
    $psrResponse->shouldReceive('getBody')->andReturn($stream);

    // Create a mock Laravel Response
    $response = Mockery::mock(Response::class)->makePartial();
    $response->shouldReceive('toPsrResponse')->andReturn($psrResponse);

    // Create observer instance
    $observer = new HttpClientObserver();

    // Use reflection to call the private getResponseBody method
    $reflection = new ReflectionClass($observer);
    $method = $reflection->getMethod('getResponseBody');
    $method->setAccessible(true);

    $result = $method->invoke($observer, $response);

    expect($result)->toBe('Stream Response');
});

it('rewinds seekable streams after reading', function () {
    // Create a mock seekable stream
    $stream = Mockery::mock(\Psr\Http\Message\StreamInterface::class);
    $stream->shouldReceive('isSeekable')->andReturn(true);
    $stream->shouldReceive('rewind')->once();

    // Create a mock PSR response
    $psrResponse = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
    $psrResponse->shouldReceive('getBody')->andReturn($stream);

    // Create a mock Laravel Response
    $response = Mockery::mock(Response::class);
    $response->shouldReceive('toPsrResponse')->andReturn($psrResponse);
    $response->shouldReceive('json')->andReturn(['data' => 'test']);
    $response->shouldReceive('body')->andReturn('{"data":"test"}');

    // Create observer instance
    $observer = new HttpClientObserver();

    // Use reflection to call the private getResponseBody method
    $reflection = new ReflectionClass($observer);
    $method = $reflection->getMethod('getResponseBody');
    $method->setAccessible(true);

    $result = $method->invoke($observer, $response);

    expect($result)->toBe(['data' => 'test']);
    // The expectation that rewind() was called once is verified by Mockery
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
    $method->setAccessible(true);

    $result = $method->invoke($observer, $response);

    expect($result)->toBeString();
    // The result will be the dumped version of 'plain text response'
});
