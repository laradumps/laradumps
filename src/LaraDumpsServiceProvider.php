<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\{ServiceProvider, Str};
use LaraDumps\LaraDumps\Commands\{CheckCommand, InitCommand};
use LaraDumps\LaraDumps\Observers\{LivewireComponentsObserver,
    LivewireDispatchObserver,
    LivewireEventsObserver,
    LivewireFailedValidationObserver,
    LogObserver,
    QueryObserver};
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

        $file = __DIR__ . './functions.php';
        if (file_exists($file)) {
            require_once($file);
        }

        $this->app->singleton(QueryObserver::class);

        Builder::macro('ds', function () {
            $trace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $trace = collect($trace)
                ->filter(function ($trace) {
                    /** @var string $file */
                    $file = $trace['file'];

                    return !str_contains($file, 'vendor');
                });

            $ds = new LaraDumps(trace: (array) $trace->first());
            /** @phpstan-ignore-next-line  */
            $ds->send(new QueryPayload($this));

            return $this;
        });
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
        app(LogObserver::class)->register();
        app(QueryObserver::class)->register();
        app(LivewireEventsObserver::class)->register();
        app(LivewireDispatchObserver::class)->register();
        app(LivewireComponentsObserver::class)->register();
        app(LivewireFailedValidationObserver::class)->register();
    }
}
