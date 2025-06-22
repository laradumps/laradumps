<?php

namespace LaraDumps\LaraDumps\Observers;

use LaraDumps\LaraDumpsCore\Actions\Config;

class BaseObserver
{
    protected string $label = '';

    protected bool $enabled = false;

    public function isEnabled(string $key): bool
    {
        if (! boolval(Config::get('observers.'.$key, false))) {
            return $this->enabled;
        }

        return boolval(Config::get('observers.'.$key, false));
    }

    public function enable(string $label = ''): void
    {
        $this->label = $label;
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }
}
