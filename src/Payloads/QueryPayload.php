<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use LaraDumps\LaraDumpsCore\Payloads\Payload;

class QueryPayload extends Payload
{
    public function __construct(
        protected Builder $query
    ) {
    }

    public function content(): array
    {
        $toSql = DB::getQueryGrammar()
            ->substituteBindingsIntoRawSql(
                $this->query->toSql(),
                $this->query->getBindings()
            );

        return [
            'sql' => $toSql,
        ];
    }

    public function type(): string
    {
        return 'query';
    }
}
