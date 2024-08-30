<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\{Collection, ServiceProvider, Stringable};
use Illuminate\Testing\TestResponse;
use Illuminate\View\View;
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
    ScheduledCommandObserver,
    SlowQueryObserver};
use LaraDumps\LaraDumps\Payloads\QueryPayload;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, TableV2Payload};

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
            require_once $file;
        }

        $this->app->singleton(JobsObserver::class);
        $this->app->singleton(CommandObserver::class);
        $this->app->singleton(ScheduledCommandObserver::class);
        $this->app->singleton(CacheObserver::class);
        $this->app->singleton(GateObserver::class);
        $this->app->singleton(QueryObserver::class);
        $this->app->singleton(HttpClientObserver::class);
        $this->app->singleton(DumpObserver::class);
        $this->app->singleton(SlowQueryObserver::class);

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
        app(SlowQueryObserver::class)->register();
    }

    private function registerMacros(): void
    {
        Collection::macro('ds', function (string $label = '') {
            $laradumps = new LaraDumps();
            $laradumps->write($this->items); // @phpstan-ignore-line

            if ($label) {
                $laradumps->label($label);
            }

            return $this;
        });

        Stringable::macro('ds', function (string $label = '') {
            $laradumps = new LaraDumps();
            $laradumps->write($this->value); // @phpstan-ignore-line

            if ($label) {
                $laradumps->label($label);
            }

            return $this;
        });

        Builder::macro('ds', function () {
            $payload = new QueryPayload($this);
            $payload->setDumpId(uniqid());

            $laradumps = new LaraDumps();
            $laradumps->send($payload);

            $laradumps->label('Queries Macro');

            return $this;
        });

        TestResponse::macro('ds', function () {
            $data = $this->original instanceof View
                ? $this->original->getData()
                : $this->original;

            $payload = new TableV2Payload([
                'Status'    => $this->getStatusCode(),
                'Headers'   => Dumper::dump($this->headers->all())[0],
                'Data'      => Dumper::dump($data)[0],
                'Exception' => Dumper::dump($this->exceptions->all())[0],
            ]);

            $laradumps = new LaraDumps();
            $laradumps->send($payload);
            $laradumps->label('Test Response');

            return $this;
        });

        if (runningInTest() && function_exists('expect')) {
            expect()->extend('ds', function () {
                $frame = array_values(
                    array_filter(debug_backtrace(), function (array $frame) {
                        return $frame['function'] === '__call' && $frame['class'] === 'Pest\Expectation'; // @phpstan-ignore-line
                    })
                )[0];

                $frame = [
                    'file' => data_get($frame, 'file'),
                    'line' => data_get($frame, 'line'),
                ];

                $laradumps = new LaraDumps();

                [$pre, $id] = Dumper::dump($this->value); // @phpstan-ignore-line

                $payload = new DumpPayload($pre, $this->value, variableType: gettype($this->value)); // @phpstan-ignore-line
                $payload->setDumpId($id);

                $payload->setFrame($frame);
                $laradumps->send($payload, withFrame: false);
                $laradumps->label('Pest Expectation');
            });
        }
    }
}
