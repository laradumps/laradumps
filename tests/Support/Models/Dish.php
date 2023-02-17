<?php

namespace LaraDumps\LaraDumps\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Dish extends Model
{
    protected $guarded = [];

    protected $table = 'dishes';
}
