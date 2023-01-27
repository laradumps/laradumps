<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Collection;

class Table
{
    public function __construct(
        private Collection | array $data = [],
        private string             $name = '',
    ) {
    }

    public function make(): array
    {
        $values  = [];
        $columns = [];

        if ($this->data instanceof Collection) {
            $this->data = $this->data->toArray();
        }

        foreach ($this->data as $row) {
            foreach ($row as $key => $item) {
                if (!in_array($key, $columns)) {
                    $columns[] = $key;
                }
            }

            $value = [];
            foreach ($columns as $column) {
                $value[$column] = (string) $row[$column];
            }

            $values[] = $value;
        }

        return [
            'fields' => $columns,
            'values' => $values,
            'header' => $columns,
            'label'  => $this->name,
        ];
    }
}
