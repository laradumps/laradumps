<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumps\Concerns\Converter;

class TableV2Payload extends Payload
{
    use Converter;

    /** @var array */
    protected $values;

    /** @var string */
    protected $label;

    /** @var null|string */
    protected $type = null;

    public function __construct(array $values, string $label = 'Table', string $type = 'table-v2')
    {
        $this->values = $values;

        $this->label = $label;

        $this->type = $type;
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
