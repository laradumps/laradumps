<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;
use Throwable;

class MailablePayload extends Payload
{
    public function __construct(
        protected string $html,
        protected ?Mailable $mailable = null,
    ) {
    }

    public static function forMailable(Mailable $mailable): self
    {
        return new self(self::renderMailable($mailable), $mailable);
    }

    public function type(): string
    {
        return 'mailable';
    }

    public function content(): array
    {
        $content = [
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

        $values = [];

        foreach ($content as $key => $value) {
            /** @var array<string> $values */
            $values[] = [
                'property' => Str::title($key),
                'value'    => $value,
            ];
        }

        return [
            'html'   => $this->html,
            'fields' => [
                'property',
                'value',
            ],
            'values' => $values,
            'header' => [
                'Property',
                'Value',
            ],
            'label' => 'Mailable',
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

    protected function convertToPersons(array $persons): array
    {
        return collect($persons)
            ->map(function (array $person) {
                $name = $person['name'] ?? '';

                return "email: {$person['address']}, name: {$name}";
            })
            ->toArray();
    }
}
