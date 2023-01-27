<?php

namespace LaraDumps\LaraDumps\Support;

use Illuminate\Support\Str;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class Dumper
{
    public static function dump(mixed $arguments): mixed
    {
        if (is_null($arguments)) {
            return null;
        }

        if (is_string($arguments)) {
            return $arguments;
        }

        if (is_int($arguments)) {
            return $arguments;
        }

        if (is_bool($arguments)) {
            return $arguments;
        }

        $varCloner = new VarCloner();

        $dumper = new HtmlDumper();

        $htmlDumper = (string) $dumper->dump($varCloner->cloneVar($arguments), true);

        return Str::cut($htmlDumper, '<pre ', '</pre>');
    }
}
