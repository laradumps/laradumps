<?php

namespace LaraDumps\LaraDumps\Observers;

use LaraDumps\LaraDumps\Profile\Collectors\{
    AppCollector,
    CacheCollector,
    ControllerCollector,
    EloquentCollector,
    EventCollector,
    HttpCollector,
    JobCollector,
    QueryCollector,
    ViewCollector,
    XHProfCollector
};
use LaraDumps\LaraDumps\Profile\ProfileManager;
use LaraDumps\LaraDumpsCore\Actions\Config;

class ProfileObserver extends BaseObserver
{
    private ProfileManager $manager;

    private bool $collectorsRegistered = false;

    private ?ControllerCollector $controllerCollector = null;

    private ?XHProfCollector $xhprofCollector = null;

    public function __construct()
    {
        $this->manager = new ProfileManager();
    }

    public function register(): void
    {
        $this->registerCollectors();
    }

    public function getManager(): ProfileManager
    {
        return $this->manager;
    }

    public function start(?string $label = null): void
    {
        if (! $this->collectorsRegistered) {
            $this->registerCollectors();
        }

        $this->manager->start($label);

        if ($this->xhprofCollector) {
            $this->xhprofCollector->start();
        }
    }

    public function stop(): array
    {
        if (! $this->manager->isActive()) {
            return [];
        }

        $this->xhprofCollector?->stop();

        $this->controllerCollector?->stopController();

        return $this->manager->stop();
    }

    public function isEnabled(string $key): bool
    {
        return boolval(Config::get('observers.'.$key, false)) || $this->enabled;
    }

    public function isActive(): bool
    {
        return $this->manager->isActive();
    }

    private function registerCollectors(): void
    {
        if ($this->collectorsRegistered) {
            return;
        }

        $collectors = [
            'queries' => QueryCollector::class,
            'events' => EventCollector::class,
            'app' => AppCollector::class,
            'eloquent' => EloquentCollector::class,
            'views' => ViewCollector::class,
            'controller' => ControllerCollector::class,
            'http' => HttpCollector::class,
            'cache' => CacheCollector::class,
            'jobs' => JobCollector::class,
        ];

        foreach ($collectors as $type => $collectorClass) {
            if ($this->shouldRegisterCollector($type)) {
                $collector = new $collectorClass($this->manager);
                $collector->register();

                if ($type === 'controller') {
                    $this->controllerCollector = $collector;
                }
            }
        }

        if ($this->shouldEnableXHProf()) {
            $this->xhprofCollector = new XHProfCollector($this->manager);
            $this->xhprofCollector->register();
        }

        $this->collectorsRegistered = true;
    }

    private function shouldRegisterCollector(string $type): bool
    {
        return boolval(Config::get("profile.capture.{$type}", true));
    }

    private function shouldEnableXHProf(): bool
    {
        return extension_loaded('xhprof') && function_exists('xhprof_enable');
    }
}
