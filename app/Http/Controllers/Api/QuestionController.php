<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Http\Resources\ModelResource;
use App\Models\Category;
use App\Models\Question;

class QuestionController extends Controller
{
    /** Submit a new Question */
    public function store(QuestionRequest $request)
    {
        $this->authorize('create', Question::class);

        $validated = $request->validated();

        // Find or create the category
        $categoryId = $validated['category_id'] ??= Category::firstOrCreate(['name' => $validated['category']])->id;

        $question = Question::create([
            'type' => $validated['type'],
            'question' => $validated['question'],
            'difficulty' => $validated['difficulty'],
            'category_id' => $categoryId,
            'correct_answer' => $validated['correct_answer'],
            'incorrect_answers' => $validated['incorrect_answers'],
        ]);

        return response()->json(ModelResource::create($question), 201);
    }

    /** Delete a Question */
    public function destroy(Question $question)
    {
        // As long as its not used on a Game
        $this->authorize('delete', $question);

        $question->delete();

        return response()->noContent();
    }
}
