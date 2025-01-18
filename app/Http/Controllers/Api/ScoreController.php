<?php

namespace App\Http\Controllers\Api;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ScoreController extends Controller
{
    public function index(Request $request, Game $game)
    {
        // Check if the user is part of the game
        $this->authorize('view', $game);

        if (! in_array($game->status, [GameStatus::IN_PROGRESS, GameStatus::COMPLETED])) {
            throw new AuthorizationException('Leaderboard is not available for this game.');
        }

        return Cache::remember('game_leaderboard_'.$game->id, 60, function () use ($game) {
            // Fetch scores using the scope
            $scores = Game::PlayerScoresQuery($game->id)->get();

            // Question Query
            $questionQuery = $game->questions();
            $gameQuestionQuery = $game->gameQuestions();

            // Get Question Stats
            $totalQuestions = (clone $questionQuery)->count();
            $answeredQuestions = (clone $questionQuery)->whereNotNull('answered_by_id')->count();
            $remainingQuestions = $totalQuestions - $answeredQuestions;
            $percentageCompleted = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0;

            // get Game Questions Stats
            $correctAnswers = (clone $gameQuestionQuery)->where('is_correct', true)->count();
            $incorrectAnswers = (clone $gameQuestionQuery)->where('is_correct', false)->count();
            $percentageCorrect = $answeredQuestions > 0 ? round(($correctAnswers / $answeredQuestions) * 100, 2) : 0;

            return response()->json([
                'leaderboard' => $scores,
                'game_status' => $game->status,
                'total_questions' => $totalQuestions,
                'remaining_questions' => $remainingQuestions,
                'correct_answers' => $correctAnswers,
                'incorrect_answers' => $incorrectAnswers,
                'percentage_correct' => $percentageCorrect,
                'percentage_completed' => $percentageCompleted,
            ]);
        });

    }

    /** Authenticated user score breakdown for current game */
    public function show(Request $request, Game $game)
    {
        // Check if the user is part of the game
        $this->authorize('view', $game);

        $user = $request->user();

        // Get Score
        return Cache::remember('user_score_'.$game->id.'_'.$user->id, 15, function () use ($game, $user) {

            $score = Game::PlayerScoresQuery($game->id, $user->id)->first();

            // Show answers
            $userAnswers = $game->getGameQuestionsWithAnswers(
                userId : $user->id,
                showCorrectAnswers : in_array($game->status, [GameStatus::COMPLETED, GameStatus::CANCELLED])
            );

            return response()->json([
                ...((array) $score),
                'answers' => $userAnswers,
            ]);
        });
    }
}
