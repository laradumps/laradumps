<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\{Collection, ServiceProvider, Stringable};
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\Observers\LogObserver;
use LaraDumps\LaraDumps\Observers\{CacheObserver,
    CommandObserver,
    GateObserver,
    HttpClientObserver,
    JobsObserver,
    MailObserver,
    NotificationObserver,
    QueryObserver,
    ScheduledCommandObserver};
use LaraDumps\LaraDumps\Payloads\QueryPayload;

class LaraDumpsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!defined('LARADUMPS_REQUEST_ID')) {
            define('LARADUMPS_REQUEST_ID', uniqid());
        }

        $this->loadConfigs();
        $this->createDirectives();

        $this->bootObservers();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laradumps');
    }

    public function register(): void
    {
        $file = str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/functions.php');

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
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    private function createDirectives(): void
    {
        Blade::directive('ds', function ($args) {
            return "<?php dsBlade($args); ?>";
        });

        Blade::directive('dsAutoClearOnPageReload', function ($args) {
            if (boolval(Config::get('auto_clear_on_page_reload')) === false) {
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
        app(MailObserver::class)->register();
    }

    private function registerMacros(): void
    {
        Collection::macro('ds', function (string $label = '') {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $trace = collect($trace)
                ->filter(function ($trace) {
                    /** @var array $trace */
                    /** @var string $file */
                    $file = $trace['file'] ?? '';

                    return !str_contains($file, 'vendor');
                });

            $ds = new LaraDumps(trace: (array) $trace->first());
            /** @phpstan-ignore-next-line  */
            $ds->write($this->items);

            if ($label) {
                $ds->label($label);
            }

            return $this;
        });

        Stringable::macro('ds', function (string $label = '') {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $trace = collect($trace)
                ->filter(function ($trace) {
                    /** @var array $trace */
                    /** @var string $file */
                    $file = $trace['file'] ?? '';

                    return !str_contains($file, 'vendor');
                });

            $ds = new LaraDumps(trace: (array) $trace->first());
            /** @phpstan-ignore-next-line  */
            $ds->write($this->value);

            if ($label) {
                $ds->label($label);
            }

            return $this;
        });

        Builder::macro('ds', function () {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $trace = collect($trace)
                ->filter(function ($trace) {
                    /** @var array $trace */
                    /** @var string $file */
                    $file = $trace['file'] ?? '';

                    return !str_contains($file, 'vendor');
                });

            $ds = new LaraDumps(trace: (array) $trace->first());

            /** @phpstan-ignore-next-line */
            $payload = new QueryPayload($this);
            $payload->setDumpId(uniqid());

            $ds->send($payload);

            return $this;
        });
    }
}
