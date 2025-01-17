<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class NextQuestionController extends Controller
{
    public function show(Request $request, Game $game)
    {
        $this->authorize('view', $game);

        // Fetch the next unanswered question.
        // Check if the user is part of the game.
        // Fetch the next unlocked question for the game.
        // Lock the question (locked_by, locked_at) to the user.
        // Handle stale locks.
    }
}
