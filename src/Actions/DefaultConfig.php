<?php

namespace LaraDumps\LaraDumps\Actions;

use LaraDumps\LaraDumpsCore\Actions\Config;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;

class DefaultConfig
{
    public static function toArray(): array
    {
        /** @var array<string, mixed> $core */
        $core = (array) Yaml::parseFile(self::coreBasePath());

        /** @var array<string, mixed> $package */
        $package = (array) Yaml::parseFile(self::packageBasePath());

        return array_replace_recursive($core, $package);
    }

    public static function coreBasePath(): string
    {
        $fileName = (new ReflectionClass(Config::class))->getFileName();

        $coreActionsDir = $fileName !== false
            ? dirname($fileName)
            : appBasePath().'vendor/laradumps/laradumps-core/src/Actions';

        return $coreActionsDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Commands'.DIRECTORY_SEPARATOR.'laradumps-base.yaml';
    }

    public static function packageBasePath(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Commands'.DIRECTORY_SEPARATOR.'laradumps-base.yaml';
    }
}
