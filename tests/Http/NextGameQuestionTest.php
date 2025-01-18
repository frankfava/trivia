<?php

namespace Tests\Http;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NextGameQuestionTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function can_fetch_the_next_question_in_a_game()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->has(GameQuestion::factory(), 'gameQuestions')
            ->create(['status' => GameStatus::IN_PROGRESS->value]);

        $game->users()->attach($user);

        $this->getJson(route('games.questions.next', [$game]))
            ->assertOk()
            ->assertJsonFragment(['id' => $game->questions()->first()->id]);

    }

    #[Test]
    public function cannot_fetch_next_question_if_user_is_not_in_game()
    {
        $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->has(GameQuestion::factory(), 'gameQuestions')
            ->create(['status' => GameStatus::IN_PROGRESS->value]);

        $this->getJson(route('games.questions.next', [$game]))
            ->assertForbidden();
    }

    #[Test]
    public function does_not_fetch_a_locked_question()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $gameQuestion = GameQuestion::factory()
            ->for($game = Game::factory()->create([
                'status' => GameStatus::IN_PROGRESS->value,
            ]))
            ->create();

        $game->users()->attach($user);

        $game->gameQuestions()->save($gameQuestion->lockForUser(User::factory()->create()));

        $this->getJson(route('games.questions.next', [$game]))
            ->assertNotFound();
    }

    #[Test]
    public function can_fetch_a_locked_if_more_than_5_minutes_passed_and_is_unanswered()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $gameQuestion = GameQuestion::factory()
            ->for($game = Game::factory()->create([
                'status' => GameStatus::IN_PROGRESS->value,
            ]))
            ->create();

        $game->users()->attach($user);

        $game->gameQuestions()->save(
            $gameQuestion->lockForUser(
                user : User::factory()->create(),
                datetime: now()->subMinutes(6)
            )
        );

        $this->getJson(route('games.questions.next', [$game]))
            ->assertOk()
            ->assertJsonFragment(['id' => $gameQuestion->question->id]);
    }

    #[Test]
    public function show_403_if_game_is_not_in_progress()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->has(GameQuestion::factory(), 'gameQuestions')
            ->create(['status' => GameStatus::PENDING->value]);

        $game->users()->attach($user);

        $response = $this->getJson(route('games.questions.next', [$game]))
            ->assertForbidden();
    }

    #[Test]
    public function show_404_if_no_questions_are_available()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->create(['status' => GameStatus::IN_PROGRESS->value]);

        $game->users()->attach($user);

        $this->getJson(route('games.questions.next', [$game]))
            ->assertNotFound();
    }

    #[Test]
    public function show_404_if_no_more_unanswered_questions_are_available()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->has(GameQuestion::factory()->answered()->count(3), 'gameQuestions')
            ->create(['status' => GameStatus::IN_PROGRESS->value]);

        $game->users()->attach($user);

        $this->getJson(route('games.questions.next', [$game]))
            ->assertNotFound();
    }

    #[Test]
    public function fetching_the_question_locks_it()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->has(GameQuestion::factory(), 'gameQuestions')
            ->create(['status' => GameStatus::IN_PROGRESS->value]);

        $game->users()->attach($user);

        $gameQuestion = $game->gameQuestions()->first();

        $res = $this->getJson(route('games.questions.next', [$game]));

        $this->assertDatabaseHas((new GameQuestion)->getTable(), [
            'id' => $gameQuestion->id,
            'last_fetched_by' => $user->id,
        ]);
    }
}
