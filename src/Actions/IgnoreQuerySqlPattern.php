<?php

namespace LaraDumps\LaraDumps\Actions;

class IgnoreQuerySqlPattern
{
    public static function execute(string $sql): bool
    {
        $patterns = config('laradumps.queries.ignore_sql_patterns', []);

        if (blank($patterns)) {
            return false;
        }

        return PatternMatcher::any($patterns, $sql, false);
    }
}
