<?php

namespace Tests\Http;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\GameQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GameQuestionsTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function can_index_questions_in_a_game()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->for($user, 'owner')
            ->has(GameQuestion::factory()->count(5), 'gameQuestions')
            ->create(['status' => GameStatus::IN_PROGRESS]);

        $game->users()->attach($user);

        $res = $this->getJson(route('games.questions.index', $game));

        $this->getJson(route('games.questions.index', $game))
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function cannot_index_question_if_user_is_not_in_game()
    {
        $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()->create(['status' => GameStatus::IN_PROGRESS]);

        $response = $this->getJson(route('games.questions.index', $game))
            ->assertForbidden();
    }

    #[Test]
    public function does_not_show_correct_answers_if_game_is_not_completed()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->has(GameQuestion::factory()->count(5), 'gameQuestions')
            ->create(['status' => GameStatus::IN_PROGRESS]);

        $game->users()->attach($user);

        $this->getJson(route('games.questions.index', $game))
            ->assertOk()
            ->assertJsonMissing(['correct_answer']);
    }
}
