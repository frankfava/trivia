<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Models\Game;

class GameController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Game::class);

        // List games
        // List games with pagination (10 per page, oldest first).
        // Return only games the user is associated with (created or joined).
    }

    public function show(Game $game)
    {
        $this->authorize('view', $game);

        // Show Game Details
        //  Status, players count, total questions, etc.
        // Map to Resource
    }

    public function store(GameRequest $request)
    {
        $this->authorize('create', Game::class);

        // Create a game - store
        // Create a game in pending status.
        // Accept options for max_players and show_correct_answers, how many questions
        // Assign Questions to Game
    }

    public function update(GameRequest $request, Game $game)
    {
        $this->authorize('update', $game);

        // ONLY If Pending
        // Update Game to change Question Count
        // Update Game to change max_player Count
    }

    public function destroy(Game $game)
    {
        $this->authorize('delete', $game);

        // Delete a game if:
        //     ONLY if It's in pending status.
        //     No questions have been answered.
    }
}
