<?php

namespace LaraDumps\LaraDumps\Contracts;

interface TraceableContract
{
    public function setTrace(array $trace): array;
}
