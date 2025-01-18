<?php

namespace App\Http\Resources;

use App\Models\GameQuestion;
use Illuminate\Http\Request;

class NextQuestionResource extends ModelResource
{
    public function __construct(GameQuestion $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request)
    {
        $question = $this->resource->question;

        $question->game = GameResource::create($this->resource->game);

        // Create Array with all Options mixed
        $question->options = array_unique(array_filter([
            ...($question->incorrect_answers ??= []),
            $question->correct_answer,
        ]));
        // Hide answers
        $question->makeHidden(['incorrect_answers', 'correct_answer']);

        // Hide Category ID
        $question->makeHidden(['category_id']);
        // Show description
        $question->category->setVisible(['name']);

        return $question->toArray();
    }
}
