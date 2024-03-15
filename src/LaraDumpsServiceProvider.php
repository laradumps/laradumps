<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\{Collection, ServiceProvider, Stringable};
use LaraDumps\LaraDumps\Commands\InitCommand;
use LaraDumps\LaraDumps\Observers\LogObserver;
use LaraDumps\LaraDumps\Observers\{CacheObserver,
    CommandObserver,
    DumpObserver,
    GateObserver,
    HttpClientObserver,
    JobsObserver,
    MailObserver,
    QueryObserver,
    ScheduledCommandObserver};
use LaraDumps\LaraDumps\Payloads\QueryPayload;
use LaraDumps\LaraDumpsCore\Actions\Config;

class LaraDumpsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!defined('LARADUMPS_REQUEST_ID')) {
            define('LARADUMPS_REQUEST_ID', uniqid());
        }

        $this->createDirectives();

        $this->bootObservers();

        $this->commands([InitCommand::class]);

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
        $this->app->singleton(DumpObserver::class);

        $this->registerMacros();
    }

    private function createDirectives(): void
    {
        Blade::directive('ds', function ($args) {
            return "<?php dsBlade($args); ?>";
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
        app(DumpObserver::class)->register();
    }

    private function registerMacros(): void
    {
        Collection::macro('ds', function (string $label = '') {
            $laradumps = app(LaraDumps::class);

            /** @phpstan-ignore-next-line  */
            $laradumps->write($this->items);

            if ($label) {
                $laradumps->label($label);
            }

            return $this;
        });

        Stringable::macro('ds', function (string $label = '') {
            $laradumps = app(LaraDumps::class);
            /** @phpstan-ignore-next-line  */
            $laradumps->write($this->value);

            if ($label) {
                $laradumps->label($label);
            }

            return $this;
        });

        Builder::macro('ds', function () {
            $laradumps = app(LaraDumps::class);

            /** @phpstan-ignore-next-line */
            $payload = new QueryPayload($this);
            $payload->setDumpId(uniqid());

            $laradumps->send($payload);

            return $this;
        });
    }
}
