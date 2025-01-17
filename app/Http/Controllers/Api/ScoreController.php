<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;

class ScoreController extends Controller
{
    public function index()
    {
        // $this->authorize('view', Game::class);

        // Show all player scores in descending order.
        // Game status.
        // Remaining unanswered questions.
        // Percentage of questions completed.
    }

    public function show(Game $game)
    {
        // $this->authorize('view', $game);

        // Fetch the user’s current score.
        // Include a breakdown of:
        // Questions answered.
        // Correct/incorrect answers.
        // Correct answers for incorrect ones (optional).
    }
}
