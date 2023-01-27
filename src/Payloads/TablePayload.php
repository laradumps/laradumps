<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Support\Collection;
use LaraDumps\LaraDumps\Actions\Table;

class TablePayload extends Payload
{
    public function __construct(
        private Collection | array  $data = [],
        private string $name = '',
    ) {
        if (blank($this->name)) {
            $this->name = 'Table';
        }
    }

    public function type(): string
    {
        return 'table';
    }

    /**
     * @return array
     */
    public function content(): array
    {
        return (new Table($this->data, $this->name))->make();
    }
}
