<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\{Collection, ServiceProvider, Str, Stringable};
use LaraDumps\LaraDumps\Commands\{CheckCommand, InitCommand};
use LaraDumps\LaraDumps\Observers\{CacheObserver,
    CommandObserver,
    GateObserver,
    HttpClientObserver,
    JobsObserver,
    LivewireComponentsObserver,
    LivewireDispatchObserver,
    LivewireEventsObserver,
    LivewireFailedValidationObserver,
    LogObserver,
    QueryObserver,
    ScheduledCommandObserver};
use LaraDumps\LaraDumps\Payloads\QueryPayload;

class LaraDumpsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadConfigs();
        $this->createDirectives();

        $this->bootMacros();
        $this->bootObservers();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laradumps');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InitCommand::class,
                CheckCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laradumps.php',
            'laradumps'
        );

        $file = __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
        if (file_exists($file)) {
            require_once($file);
        }

        $this->app->singleton(JobsObserver::class);
        $this->app->singleton(CommandObserver::class);
        $this->app->singleton(ScheduledCommandObserver::class);
        $this->app->singleton(CacheObserver::class);
        $this->app->singleton(GateObserver::class);
        $this->app->singleton(QueryObserver::class);
        $this->app->singleton(HttpClientObserver::class);

        $this->registerMacros();
    }

    private function loadConfigs(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laradumps.php' => config_path('laradumps.php'),
        ], 'laradumps-config');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    private function createDirectives(): void
    {
        Blade::directive('ds', function ($args) {
            return "<?php dsBlade($args); ?>";
        });

        Blade::directive('dsAutoClearOnPageReload', function ($args) {
            if (boolval(config('laradumps.auto_clear_on_page_reload'))   === false
                && boolval(config('laradumps.send_livewire_components')) === false) {
                return '';
            }

            $csrf = csrf_token();

            return <<<HTML
<script>
document.addEventListener('DOMContentLoaded', () => {
    window.onbeforeunload = () => {
       const xmlhttp = new XMLHttpRequest();
       xmlhttp.open("POST", "/__ds__/clear");
       xmlhttp.setRequestHeader('X-CSRF-TOKEN', '{$csrf}');
       xmlhttp.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
       xmlhttp.send(JSON.stringify({ "ds": true }));
    }
}, false);
</script>
HTML;
        });
    }

    private function bootMacros(): void
    {
        Str::macro('cut', function (string $str, string $start, string $end) {
            /** @phpstan-ignore-next-line */
            $arr = explode($start, $str);
            if (isset($arr[1])) {
                /** @phpstan-ignore-next-line */
                $arr = explode($end, $arr[1]);

                return '<pre ' . $arr[0] . '</pre>';
            }

            return '';
        });
    }

    private function bootObservers(): void
    {
        app(JobsObserver::class)->register();
        app(CommandObserver::class)->register();
        app(ScheduledCommandObserver::class)->register();
        app(CacheObserver::class)->register();
        app(GateObserver::class)->register();
        app(HttpClientObserver::class)->register();
        app(LogObserver::class)->register();
        app(QueryObserver::class)->register();
        app(LivewireEventsObserver::class)->register();
        app(LivewireDispatchObserver::class)->register();
        app(LivewireComponentsObserver::class)->register();
        app(LivewireFailedValidationObserver::class)->register();
    }

    private function registerMacros(): void
    {
        Collection::macro('ds', function (string $label = '') {
            /* @var Collection $this */
            $label === ''
                // @phpstan-ignore-next-line
                ? ds($this->items)
                // @phpstan-ignore-next-line
                : ds($this->items)->label($label);

            return $this;
        });

        Stringable::macro('ds', function (string $label = '') {
            /* @var Stringable $this */
            $label === ''
                // @phpstan-ignore-next-line
                ? ds($this->value)
                // @phpstan-ignore-next-line
                : ds($this->value)->label($label);

            return $this;
        });

        Builder::macro('ds', function () {
            $trace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $trace = collect($trace)
                ->filter(function ($trace) {
                    /** @var array $trace */
                    /** @var string $file */
                    $file = $trace['file'] ?? '';

                    return !str_contains($file, 'vendor');
                });

            $ds = new LaraDumps(trace: (array) $trace->first());
            /** @phpstan-ignore-next-line  */
            $ds->send(new QueryPayload($this));

            return $this;
        });
    }
}
