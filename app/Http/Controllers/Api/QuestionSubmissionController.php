<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionSubmissionResource;
use App\Models\Game;
use App\Models\Question;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class QuestionSubmissionController extends Controller
{
    public function store(Request $request, Game $game, Question $question)
    {
        // Ensure user is in the game
        $this->authorize('view', $game);

        $gameQuestion = $game->gameQuestions()->where('question_id', $question->id)->firstOrFail();

        // Make sure it is assigned to this used
        if (! $gameQuestion->last_fetched_by) {
            throw new AuthorizationException('You cannot answer this question. You must first be assigned this question.');
        }

        // Ensure question is not locked by another user
        if ($gameQuestion->last_fetched_by !== $request->user()->id) {
            throw new AuthorizationException('You cannot answer this question. This question is assigned to another user.');
        }

        // Validate input
        $request->validate(['answer' => 'required']);

        // Calculate if the answer is correct
        $isCorrect = $request->input('answer') === $question->correct_answer;

        $gameQuestion->update([
            'answer' => $request->input('answer'),
            'answered_by_id' => $request->user()->id,
            'answered_at' => now(),
            'is_correct' => $isCorrect,
        ]);

        return QuestionSubmissionResource::create($gameQuestion);
    }
}
