<?php

namespace LaraDumps\LaraDumps\Livewire\Attributes;

use LaraDumps\LaraDumps\Observers\LivewireObserver;
use Livewire\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Ds extends Attribute
{
    public function __construct(
        public bool $queries = true,
    ) {}

    public function boot(): void
    {
        app(LivewireObserver::class)->pin($this->getComponent()->getName());
    }
}
