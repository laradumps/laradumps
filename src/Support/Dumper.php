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
            return '❮NULL❯';
        }

        if (is_string($arguments)) {
            if (trim($arguments) === '') {
                return '❮EMPTY STRING❯';
            }

            return '❮STRING❯ ' . $arguments;
        }

        if (is_int($arguments)) {
            return '❮INT❯ ' . strval($arguments);
        }

        if (is_float($arguments)) {
            return '❮FLOAT❯ ' . strval($arguments);
        }

        if (is_bool($arguments)) {
            return '❮BOOL❯ ' . ($arguments  === true ? 'true' : 'false');
        }

        $varCloner = new VarCloner();

        $dumper = new HtmlDumper();

        $htmlDumper = (string) $dumper->dump($varCloner->cloneVar($arguments), true);

        return Str::cut($htmlDumper, '<pre ', '</pre>');
    }
}
