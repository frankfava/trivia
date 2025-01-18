<?php

namespace App\Http\Resources;

use App\Models\GameQuestion;
use Illuminate\Http\Request;

class QuestionSubmissionResource extends ModelResource
{
    public function __construct(GameQuestion $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request)
    {

        $game = $this->resource->game;
        $question = $this->resource->question;

        $question->game = GameResource::create($this->resource->game);

        $question->makeHidden(['incorrect_answers', 'correct_answer']);

        // Hide Category ID
        $question->makeHidden(['category_id']);
        // Show description
        $question->category->setVisible(['name']);

        return [
            ...$question->toArray(),
            'submitted_answer' => $this->answer,
            $this->mergeWhen($game->meta['show_correct_answers'] ?? false, [
                'correct_answer' => $question->correct_answer,
            ]),
            'answered_at' => $this->answered_at,
            'is_correct' => $this->is_correct,
        ];
    }
}
