<?php

namespace App\Http\Controllers\Api;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ModelResource;
use App\Models\Game;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GameQuestionController extends Controller
{
    public function index(Request $request, Game $game)
    {
        $this->authorize('view', $game);

        // Ensure the user is part of the game
        if (! $game->users->contains($request->user())) {
            throw new AuthorizationException('You are not part of this game.');
        }

        /** @var LengthAwarePaginator Fetch questions related to the game */
        $questions = $game
            ->questions()
            ->with('category')
            ->paginate(
                perPage : $request->query('per_page', 10),
                page : $request->query('page', 1)
            );

        // Retrieve questions for the game
        $items = $questions->getCollection()->map(function ($question) use ($game) {
            if ($game->status !== GameStatus::COMPLETED) {
                $question->makeHidden(['correct_answer', 'incorrect_answers', 'category_id']);
                $question->category->setVisible(['id', 'name']);
            }

            return $question;
        });

        // Re-add mapped collection back to paginator
        $questions->setCollection($items);

        return ModelResource::create($questions);
    }
}
