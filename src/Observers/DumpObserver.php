<?php

namespace LaraDumps\LaraDumps\Observers;

use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumpsCore\Actions\Config;
use Symfony\Component\VarDumper\VarDumper;

class MultiDumpHandler
{
    protected array $handlers = [];

    public function dump(mixed $value): void
    {
        foreach ($this->handlers as $handler) {
            $handler($value);
        }
    }

    public function addHandler(callable $callable = null): self
    {
        $this->handlers[] = $callable;

        return $this;
    }

    public function resetHandlers(): void
    {
        $this->handlers = [];
    }
}

class DumpObserver
{
    protected array $dumps = [];

    protected static bool $registeredHandler = false;

    public function register(): void
    {
        $multiDumpHandler = new MultiDumpHandler();

        app()->singleton(MultiDumpHandler::class, fn () => $multiDumpHandler);

        if (!static::$registeredHandler) {
            static::$registeredHandler = true;

            $multiDumpHandler->resetHandlers();

            $originalHandler = VarDumper::setHandler(fn ($args) => $multiDumpHandler->dump($args));

            if ($originalHandler && $this->displayOriginalDump()) {
                $multiDumpHandler->addHandler($originalHandler);
            }

            $multiDumpHandler->addHandler(function ($args) {
                if ($this->isEnabled()) {
                    app(LaraDumps::class)->write($args);
                }
            });
        }
    }

    public function isEnabled(): bool
    {
        return (bool) Config::get('observers.dump', false);
    }

    public function displayOriginalDump(): bool
    {
        if (runningInTest()) {
            return true;
        }

        return (bool) Config::get('observers.original_dump', true);
    }
}
