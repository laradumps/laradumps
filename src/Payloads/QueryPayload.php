<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Database\Query\Builder;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumpsCore\Payloads\Payload;

class QueryPayload extends Payload
{
    public function __construct(
        protected Builder $query
    ) {
    }

    public function content(): array
    {
        $toSql = str_replace(['?'], ['\'%s\''], $this->query->toSql());
        $toSql = vsprintf($toSql, $this->query->getBindings());

        return [
            'sql'       => $toSql,
            'formatted' => boolval(Config::get('send_queries.formatted')),
        ];
    }

    public function type(): string
    {
        return 'query';
    }
}
