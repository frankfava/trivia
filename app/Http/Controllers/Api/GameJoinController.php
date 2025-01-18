<?php

namespace App\Http\Controllers\Api;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Auth\Access\AuthorizationException;
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
        if (! in_array($game->status, [GameStatus::PENDING])) {
            throw new AuthorizationException('Cannot join a game that is not pending.');
        }

        // Check if the game has reached the maximum number of players
        $maxPlayers = $game->meta['max_players'] ?? null;
        if ($maxPlayers && $game->users()->count() >= $maxPlayers) {
            throw new AuthorizationException('Game has reached the maximum number of players.');
        }

        // Add the user to the game
        $game->users()->attach($request->user());

        // Return success response
        return response()->json([
            'message' => 'You have successfully joined the game.',
        ], 200);
    }
}
