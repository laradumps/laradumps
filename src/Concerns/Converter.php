<?php

namespace LaraDumps\LaraDumps\Concerns;

use LaraDumps\LaraDumps\Support\Dumper;

trait Converter
{
    public function convertToPrimitive(mixed $argument): mixed
    {
        if (is_null($argument)) {
            return null;
        }

        if (is_string($argument)) {
            return $argument;
        }

        if (is_int($argument)) {
            return $argument;
        }

        if (is_bool($argument)) {
            return $argument;
        }

        return Dumper::dump($argument);
    }
}
