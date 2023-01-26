<?php

namespace LaraDumps\LaraDumps\Tests\Classes;

use Illuminate\Contracts\Queue\ShouldQueue;

class TestJob implements ShouldQueue
{
    public $data;

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function handle()
    {
    }
}
