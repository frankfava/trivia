<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionSubmissionController extends Controller
{
    public function store(Request $request, Game $game, Question $question)
    {
        $this->authorize('create', GameQuestion::class);

        // Submit
        // Verify the user locked the question.
        // Check if the answer is correct and update is_correct.
        // Handle duplicate submissions.
    }
}
