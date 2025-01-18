<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }
}
