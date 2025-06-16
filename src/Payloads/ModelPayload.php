<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Database\Eloquent\Model;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class ModelPayload extends Payload
{
    public function __construct(
        protected Model $model,
    ) {}

    public function type(): string
    {
        return 'model';
    }

    /** @return array<string, mixed> */
    public function content(): array
    {
        $relations = $this->model->relationsToArray();

        return [
            'relations' => $this->model->relationsToArray() ? Dumper::dump($relations) : [],
            'className' => get_class($this->model),
            'attributes' => Dumper::dump($this->model->attributesToArray()),
        ];
    }

    public function toScreen(): array|Screen
    {
        return new Screen('home');
    }

    public function withLabel(): array|Label
    {
        return [];
    }
}
