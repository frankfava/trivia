<?php

namespace App\Http\Controllers\Api;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Question;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Game::class);

        /** @var LengthAwarePaginator */
        $games = Game::query()
            ->oldest()
            ->with(['users'])
            ->when($request->query('is_owner'), fn ($query) => $query->ByOwner())
            ->when($request->query('is_player'), fn ($query) => $query->ByPlayer(include: true))
            ->when(
                value : $request->query('can_join'),
                callback : function ($query) {
                    $query
                        ->where(function ($sq) {
                            $sq
                                ->ByStatus(GameStatus::PENDING)
                                ->ByPlayer(include: false)
                                ->WithPlayerLimitReached(include : false);
                        });
                }
            )
            ->paginate(
                perPage : $request->query('per_page', 10),
                page : $request->query('page', 1)
            );

        return GameResource::create($games);
    }

    public function show(Game $game)
    {
        $this->authorize('view', $game);

        return GameResource::create($game);
    }

    public function store(GameRequest $request)
    {
        $this->authorize('create', Game::class);

        $payload = [
            'label' => $request->label,
            'created_by_id' => $request->user()->getKey(),
            'status' => GameStatus::PENDING->value,
            'meta' => [
                'max_players' => $request->max_players,
                'show_correct_answers' => $request->show_correct_answers ?? false,
            ],
        ];

        /** @var Game */
        $game = Game::create($payload);

        // Assign Questions to Game
        $questions = $request->number_of_questions ?? 0;
        if ($questions > 0) {
            GameQuestion::insert(
                Question::query()
                    ->inRandomOrder()
                    ->limit($questions)
                    ->get()
                    ->map(fn ($q) => [
                        'game_id' => $game->getKey(),
                        'question_id' => $q->getKey(),
                    ])
                    ->toArray()
            );
        }

        return response()->json(GameResource::create($game), 201);
    }

    public function destroy(Game $game)
    {
        $this->authorize('delete', $game);

        // Check if the game is in the PENDING status
        if ($game->status !== GameStatus::PENDING) {
            throw new UnprocessableEntityHttpException('Only games with a PENDING status can be deleted.');
        }

        if ($game->gameQuestions()->whereNotNull('answered_at')->exists()) {
            throw new UnprocessableEntityHttpException('Games with answered questions cannot be deleted.');
        }

        $game->delete();

        return response()->noContent();
    }
}
