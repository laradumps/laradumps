<?php

namespace LaraDumps\LaraDumps\Actions;

class PatternMatcher
{
    /**
     * Check if any pattern matches the given subject using case-insensitive substring or simple '*' glob.
     */
    public static function any(array $patterns, string $subject, bool $globMustCoverFullSubject = false): bool
    {
        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if ($pattern === null) {
                continue;
            }

            $pattern = trim((string) $pattern);
            if ($pattern === '') {
                continue;
            }

            // Glob support: convert '*' to '.*'
            if (str_contains($pattern, '*')) {
                $escaped = preg_quote($pattern, '/');
                $regexBody = str_replace('\\*', '.*', $escaped);
                $regex = $globMustCoverFullSubject ? '/^'.$regexBody.'$/i' : '/'.$regexBody.'/i';

                if (@preg_match($regex, $subject) === 1) {
                    return true;
                }

                continue;
            }

            if (stripos($subject, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
