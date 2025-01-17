<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Question;

class QuestionController extends Controller
{
    /** Submit a new Question */
    public function store(QuestionRequest $request)
    {
        $this->authorize('create', Question::class);
    }

    /** Delete a Question */
    public function destroy(Question $question)
    {
        // As long as its not used on a Question
        $this->authorize('delete', $question);
    }
}
