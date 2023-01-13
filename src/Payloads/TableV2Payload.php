<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumps\Concerns\Converter;

class TableV2Payload extends Payload
{
    use Converter;

    public function __construct(
        protected array $values,
        protected string $label = 'Table',
        protected string $type = 'table-v2'
    ) {
    }

    public function type(): string
    {
        return $this->type ?? 'table-v2';
    }

    public function content(): array
    {
        $values = array_map(function ($value) {
            return $this->convertToPrimitive($value);
        }, $this->values);

        return [
            'values' => $values,
            'label'  => $this->label,
        ];
    }
}
