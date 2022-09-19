<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\LogPayload;
use LaraDumps\LaraDumps\Support\Dumper;

class LogObserver
{
    public function register(): void
    {
        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            if (!$this->isEnabled()) {
                return;
            }

            $dumps = new LaraDumps();

            /** @var array $config */
            $config    = config('laradumps.level_log_colors_map');

            $levelColor = $config[$message->level];
            $bgColor    = Str::of($levelColor)->replace('border', 'bg');

            if ($message->level == 'debug') {
                $message->level = 'info';
            }

            $filter = <<<HTML
<div id="filter-log-$message->level" x-on:click="filterScreen('log-$message->level', true)"
     class="py-2 cursor-pointer px-4 hover:bg-slate-100 dark:hover:bg-slate-600 dark:hover-text-slate-800">
     <div class="flex gap-2 items-center justify-start">
         <div class="rounded-sm $bgColor w-[1rem] h-[1rem] justify-center items-center flex text-white">
             <span x-show="filteredChildren === 'log-$message->level'">&#10004;</span>
         </div>
         <span class="capitalize">$message->level</span>
     </div>
</div>
HTML;

            $log       = [
                'message'     => $message->message,
                'level'       => $message->level,
                'level_color' => $levelColor,
                'context'     => Dumper::dump($message->context),
                'filter'      => $filter,
            ];

            $dumps->send(new LogPayload($log));

            $dumps->toScreen('Logs');
        });
    }

    public function isEnabled(): bool
    {
        return (bool) config('laradumps.send_log_applications');
    }
}
