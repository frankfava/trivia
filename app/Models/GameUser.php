<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GameUser extends Pivot
{
    public $table = 'game_user';

    public $timestamps = false;

    protected $casts = [
        'game_id' => 'integer',
        'user_id' => 'integer',
    ];
}
