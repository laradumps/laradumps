<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\{Collection, ServiceProvider, Stringable};
use Illuminate\Support\Facades\Blade;
use Illuminate\Testing\TestResponse;
use Illuminate\View\View;
use LaraDumps\LaraDumps\Actions\DefaultConfig;
use LaraDumps\LaraDumps\Commands\InitCommand;
use LaraDumps\LaraDumps\Middleware\ProfileMiddleware;
use LaraDumps\LaraDumps\Observers\{BrainObserver,
    CacheObserver,
    CommandObserver,
    DumpObserver,
    GateObserver,
    HttpClientObserver,
    JobsObserver,
    LogObserver,
    MailObserver,
    ProfileObserver,
    QueryObserver,
    ScheduledCommandObserver,
    SlowQueryObserver};
use LaraDumps\LaraDumps\Payloads\QueryPayload;
use LaraDumps\LaraDumps\Profile\ProfileManager;
use LaraDumps\LaraDumpsCore\Actions\{Config, Dumper};
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, TableV2Payload};

class LaraDumpsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! defined('LARADUMPS_REQUEST_ID')) {
            define('LARADUMPS_REQUEST_ID', uniqid());
        }

        $this->publishes([
            __DIR__.'/../resources/config/laradumps.php' => config_path('laradumps.php'),
        ], 'laradumps-config');

        $this->createDirectives();

        $this->bootObservers();

        $this->commands([InitCommand::class]);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laradumps');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../resources/config/laradumps.php',
            'laradumps'
        );

        Config::registerDefaults(DefaultConfig::packageDefaults());

        $file = str_replace('/', DIRECTORY_SEPARATOR, __DIR__.'/functions.php');

        $file = __DIR__.DIRECTORY_SEPARATOR.'functions.php';

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
        $this->app->singleton(BrainObserver::class);
        $this->app->singleton(ProfileObserver::class);
        $this->app->singleton(ProfileManager::class, function () {
            return app(ProfileObserver::class)->getManager();
        });

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
        app(BrainObserver::class)->register();
        app(ProfileObserver::class)->register();

        $this->registerProfileMiddleware();
    }

    private function registerProfileMiddleware(): void
    {
        if (! boolval(Config::get('observers.profiler', false))) {
            return;
        }

        if (! boolval(Config::get('profiler.auto_middleware', false))) {
            return;
        }

        if ($this->app->runningInConsole()) {
            return;
        }

        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(ProfileMiddleware::class);
    }

    private function registerMacros(): void
    {
        Collection::macro('ds', function (string $label = '') {
            $laradumps = new LaraDumps();
            $laradumps->write($this->all());

            if ($label) {
                $laradumps->label($label);
            }

            return $this;
        });

        Stringable::macro('ds', function (string $label = '') {
            $laradumps = new LaraDumps();
            $laradumps->write((string) $this);

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

            return $this;
        });

        TestResponse::macro('ds', function () {
            $data = $this->original instanceof View
                ? $this->original->getData()
                : $this->original;

            $payload = new TableV2Payload([
                'Status' => $this->getStatusCode(),
                'Headers' => Dumper::dump($this->headers->all())[0],
                'Data' => Dumper::dump($data)[0],
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

                [$pre, $id] = Dumper::dump($this->value);

                $payload = new DumpPayload($pre, $this->value, variableType: gettype($this->value));
                $payload->setDumpId($id);

                $payload->setFrame($frame);
                $laradumps->send($payload, withFrame: false);
                $laradumps->label('Pest Expectation');
            });
        }
    }
}
