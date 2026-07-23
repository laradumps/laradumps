<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Support\Facades\Log;
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
use LaraDumps\LaraDumps\Profile\OpenTelemetry\ProfileTracer;
use LaraDumps\LaraDumps\Profile\ProfileManager;
use LaraDumps\LaraDumpsCore\Actions\Config;

class ProfileObserver extends BaseObserver
{
    private ProfileManager $manager;

    private bool $collectorsRegistered = false;

    private ?ControllerCollector $controllerCollector = null;

    private ?XHProfCollector $xhprofCollector = null;

    private static bool $missingOtelWarned = false;

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

        if (ProfileTracer::isAvailable()) {
            $this->manager->setTracer(new ProfileTracer($this->manager));
        } else {
            $this->warnMissingOpenTelemetry();
        }

        if ($this->xhprofCollector) {
            $this->xhprofCollector->start();
        }
    }

    private function warnMissingOpenTelemetry(): void
    {
        if (self::$missingOtelWarned) {
            return;
        }

        self::$missingOtelWarned = true;

        Log::error(
            'LaraDumps: profiling was requested but the OpenTelemetry packages are not installed. '
            .'The profiler is disabled. Install them with: '
            .'composer require open-telemetry/sdk open-telemetry/api'
        );
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
        return boolval(Config::get("profile.capture.{$type}", true));
    }

    private function shouldEnableXHProf(): bool
    {
        return extension_loaded('xhprof') && function_exists('xhprof_enable');
    }
}
