<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\{Collection, Str};
use LaraDumps\LaraDumps\Concerns\Colors;
use LaraDumps\LaraDumps\Observers\QueryObserver;
use LaraDumps\LaraDumps\Payloads\{ClearPayload,
    ColorPayload,
    DiffPayload,
    DumpPayload,
    LabelPayload,
    ModelPayload,
    Payload,
    PhpInfoPayload,
    RoutesPayload,
    ScreenPayload,
    TablePayload,
    ValidateStringPayload};

class LaraDumps
{
    use Colors;

    public function __construct(
        public string  $notificationId = '',
        private string $fullUrl = '',
        private array $backtrack = [],
    ) {
        if (config('laradumps.sleep')) {
            $sleep = intval(config('laradumps.sleep'));
            sleep($sleep);
        }

        $this->fullUrl        = config('laradumps.host') . ':' . config('laradumps.port') . '/api/dumps';
        $this->notificationId = filled($notificationId) ? $this->notificationId : Str::uuid()->toString();
    }

    public function send(array|Payload $payload): array|Payload
    {
        if ($payload instanceof Payload) {
            $payload->trace($this->backtrack);
            $payload->notificationId($this->notificationId);
            $payload = $payload->toArray();

            try {
                Http::post($this->fullUrl, $payload);
            } catch (\Throwable) {
            }
        }

        return $payload;
    }

    /**
     * Send custom color
     *
     */
    public function color(string $color): LaraDumps
    {
        $payload = new ColorPayload($color);
        $this->send($payload);

        return $this;
    }

    /**
     * Add new screen
     *
     */
    public function s(string $screen, bool $classAttr = false): LaraDumps
    {
        return $this->toScreen($screen, $classAttr);
    }

    /**
     * Add new screen
     *
     */
    public function toScreen(string $screen, bool $classAttr = false): LaraDumps
    {
        $payload = new ScreenPayload($screen, $classAttr);
        $this->send($payload);

        return $this;
    }

    /**
     * Send custom label
     *
     */
    public function label(string $label): LaraDumps
    {
        $payload = new LabelPayload($label);
        $this->send($payload);

        return $this;
    }

    /**
     * Send dump and die
     */
    public function die(string $status = ''): void
    {
        die($status);
    }

    /**
     * Clear screen
     *
     */
    public function clear(): LaraDumps
    {
        $this->send(new ClearPayload());

        return $this;
    }

    /**
     * Send JSON data and validate
     *
     */
    public function isJson(): LaraDumps
    {
        $payload = new ValidateStringPayload('json');

        $this->send($payload);

        return $this;
    }

    /**
     * Search if content contains string
     *
     */
    public function contains(string $content): LaraDumps
    {
        $payload = new ValidateStringPayload('contains');
        $payload->setContent($content);

        $this->send($payload);

        return $this;
    }

    /**
     * Send PHPInfo
     *
     */
    public function phpinfo(): LaraDumps
    {
        $this->send(new PhpInfoPayload());

        return $this;
    }

    /**
     * Send Routes
     *
     */
    public function routes(mixed ...$except): LaraDumps
    {
        $this->send(new RoutesPayload($except));

        return $this;
    }

    /**
     * Send Table
     *
     */
    public function table(Collection|array $data = [], string $name = ''): LaraDumps
    {
        $this->send(new TablePayload($data, $name));

        return $this;
    }

    public function write(mixed $args = null): LaraDumps
    {
        $originalContent = $args;
        $args            = Support\Dumper::dump($args);
        if (!empty($args)) {
            $payload = new DumpPayload($args, $originalContent);
            $this->send($payload);
        }

        return $this;
    }

    /**
     * Shows model attributes and relationship
     *
     */
    public function model(Model ...$models): LaraDumps
    {
        foreach ($models as $model) {
            if ($model instanceof Model) {
                $payload    = new ModelPayload($model);
                $this->send($payload);
            }
        }

        return $this;
    }

    /**
     * Display all queries that are executed with custom label
     *
     */
    public function queriesOn(string $label = null): void
    {
        $backtrace   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(QueryObserver::class)->setTrace($backtrace);
        app(QueryObserver::class)->enable($label);
    }

    /**
     * Stop displaying queries
     *
     */
    public function queriesOff(): void
    {
        app(QueryObserver::class)->disable();
    }

    /**
     * Check the difference between two texts
     *
     */
    public function diff(mixed $first, mixed $second, bool $col = false): LaraDumps
    {
        $first  = is_array($first) ? json_encode($first) : $first;
        $second = is_array($second) ? json_encode($second) : $second;

        $payload = new DiffPayload($first, $second, $col);
        $this->send($payload);

        return $this;
    }
}
