<?php

namespace LaraDumps\LaraDumps\Profile;

class ProfileStack
{
    private array $stack = [];

    public function push(string $entryId): void
    {
        $this->stack[] = $entryId;
    }

    public function pop(): ?string
    {
        return array_pop($this->stack);
    }

    public function current(): ?string
    {
        if (empty($this->stack)) {
            return null;
        }

        return $this->stack[count($this->stack) - 1];
    }

    public function clear(): void
    {
        $this->stack = [];
    }

    public function depth(): int
    {
        return count($this->stack);
    }

    public function isEmpty(): bool
    {
        return empty($this->stack);
    }
}
