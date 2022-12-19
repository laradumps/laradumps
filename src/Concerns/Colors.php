<?php

namespace LaraDumps\LaraDumps\Concerns;

use LaraDumps\LaraDumps\LaraDumps;

trait Colors
{
    public function danger(): LaraDumps
    {
        if (boolval(config('laradumps.send_color_in_screen'))) {
            return $this->toScreen('danger', true);
        }

        return $this->color('red');
    }

    public function dark(): LaraDumps
    {
        return $this->color('black');
    }

    public function warning(): LaraDumps
    {
        if (boolval(config('laradumps.send_color_in_screen'))) {
            return $this->toScreen('warning', true);
        }

        return $this->color('orange');
    }

    public function success(): LaraDumps
    {
        if (boolval(config('laradumps.send_color_in_screen'))) {
            return $this->toScreen('success', true);
        }

        return $this->color('green');
    }

    public function info(): LaraDumps
    {
        if (boolval(config('laradumps.send_color_in_screen'))) {
            return $this->toScreen('info', true);
        }

        return $this->color('blue');
    }
}
