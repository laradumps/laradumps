<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Support\Arr;

class CoffeePayload extends Payload
{
    public function type(): string
    {
        return 'coffee';
    }

    /** @return array<string, mixed> */
    public function content(): array
    {
        /* @phpstan-ignore-next-line */
        return $this->coffeeQuote();
    }

    private function coffeeQuote(): mixed
    {
        return Arr::random([
            ['quote' => 'Coffee. Creative lighter fluid.', 'author' => 'Floyd Maxwell'],
            ['quote' => 'I would rather take coffee than compliments just now.', 'author' => 'Louisa May Alcott'],
            ['quote' => 'Coffee smells like freshly ground heaven.', 'author' => 'Jessi Lane Adams'],
            ['quote' => 'I orchestrate my mornings to the tune of coffee.', 'author' => 'Terri Guillemets'],
            ['quote' => 'Adventure in life is good; consistency in coffee even better.', 'author' => 'Justina Chen Headley'],
            ['quote' => 'There is nothing sweeter than a cup of bitter coffee', 'author' => 'Rian Aditia'],
            ['quote' => 'Never underestimate the power of a good cup of coffee.', 'author' => 'Ursula Vernon'],
            ['quote' => 'Coffee is a kind of magic you can drink.', 'author' => 'Catherynne M. Valente'],
            ['quote' => 'Life is too short for bad coffee.', 'author' => 'Gord Downie'],
            ['quote' => 'Coffee smells like magic and fairytales', 'author' => 'Allison Czarnecki'],
            ['quote' => 'Coffee first. Schemes later.', 'author' => 'Leanna Renee Hieber'],
        ]);
    }
}
