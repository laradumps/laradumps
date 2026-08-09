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
use LaraDumps\LaraDumps\Profile\Tracing\ProfileTracer;
use LaraDumps\LaraDumpsCore\Actions\Config;

class ProfileObserver extends BaseObserver
{
    private ProfileManager $manager;

    private bool $collectorsRegistered = false;

    private ?ControllerCollector $controllerCollector = null;

    private ?XHProfCollector $xhprofCollector = null;

    private bool $finalized = false;

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

        $this->manager->setTracer(new ProfileTracer($this->manager));

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

        $this->manager->tracer()?->shutdown();
        $this->manager->setTracer(null);

        return $this->manager->stop();
    }

    /**
     * Close collectors + measurement in-request, but defer building the payload
     * (getProfileData) to buildData(), which the terminable phase calls after
     * the response is sent.
     */
    public function finalize(): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        $this->xhprofCollector?->stop();

        $this->controllerCollector?->stopController();

        $this->manager->tracer()?->shutdown();
        $this->manager->setTracer(null);

        $this->manager->finalize();

        $this->finalized = true;
    }

    public function buildData(): array
    {
        if (! $this->finalized) {
            return [];
        }

        $this->finalized = false;

        return $this->manager->getProfileData();
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

                if ($type === 'controller' && $collector instanceof ControllerCollector) {
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
        return boolval(Config::get("profiler.capture.{$type}", true));
    }

    private function shouldEnableXHProf(): bool
    {
        if (! boolval(Config::get('profiler.xhprof', false))) {
            return false;
        }

        if (! boolval(Config::get('profiler.capture.method', true))) {
            return false;
        }

        return extension_loaded('xhprof') && function_exists('xhprof_enable');
    }
}
