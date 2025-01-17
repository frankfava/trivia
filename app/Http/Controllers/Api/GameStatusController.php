<?php

namespace App\Http\Controllers\Api;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ModelResource;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GameStatusController extends Controller
{
    /** Start the Game */
    public function start(Request $request, Game $game)
    {
        $this->authorize('update', $game);

        if ($game->status !== GameStatus::PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Only pending games can be started.',
            ]);
        }

        if ($game->questions()->count() === 0) {
            throw ValidationException::withMessages([
                'questions' => 'Cannot start a game without questions.',
            ]);
        }

        // Update the status to in_progress
        $game->update(['status' => GameStatus::IN_PROGRESS]);

        return ModelResource::create($game);
    }

    /** Resume a cancelled Game */
    public function resume(Request $request, Game $game)
    {
        $this->authorize('update', $game);

        if ($game->status !== GameStatus::CANCELLED) {
            throw ValidationException::withMessages([
                'status' => 'Only cancelled games can be resumed.',
            ]);
        }

        $newStatus = $game->questions()->count() === 0 ? GameStatus::PENDING : GameStatus::IN_PROGRESS;

        $game->update(['status' => $newStatus]);

        return ModelResource::create($game);
    }

    public function cancel(Game $game)
    {
        $this->authorize('update', $game);

        if ($game->status === GameStatus::COMPLETED) {
            throw ValidationException::withMessages([
                'status' => 'Completed games cannot be cancelled.',
            ]);
        }

        // Update the status to cancelled
        $game->update(['status' => 'cancelled']);

        // @todo:  Get leaderboard cache
        // Cache::forget("game_leaderboard_{$game->id}");

        return response()->json([
            'message' => 'Game status updated to cancelled.',
            'status' => $game->status,
        ]);
    }
}
