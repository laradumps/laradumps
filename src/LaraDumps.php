<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\{Collection, Str};
use LaraDumps\LaraDumps\Actions\SendPayload;
use LaraDumps\LaraDumps\Concerns\Colors;
use LaraDumps\LaraDumps\Observers\{HttpClientObserver, JobsObserver, QueryObserver};
use LaraDumps\LaraDumps\Payloads\{ClearPayload,
    CoffeePayload,
    ColorPayload,
    DiffPayload,
    DumpPayload,
    LabelPayload,
    MailablePayload,
    MarkdownPayload,
    ModelPayload,
    Payload,
    PhpInfoPayload,
    RoutesPayload,
    ScreenPayload,
    TablePayload,
    TimeTrackPayload,
    ValidateStringPayload
};

class LaraDumps
{
    use Colors;

    public function __construct(
        public string  $notificationId = '',
        private string $fullUrl = '',
        private array  $trace = [],
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
            $payload->trace($this->trace);
            $payload->notificationId($this->notificationId);
            $payload = $payload->toArray();

            SendPayload::handle($this->fullUrl, $payload);
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
     * @param int $raiseIn Delay in seconds for the app to raise and focus
     */
    public function toScreen(
        string $screenName,
        bool   $classAttr = false,
        int    $raiseIn = 0
    ): LaraDumps {
        $payload = new ScreenPayload($screenName, $classAttr, $raiseIn);
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
     * Grab a coffee!
     *
     */
    public function coffee(): LaraDumps
    {
        $this->send(new CoffeePayload());

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
     * Checks if content contains string.
     *
     * @param string $content
     * @param boolean $caseSensitive Search is case-sensitive
     * @param boolean $wholeWord Search for the whole words
     * @return LaraDumps
     */
    public function contains(string $content, bool $caseSensitive = false, bool $wholeWord = false): LaraDumps
    {
        $payload = new ValidateStringPayload('contains');
        $payload->setContent($content)
            ->setCaseSensitive($caseSensitive)
            ->setWholeWord($wholeWord);

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

    public function write(mixed $args = null, ?bool $autoInvokeApp = null): LaraDumps
    {
        $originalContent = $args;
        $args            = Support\Dumper::dump($args);
        if (!empty($args)) {
            $payload = new DumpPayload($args, $originalContent);
            $payload->autoInvokeApp($autoInvokeApp);
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
                $payload = new ModelPayload($model);
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
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(QueryObserver::class)->setTrace($trace);
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
     * @param mixed $argument
     * @param boolean $splitDiff Outputs comparison result in 2 rows (original/diff).
     * @return LaraDumps
     */
    public function diff(mixed $argument, bool $splitDiff = false): LaraDumps
    {
        $argument = is_array($argument) ? json_encode($argument) : $argument;

        $payload = new DiffPayload($argument, $splitDiff);
        $this->send($payload);

        return $this;
    }

    /**
     * Starts clocking a code block execution time
     *
     * @param string $reference Unique name for this time clocking
     */
    public function time(string $reference): void
    {
        $payload = new TimeTrackPayload($reference);
        $this->send($payload);
    }

    /**
     * Stops clocking a code block execution time
     *
     * @param string $reference Unique name called on ds()->time()
     */
    public function stopTime(string $reference): void
    {
        $payload = new TimeTrackPayload($reference);
        $this->send($payload);
        $this->label($reference);
    }

    /**
     * Send rendered mailable
     *
     */
    public function mailable(Mailable $mailable): self
    {
        $mailablePayload = new MailablePayload($mailable);
        $this->send($mailablePayload);

        return $this;
    }

    /**
     * Display all HTTP Client requests that are executed with custom label
     */
    public function httpClientOn(string $label = null): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(HttpClientObserver::class)->setTrace($trace);
        app(HttpClientObserver::class)->enable($label);

        return $this;
    }

    /**
     * Stop displaying HTTP Client requests
     */
    public function httpClientOff(): void
    {
        app(HttpClientObserver::class)->disable();
    }

    /*
     * Sends rendered markdown
     */
    public function markdown(string $markdown): self
    {
        $payload = new MarkdownPayload($markdown);
        $this->send($payload);

        return $this;
    }

    /**
     * Dump all Jobs that are dispatched with custom label
     */
    public function showJobs(string $label = null): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(JobsObserver::class)->setTrace($trace);
        app(JobsObserver::class)->enable($label);

        return $this;
    }

    /**
     * Stop dumping Jobs
     */
    public function stopShowingJobs(): void
    {
        app(JobsObserver::class)->disable();
    }
}
