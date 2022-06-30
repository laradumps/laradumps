<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\{ServiceProvider, Str};
use LaraDumps\LaraDumps\Commands\CheckCommand;
use LaraDumps\LaraDumps\Observers\{LivewireObserver, LogObserver, QueryObserver};
use LaraDumps\LaraDumps\Payloads\QueryPayload;

class LaraDumpsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadConfigs();
        $this->createDirectives();

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

        app(LogObserver::class)->register();
        app(QueryObserver::class)->register();
        app(LivewireObserver::class)->register();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laradumps');

        if ($this->app->runningInConsole()) {
            $this->commands([CheckCommand::class]);
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
            $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $backtrace = collect($backtrace)
                ->filter(function ($trace) {
                    /** @var string $file */
                    $file = $trace['file'];

                    return !str_contains($file, 'vendor');
                });

            $ds = new LaraDumps(backtrace: (array) $backtrace->first());
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
    }

    private function createDirectives(): void
    {
        Blade::directive('ds', function ($args) {
            return "<?php dsBlade($args); ?>";
        });
    }
}
