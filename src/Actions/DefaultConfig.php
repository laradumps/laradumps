<?php

namespace LaraDumps\LaraDumps\Actions;

use LaraDumps\LaraDumpsCore\Actions\Config;
use Symfony\Component\Yaml\Yaml;

class DefaultConfig
{
    public static function toArray(): array
    {
        return array_replace_recursive(Config::defaults(), self::packageDefaults());
    }

    public static function packageDefaults(): array
    {
        /** @var array<string, mixed> $package */
        $package = (array) Yaml::parseFile(self::packageBasePath());

        return $package;
    }

    public static function packageBasePath(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Commands'.DIRECTORY_SEPARATOR.'laradumps-base.yaml';
    }
}
