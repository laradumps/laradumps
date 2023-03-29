<?php

namespace LaraDumps\LaraDumps\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

class TestDatabase
{
    /**
     * Migrate and seed Dish
     *
     * @return void
     */
    public static function up(): void
    {
        self::migrate();
        self::seed();
    }

    /**
     * Drop databases
     *
     * @return void
     */
    public static function down(): void
    {
        Schema::dropIfExists('dishes');
    }

    public static function migrate(): void
    {
        self::down();

        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public static function seed(array $dishes = []): void
    {
        DB::table('dishes')->truncate();

        if (empty($dishes)) {
            $dishes = self::generate();
        }

        DB::table('dishes')->insert($dishes);
    }

    public static function generate(): array
    {
        return [
            [
                'name' => 'Pastel de Nata',
            ],
            [
                'name' => 'Peixada da chef Nábia',
            ],
            [
                'name' => 'Carne Louca',
            ],
            [
                'name' => 'Bife à Rolê',
            ],
            [
                'name' => 'Francesinha vegana',
            ],
        ];
    }
}
