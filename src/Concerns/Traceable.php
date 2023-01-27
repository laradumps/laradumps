<?php

namespace LaraDumps\LaraDumps\Concerns;

trait Traceable
{
    public function setTrace(array $trace): array
    {
        return $this->trace = $trace;
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
            if (method_exists($this, 'fileIsInExcludedPath')) {
                return !$this->fileIsInExcludedPath($trace['file']) ? $trace : [];
            }

            return $trace;
        }

        return [];
    }
}
