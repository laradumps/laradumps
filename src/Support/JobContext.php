<?php

namespace LaraDumps\LaraDumps\Support;

final class JobContext
{
    /** @var array<int, array{job_id: string, display_name: string}> */
    private static array $stack = [];

    public static function push(string $jobId, string $displayName): void
    {
        self::$stack[] = ['job_id' => $jobId, 'display_name' => $displayName];
    }

    public static function pop(): void
    {
        array_pop(self::$stack);
    }

    /** @return array{job_id: string, display_name: string}|null */
    public static function current(): ?array
    {
        return empty(self::$stack) ? null : self::$stack[array_key_last(self::$stack)];
    }

    public static function clear(): void
    {
        self::$stack = [];
    }
}
