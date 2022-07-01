<?php

namespace LaraDumps\LaraDumps\Payloads;

class ValidateStringPayload extends Payload
{
    protected string $content;

    public function __construct(
        public string $type
    ) {
    }

    public function type(): string
    {
        return 'validate';
    }

    /** @return array<string> */
    public function content(): array
    {
        return [
            'type'    => $this->type,
            'content' => $this->content ?? '',
        ];
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
