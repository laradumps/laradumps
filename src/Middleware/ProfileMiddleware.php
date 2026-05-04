<?php

namespace LaraDumps\LaraDumps\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumpsCore\Actions\Config;
use Symfony\Component\HttpFoundation\Response;

class ProfileMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldProfile()) {
            return $next($request);
        }

        $label = $this->buildLabel($request);

        app(LaraDumps::class)->startProfile($label);

        $response = $next($request);

        app(LaraDumps::class)->stopProfile();

        return $response;
    }

    private function shouldProfile(): bool
    {
        if (! boolval(Config::get('observers.profile', false))) {
            return false;
        }

        if (! boolval(Config::get('profile.auto_middleware', false))) {
            return false;
        }

        return true;
    }

    private function buildLabel(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        return "{$method} /{$path}";
    }
}
