<?php

use Illuminate\Queue\Events\{JobFailed, JobProcessed, JobProcessing, JobQueued};
use Illuminate\Queue\{Jobs\SyncJob};
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Observers\JobsObserver;

use LaraDumps\LaraDumpsCore\Actions\Config;

use function PHPUnit\Framework\assertStringContainsString;

beforeEach(function () {
    putenv('DS_RUNNING_IN_TESTS=true');
});

it('should register the event listeners', function () {
    $observer = new JobsObserver();

    $observer->register();

    $jobQueued = Event::getListeners(JobQueued::class);

    expect($jobQueued)->not()->toBeEmpty();

    $jobProcessing = Event::getListeners(JobProcessing::class);
    expect($jobProcessing)->not()->toBeEmpty();

    $jobProcessed = Event::getListeners(JobProcessed::class);
    expect($jobProcessed)->not()->toBeEmpty();

    $jobFailed = Event::getListeners(JobFailed::class);
    expect($jobFailed)->not()->toBeEmpty();
});

it('enables and disables observer', function () {
    $observer = new JobsObserver();

    $observer->enable();
    expect($observer->isEnabled())->toBeTrue();

    $observer->disable();
    expect($observer->isEnabled())->toBeFalse();
});

it('returns false when disabled by config', function () {
    Config::set('observers.jobs', false);

    $observer = new JobsObserver();

    expect($observer->isEnabled())->toBeFalse();
});

it('returns true when enabled by config', function () {
    Config::set('observers.jobs', false);

    $observer = new JobsObserver();

    expect($observer->isEnabled())->toBeFalse();
});

it('generate job payload with JobProcessing class', function () {
    $payload    = '{"uuid":"7a8c2724-787f-450d-8952-a094a379cb28","displayName":"App\\Jobs\\ApproveJob","job":"Illuminate\\Queue\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"App\\Jobs\\ApproveJob","command":"O:19:\"App\\Jobs\\ApproveJob\":1:{s:6:\"userId\";i:1725013240;}"}}';
    $job        = new SyncJob(app(), $payload, 'sync', 'default');
    $processing = new JobProcessing('sync', $job);

    $generatePayload = app(JobsObserver::class)->generatePayload($processing);
    $generatePayload->setNotificationId(1234);

    $payload = (object) $generatePayload->toArray();

    $sfDumpId = $payload->sf_dump_id;

    $expected = <<<EOTTXT
<span class=sf-dump-protected title="Protected property">payload</span>: "<span class=sf-dump-str title="357 characters">{&quot;uuid&quot;:&quot;7a8c2724-787f-450d-8952-a094a379cb28&quot;,&quot;displayName&quot;:&quot;App\Jobs\ApproveJob&quot;,&quot;job&quot;:&quot;Illuminate\Queue\CallQueuedHandler@call&quot;,&quot;maxTries&quot;:null,&quot;maxExceptions&quot;:null,&quot;failOnTimeout&quot;:false,&quot;backoff&quot;:null,&quot;timeout&quot;:null,&quot;retryUntil&quot;:null,&quot;data&quot;:{&quot;commandName&quot;:&quot;App\Jobs\ApproveJob&quot;,&quot;command&quot;:&quot;O:19:\&quot;App\Jobs\ApproveJob\&quot;:1:{s:6:\&quot;userId\&quot;;i:1725013240;}&quot;}}</span>"\n
EOTTXT;

    expect($payload->id)->toBe('1234')
        ->and($payload->type)->toBe('dump')
        ->and($payload->dump['dump'])->toContain($sfDumpId);

    assertStringContainsString($expected, $payload->dump['dump']);
});

it('generate job payload with JobFailed class', function () {
    $payload = '{"uuid":"11a9299e-7593-4a55-8009-8fd511ea946a","displayName":"App\\Jobs\\ApproveJob","job":"Illuminate\\Queue\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"App\\Jobs\\ApproveJob","command":"O:19:\"App\\Jobs\\ApproveJob\":1:{s:6:\"userId\";i:35649791;}"}}';
    $job     = new SyncJob(app(), $payload, 'sync', 'default');
    $job->markAsFailed();

    $processing = new JobFailed('sync', $job, new Exception('Failed!'));

    $generatePayload = app(JobsObserver::class)->generatePayload($processing);
    $generatePayload->setNotificationId(1234);

    $payload = (object) $generatePayload->toArray();

    $sfDumpId = $payload->sf_dump_id;

    $expected = <<<EOTTXT
<span class=sf-dump-protected title="Protected property">payload</span>: "<span class=sf-dump-str title="355 characters">{&quot;uuid&quot;:&quot;11a9299e-7593-4a55-8009-8fd511ea946a&quot;,&quot;displayName&quot;:&quot;App\Jobs\ApproveJob&quot;,&quot;job&quot;:&quot;Illuminate\Queue\CallQueuedHandler@call&quot;,&quot;maxTries&quot;:null,&quot;maxExceptions&quot;:null,&quot;failOnTimeout&quot;:false,&quot;backoff&quot;:null,&quot;timeout&quot;:null,&quot;retryUntil&quot;:null,&quot;data&quot;:{&quot;commandName&quot;:&quot;App\Jobs\ApproveJob&quot;,&quot;command&quot;:&quot;O:19:\&quot;App\Jobs\ApproveJob\&quot;:1:{s:6:\&quot;userId\&quot;;i:35649791;}&quot;}}</span>"\n
EOTTXT;

    expect($payload->id)->toBe('1234')
        ->and($payload->type)->toBe('dump')
        ->and($payload->dump['dump'])->toContain($sfDumpId);

    assertStringContainsString($expected, $payload->dump['dump']);

    expect($payload->dump['dump'])->toContain('<span class=sf-dump-protected title="Protected property">failed</span>: <span class=sf-dump-const>true</span>');
});
