<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumps\Support\Dumper;

class TableV2Payload extends Payload
{
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
            return Dumper::dump($value);
        }, $this->values);

        return [
            'values' => $values,
            'label'  => $this->label,
        ];
    }
}
