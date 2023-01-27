<?php

namespace LaraDumps\LaraDumps\Tests\Classes;

use Illuminate\Contracts\Queue\ShouldQueue;

class TestJob implements ShouldQueue
{
    public function __construct(public mixed $data = null)
    {
    }

    public function handle()
    {
    }
}
