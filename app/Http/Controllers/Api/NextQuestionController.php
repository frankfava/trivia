<?php

namespace App\Http\Controllers\Api;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\NextQuestionResource;
use App\Models\Game;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NextQuestionController extends Controller
{
    /** Fetch the next unanswered question. */
    public function show(Request $request, Game $game)
    {
        // Ensure user is in the game
        $this->authorize('view', $game);

        // Ensure game is in progress
        if ($game->status !== GameStatus::IN_PROGRESS) {
            throw new AuthorizationException('Game is not in progress.');
        }

        // Find the next question that is not answered
        $gameQuestion = $game->gameQuestions()
            ->CanAnswer()
            ->inRandomOrder()
            ->with(['question' => fn ($r) => $r->with('category')])
            ->first();

        if (! $gameQuestion) {
            throw new NotFoundHttpException('No more questions available.');
        }

        // Lock the question
        $gameQuestion->lockForUser($request->user());

        return NextQuestionResource::create($gameQuestion);
    }
}
