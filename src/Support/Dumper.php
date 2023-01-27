<?php

namespace LaraDumps\LaraDumps\Support;

use Illuminate\Support\Str;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class Dumper
{
    public static function dump(mixed $arguments): array
    {
        $id = Str::uuid();

        if (is_null($arguments)) {
            return [null, $id];
        }

        if (is_string($arguments)) {
            return [$arguments, $id];
        }

        if (is_int($arguments)) {
            return [$arguments, $id];
        }

        if (is_bool($arguments)) {
            return [$arguments, $id];
        }

        $varCloner = new VarCloner();

        $dumper = new HtmlDumper();

        $htmlDumper = (string) $dumper->dump($varCloner->cloneVar($arguments), true);

        $pre = Str::cut($htmlDumper, '<pre ', '</pre>');

        $id = Str::between($pre, 'class=sf-dump id=sf-dump-', ' data-indent-pad="  "');

        return [
            $pre,
            $id,
        ];
    }
}
