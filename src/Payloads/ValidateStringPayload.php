<?php

namespace LaraDumps\LaraDumps\Payloads;

class ValidateStringPayload extends Payload
{
    protected string $content;

    protected bool $caseSensitive = false;

    protected bool $wholeWord = false;

    public function __construct(
        public string $type
    ) {
    }

    public function type(): string
    {
        return 'validate';
    }

    /** @return array<string|boolean> */
    public function content(): array
    {
        return [
            'type'              => $this->type,
            'content'           => $this->content ?? '',
            'is_case_sensitive' => $this->caseSensitive,
            'is_whole_word'     => $this->wholeWord,
        ];
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setCaseSensitive(bool $caseSensitive = true): self
    {
        $this->caseSensitive = $caseSensitive;

        return $this;
    }

    public function SetWholeWord(bool $wholeWord = true): self
    {
        $this->wholeWord = $wholeWord;

        return $this;
    }
}
