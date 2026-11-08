<?php

use Illuminate\Http\{Request, Response};
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Observers\LivewireObserver;

$snapshot = fn (string $id, string $name, array $data, array $memo = []) => json_encode([
    'data' => $data,
    'memo' => ['id' => $id, 'name' => $name, ...$memo],
    'checksum' => 'x',
]);

$inHtml = fn (string $snapshot, string $id) => '<div wire:snapshot="'.htmlspecialchars($snapshot, ENT_QUOTES).'" wire:id="'.$id.'"></div>';

it('diffs an update request against its response', function () use ($snapshot) {
    $items = (new LivewireObserver())->collect(
        [[
            'snapshot' => $snapshot('a1', 'checkout-form', ['name' => 'Ana', 'total' => 108, 'items' => [[1, 2], ['s' => 'arr']]]),
            'updates' => ['name' => 'Ana Beatriz'],
            'calls' => [['method' => 'save', 'params' => [], 'metadata' => []]],
        ]],
        [[
            'snapshot' => $snapshot(
                'a1',
                'checkout-form',
                ['name' => 'Ana Beatriz', 'total' => 108, 'items' => [[1, 2, 3], ['s' => 'arr']]],
                ['errors' => ['email' => ['The email field must be a valid email address.']]]
            ),
            'effects' => ['dispatches' => [['name' => 'cart-updated', 'params' => ['total' => 135]]], 'html' => '<div wire:id="a1"></div>'],
        ]],
    );

    expect($items)->toHaveCount(1)
        ->and($items[0])->toMatchArray([
            'id' => 'a1',
            'name' => 'checkout-form',
            'action' => 'update',
            'updates' => ['name' => 'Ana Beatriz'],
            'calls' => [['method' => 'save', 'params' => []]],
            'changes' => ['name' => ['Ana', 'Ana Beatriz'], 'items' => [[1, 2], [1, 2, 3]]],
            'state' => ['name' => 'Ana Beatriz', 'total' => 108, 'items' => [1, 2, 3]],
            'errors' => ['email' => ['The email field must be a valid email address.']],
            'dispatches' => [['name' => 'cart-updated', 'params' => ['total' => 135]]],
        ]);
});

it('skips components the update did not run and adds children it rendered for the first time', function () use ($snapshot, $inHtml) {
    $items = (new LivewireObserver())->collect(
        [
            ['snapshot' => $snapshot('a1', 'cart', []), 'updates' => [], 'calls' => []],
            ['snapshot' => $snapshot('b1', 'sidebar', []), 'updates' => [], 'calls' => []],
        ],
        [
            ['snapshot' => $snapshot('a1', 'cart', []), 'effects' => ['html' => '<div wire:id="a1">'.$inHtml($snapshot('c1', 'cart-summary', ['count' => 2]), 'c1').'</div>']],
            ['skip' => true, 'id' => 'b1'],
        ],
    );

    expect(array_column($items, 'action', 'name'))->toBe(['cart' => 'update', 'cart-summary' => 'mount'])
        ->and($items[1]['state'])->toBe(['count' => 2]);
});

it('reads every component of a page load from its HTML', function () use ($snapshot, $inHtml) {
    $user = [null, ['class' => 'App\Models\User', 'key' => 5, 's' => 'mdl']];
    $html = $inHtml($snapshot('p1', 'dashboard', ['user' => $user]), 'p1').$inHtml($snapshot('n1', 'nav', []), 'n1');

    $items = (new LivewireObserver())->collect([], [], "<html><body>{$html}</body></html>");

    expect(array_column($items, 'name'))->toBe(['dashboard', 'nav'])
        ->and($items[0]['state'])->toBe(['user' => 'App\Models\User#5']);
});

it('unwraps Livewire synth tuples', function () {
    expect(LivewireObserver::unwrap([
        'tags' => [['a', 'b'], ['s' => 'arr']],
        'posts' => [null, ['keys' => [1, 2], 'class' => 'Illuminate\Database\Eloquent\Collection', 'modelClass' => 'App\Models\Post', 's' => 'elcln']],
        'when' => ['2026-11-06', ['type' => 'carbon', 's' => 'cbn']],
        'plain' => [1, 2],
    ]))->toBe([
        'tags' => ['a', 'b'],
        'posts' => 'App\Models\Post#1,2',
        'when' => '2026-11-06',
        'plain' => [1, 2],
    ]);
});

it('labels profile timings by call, listener and child', function () use ($snapshot, $inHtml) {
    $observer = new LivewireObserver();

    (fn () => $this->profile = [
        'a1' => [['hydrate', 1.0], ['call0', 4.0], ['call1', 2.0], ['child:c1', 3.0], ['render', 5.0]],
    ])->call($observer);

    $items = $observer->collect(
        [[
            'snapshot' => $snapshot('a1', 'cart', []),
            'updates' => [],
            'calls' => [
                ['method' => 'save', 'params' => []],
                ['method' => '__dispatch', 'params' => ['cart-updated', ['total' => 1]]],
            ],
        ]],
        [['snapshot' => $snapshot('a1', 'cart', []), 'effects' => ['html' => $inHtml($snapshot('c1', 'cart-summary', []), 'c1')]]],
    );

    expect($items[0]['profile'])->toBe([
        ['kind' => 'hydrate', 'label' => 'hydrate', 'ms' => 1.0],
        ['kind' => 'call', 'label' => 'save', 'ms' => 4.0],
        ['kind' => 'listener', 'label' => 'cart-updated', 'ms' => 2.0],
        ['kind' => 'child', 'label' => 'cart-summary', 'ms' => 3.0],
        ['kind' => 'render', 'label' => 'render', 'ms' => 5.0],
    ]);
});

it('sends only components picked on the page or pinned by Ds', function () use ($snapshot, $inHtml) {
    $sent = [];
    $this->mock(LaraDumps::class)->shouldReceive('send')->andReturnUsing(function ($payload) use (&$sent) {
        $sent[] = array_column($payload->content()['components'], 'name');

        return $payload;
    });

    $page = new Response($inHtml($snapshot('a1', 'cart', []), 'a1').$inHtml($snapshot('b1', 'nav', []), 'b1').$inHtml($snapshot('c1', 'footer', []), 'c1'));
    $observer = new LivewireObserver();

    $observer->flush(Request::create('/'), $page);
    expect($sent)->toBe([]);

    cache()->forever(LivewireObserver::SELECTED_KEY, ['cart']);
    $observer->flush(Request::create('/'), $page);

    $observer->pin('footer');
    $observer->flush(Request::create('/'), $page);

    $observer->flush(Request::create('/'), $page);

    expect($sent)->toBe([['cart'], ['cart', 'footer'], ['cart']]);
});
