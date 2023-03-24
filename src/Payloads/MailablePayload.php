<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Mail\Mailable;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use Throwable;

class MailablePayload extends Payload
{
    /** @var string */
    protected $html = '';

    /** @var \Illuminate\Mail\Mailable|null */
    protected $mailable = null;

    public function __construct(Mailable $mailable)
    {
        $this->html     = self::renderMailable($mailable);
        $this->mailable = $mailable;
    }

    public function type(): string
    {
        return 'mailable';
    }

    public function content(): array
    {
        $content = [
            'html' => $this->html,
            'from' => [],
            'to'   => [],
            'cc'   => [],
            'bcc'  => [],
        ];

        if ($this->mailable) {
            $content = array_merge($content, [
                'mailable_class' => get_class($this->mailable),
                'from'           => $this->convertToPersons($this->mailable->from),
                'subject'        => $this->mailable->subject,
                'to'             => $this->convertToPersons($this->mailable->to),
                'cc'             => $this->convertToPersons($this->mailable->cc),
                'bcc'            => $this->convertToPersons($this->mailable->bcc),
            ]);
        }

        return $content;
    }

    protected static function renderMailable(Mailable $mailable): string
    {
        try {
            return $mailable->render();
        } catch (Throwable $exception) {
            return "Mailable could not be rendered because {$exception->getMessage()}";
        }
    }

    protected function convertToPersons(array $persons): array
    {
        return collect($persons)
            ->map(function (array $person) {
                return  [
                    'email' => $person['address'],
                    'name'  => $person['name'] ?? '',
                ];
            })->toArray();
    }
}
