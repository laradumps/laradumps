<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Mail\Mailable;
use Throwable;

class MailablePayload extends Payload
{
    /** @var string */
    protected $html = '';

    public static function forMailable(Mailable $mailable)
    {
        return new self(self::renderMailable($mailable));
    }

    public function __construct(string $html)
    {
        $this->html = $html;
    }

    public function type(): string
    {
        return 'dump';
    }

    public function content(): array
    {
        return [
            'dump' => $this->html,
        ];
    }

    protected static function renderMailable(Mailable $mailable): string
    {
        try {
            return $mailable->render();
        } catch (Throwable $exception) {
            return "Mailable could not be rendered because {$exception->getMessage()}";
        }
    }
}
