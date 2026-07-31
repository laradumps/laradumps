<?php

use LaraDumps\LaraDumps\Actions\DefaultConfig;
use LaraDumps\LaraDumpsCore\Actions\Config;
use Symfony\Component\Yaml\Yaml;

function withTempLaradumpsConfig(array $content, callable $fn): void
{
    $dir = sys_get_temp_dir().'/ld_selfheal_'.uniqid();
    mkdir($dir);
    $file = $dir.'/laradumps.yaml';
    file_put_contents($file, Yaml::dump($content, 4, 2));

    $ref = new ReflectionClass(Config::class);
    $path = $ref->getProperty('configFilePath');
    $cache = $ref->getProperty('cachedContent');
    $path->setAccessible(true);
    $cache->setAccessible(true);

    $origPath = $path->isInitialized() ? $path->getValue() : null;
    $origCache = $cache->getValue();

    $path->setValue(null, $file);
    $cache->setValue(null, null);

    try {
        $fn($file);
    } finally {
        if ($origPath !== null) {
            $path->setValue(null, $origPath);
        }
        $cache->setValue(null, $origCache);
        @unlink($file);
        @rmdir($dir);
    }
}

it('backfills missing keys into a stale config using the real schema', function () {
    $stale = [
        'app' => ['project_path' => '/my/project/'],
        'observers' => ['dump' => true],
    ];

    withTempLaradumpsConfig($stale, function ($file) {
        $changed = Config::sync(DefaultConfig::toArray());

        expect($changed)->toBeTrue();

        $written = Yaml::parseFile($file);

        expect($written)->toHaveKeys(['logs', 'profile', 'queries', 'code_snippet', 'xdebug', 'slow_queries']);
        expect($written['config'])->toHaveKey('color_in_screen');
        expect($written['profile']['capture'])->toHaveKey('eloquent');

        expect($written['app']['project_path'])->toBe('/my/project/');
        expect($written['observers']['dump'])->toBeTrue();
    });
});

it('prunes keys that are no longer part of the schema', function () {
    $stale = array_replace_recursive(DefaultConfig::toArray(), [
        'app' => ['project_path' => '/my/project/'],
        'observers' => ['obsolete_observer' => true],
        'removed_section' => ['foo' => 'bar'],
    ]);

    withTempLaradumpsConfig($stale, function ($file) {
        Config::sync(DefaultConfig::toArray());

        $written = Yaml::parseFile($file);

        expect($written['observers'])->not->toHaveKey('obsolete_observer');
        expect($written)->not->toHaveKey('removed_section');
        expect($written['app']['project_path'])->toBe('/my/project/');
    });
});

it('is a no-op when the file already matches the schema', function () {
    $current = DefaultConfig::toArray();
    $current['app']['project_path'] = '/my/project/';

    withTempLaradumpsConfig($current, function () {
        expect(Config::sync(DefaultConfig::toArray()))->toBeFalse();
    });
});
