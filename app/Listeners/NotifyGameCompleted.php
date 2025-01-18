<?php

namespace App\Listeners;

use App\Events\GameCompleted;

class NotifyGameCompleted
{
    public function handle(GameCompleted $event)
    {
        // Example: Send a notification, log, or trigger other actions
        logger()->info("Game {$event->game->id} has been marked as completed.");
    }
}
