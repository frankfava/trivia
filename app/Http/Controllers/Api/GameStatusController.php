<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameStatusController extends Controller
{
    public function store(Request $request, Game $game)
    {
        $this->authorize('update', $game);

        // Start the game:
        // Change status to in_progress.
    }

    public function destroy(Game $game)
    {
        $this->authorize('update', $game);

        // Mark the game as cancelled.
        // If cancelled, invalidate the leaderboard.
    }
}
