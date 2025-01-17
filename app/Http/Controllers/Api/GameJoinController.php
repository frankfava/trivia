<?php

namespace App\Http\Controllers\Api;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameJoinController extends Controller
{
    public function join(Request $request, Game $game)
    {
        // Authorize the user to join the game
        $this->authorize('view', $game);

        // Check if the user is already part of the game
        if ($game->users->contains($request->user())) {
            return response()->json([
                'message' => 'You are already part of this game.',
            ], 200);
        }

        // Validate the game status
        if (! in_array($game->status, [GameStatus::PENDING, GameStatus::IN_PROGRESS])) {
            return response()->json([
                'message' => 'Cannot join a game that is not pending or in progress.',
            ], 403);
        }

        // Check if the game has reached the maximum number of players
        $maxPlayers = $game->meta['max_players'] ?? null;
        if ($maxPlayers && $game->users()->count() >= $maxPlayers) {
            return response()->json([
                'message' => 'Game has reached the maximum number of players.',
            ], 403);
        }

        // Add the user to the game
        $game->users()->attach($request->user());

        // Return success response
        return response()->json([
            'message' => 'You have successfully joined the game.',
        ], 200);
    }
}
