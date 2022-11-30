<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;
use Throwable;

class MailablePayload extends Payload
{
    /** @var boolean */
    private $preview = false;

    /** @var string */
    protected $html = '';

    /** @var \Illuminate\Mail\Mailable|null */
    protected $mailable = null;

    public static function forMailableTable(Mailable $mailable)
    {
        return new self(self::renderMailable($mailable), $mailable);
    }

    public static function forMailable(Mailable $mailable)
    {
        return new self(self::renderMailable($mailable), $mailable, true);
    }

    public function __construct(string $html, Mailable $mailable = null, bool $preview = false)
    {
        $this->html     = $html;
        $this->mailable = $mailable;
        $this->preview  = $preview;
    }

    public function type(): string
    {
        return $this->preview ? 'dump' : 'table';
    }

    public function content(): array
    {
        if ($this->preview) {
            return [
                'dump' => $this->html,
            ];
        }

        $content = [
            'dump' => $this->html,
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

        foreach ($content as $key => $value) {
            /** @var array<string> $values */
            $values[] = [
                'property' => Str::title($key),
                'value'    => $value,
            ];
        }

        return [
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
