<?php

namespace LaraDumps\LaraDumps\Concerns;

use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\LaraDumps;

trait Colors
{
    public function danger(): LaraDumps
    {
        if (boolval(Config::get('send_color_in_screen'))) {
            return $this->toScreen('danger', true);
        }

        return $this->color('border-red-300');
    }

    public function dark(): LaraDumps
    {
        return $this->color('border-black');
    }

    public function warning(): LaraDumps
    {
        if (boolval(Config::get('send_color_in_screen'))) {
            return $this->toScreen('warning', true);
        }

        return $this->color('border-orange-300');
    }

    public function success(): LaraDumps
    {
        if (boolval(Config::get('send_color_in_screen'))) {
            return $this->toScreen('success', true);
        }

        return $this->color('border-green-600');
    }

    public function info(): LaraDumps
    {
        if (boolval(Config::get('send_color_in_screen'))) {
            return $this->toScreen('info', true);
        }

        return $this->color('border-blue-600');
    }
}
