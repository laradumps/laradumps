<?php

namespace LaraDumps\LaraDumps\Concerns;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

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

        $cloner = new VarCloner();

        $dumper = new HtmlDumper();

        $clonedArgument = $cloner->cloneVar($argument);

        return $dumper->dump($clonedArgument, true);
    }
}
