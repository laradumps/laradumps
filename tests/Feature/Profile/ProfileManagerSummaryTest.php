<?php

use LaraDumps\LaraDumps\Profile\{ProfileEntry, ProfileManager};

it('computes self (exclusive) duration per entry', function () {
    $manager = new ProfileManager();
    $manager->start();

    $rootId = $manager->getRootEntryId();

    $controller = new ProfileEntry('controller', 'App\\Http\\Controllers\\DocumentController@index', 0, 100, $rootId);
    $manager->addEntry($controller);

    $view = new ProfileEntry('view', 'document.index', 0, 30, $controller->id);
    $manager->addEntry($view);

    $sql = new ProfileEntry('sql', 'select * from documents', 0, 20, $controller->id);
    $manager->addEntry($sql);

    $methodA = new ProfileEntry('method', 'DocumentIndex::{closure}', 0, 50, $rootId);
    $manager->addEntry($methodA);

    $methodB = new ProfileEntry('method', 'DocumentTable::{closure}', 0, 30, $methodA->id);
    $manager->addEntry($methodB);

    $methodC = new ProfileEntry('method', 'records()', 0, 10, $methodB->id);
    $manager->addEntry($methodC);

    $manager->stop();

    $entriesById = collect($manager->getEntries())->keyBy(fn (ProfileEntry $e) => $e->id);

    expect($entriesById[$controller->id]->selfDurationMs)->toBe(50.0)
        ->and($entriesById[$view->id]->selfDurationMs)->toBe(30.0)
        ->and($entriesById[$sql->id]->selfDurationMs)->toBe(20.0)
        ->and($entriesById[$methodA->id]->selfDurationMs)->toBe(20.0)
        ->and($entriesById[$methodB->id]->selfDurationMs)->toBe(20.0)
        ->and($entriesById[$methodC->id]->selfDurationMs)->toBe(10.0);
});

it('aggregates by_type using self time, avoiding double counting nested entries', function () {
    $manager = new ProfileManager();
    $manager->start();

    $rootId = $manager->getRootEntryId();

    $controller = new ProfileEntry('controller', 'App\\Http\\Controllers\\DocumentController@index', 0, 100, $rootId);
    $manager->addEntry($controller);

    $view = new ProfileEntry('view', 'document.index', 0, 30, $controller->id);
    $manager->addEntry($view);

    $sql = new ProfileEntry('sql', 'select * from documents', 0, 20, $controller->id);
    $manager->addEntry($sql);

    $methodA = new ProfileEntry('method', 'DocumentIndex::{closure}', 0, 50, $rootId);
    $manager->addEntry($methodA);

    $methodB = new ProfileEntry('method', 'DocumentTable::{closure}', 0, 30, $methodA->id);
    $manager->addEntry($methodB);

    $methodC = new ProfileEntry('method', 'records()', 0, 10, $methodB->id);
    $manager->addEntry($methodC);

    $data = $manager->stop();

    $byType = $data['summary']['by_type'];

    expect($byType['controller']['total_duration_ms'])->toBe(50.0)
        ->and($byType['view']['total_duration_ms'])->toBe(30.0)
        ->and($byType['sql']['total_duration_ms'])->toBe(20.0)
        ->and($byType['method']['total_duration_ms'])->toBe(50.0)
        ->and($byType['method']['count'])->toBe(3);
});

it('exposes self_duration_ms in the serialized entries', function () {
    $manager = new ProfileManager();
    $manager->start();

    $parent = new ProfileEntry('method', 'closureA', 0, 50, $manager->getRootEntryId());
    $manager->addEntry($parent);

    $child = new ProfileEntry('method', 'records()', 0, 10, $parent->id);
    $manager->addEntry($child);

    $data = $manager->stop();

    $entry = collect($data['entries'])->firstWhere('id', $parent->id);

    expect($entry['self_duration_ms'])->toBe(40.0);
});

it('attributes xhprof method time to the controller context instead of double counting it', function () {
    $manager = new ProfileManager();
    $manager->start();

    $rootId = $manager->getRootEntryId();

    // Controller span covers the render; its entry id is the XHProf context.
    $controller = new ProfileEntry('controller', 'DocumentIndex', 0, 60, $rootId);
    $manager->addEntry($controller);
    $manager->setContextEntryId($controller->id);

    $methodA = new ProfileEntry('method', 'DocumentIndex::{closure}', 0, 40, $controller->id);
    $manager->addEntry($methodA);

    $methodB = new ProfileEntry('method', 'records()', 0, 10, $methodA->id);
    $manager->addEntry($methodB);

    $data = $manager->stop();

    $byType = $data['summary']['by_type'];

    expect($byType['controller']['total_duration_ms'])->toBe(20.0)
        ->and($byType['method']['total_duration_ms'])->toBe(40.0)
        ->and($byType['controller']['total_duration_ms'])->toBeLessThan(60.0);
});

it('subtracts profiler overhead from the reported total so the metric never measures itself', function () {
    $manager = new ProfileManager();
    $manager->start();

    // Simulate 5ms spent inside the profiler's own machinery.
    $manager->addOverheadMs(5.0);

    $data = $manager->stop();

    expect($data)->toHaveKeys(['total_duration_ms', 'wall_duration_ms', 'overhead_ms'])
        ->and($data['overhead_ms'])->toBe(5.0)
        ->and($data['total_duration_ms'])->toBe(0.0)
        ->and($data['total_duration_ms'])->toBeLessThanOrEqual($data['wall_duration_ms']);

    $root = collect($data['entries'])->firstWhere('id', $manager->getRootEntryId());
    expect($root['duration_ms'])->toBe(0.0);
});
