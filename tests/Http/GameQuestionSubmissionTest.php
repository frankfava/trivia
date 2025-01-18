<?php

namespace Tests\Http;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GameQuestionSubmissionTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function can_submit_question_answer_in_a_game()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $gameQuestion = GameQuestion::factory()
            ->unanswered()
            ->for($game = Game::factory()->create([
                'status' => GameStatus::IN_PROGRESS->value,
                'meta' => ['reveal_correct_answers' => true],
            ]))
            ->for($question = Question::factory()->create())
            ->create();

        $game->users()->attach($user);

        $gameQuestion->lockForUser($user);

        $this->postJson(route('games.questions.answer', [$game, $question]), [
            'answer' => $question->correct_answer,
        ])->assertOk();
    }

    #[Test]
    public function answer_must_be_submitted()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $gameQuestion = GameQuestion::factory()
            ->unanswered()
            ->for($game = Game::factory()->create(['status' => GameStatus::IN_PROGRESS->value]))
            ->for($question = Question::factory()->create())
            ->create();

        $game->users()->attach($user);

        $gameQuestion->lockForUser($user);

        $this->postJson(route('games.questions.answer', [$game, $question]), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['answer']);
    }

    #[Test]
    public function forbidden_to_submit_answer_if_user_is_not_in_game()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $gameQuestion = GameQuestion::factory()
            ->unanswered()
            ->for($game = Game::factory()->create(['status' => GameStatus::IN_PROGRESS->value]))
            ->for($question = Question::factory()->create())
            ->create();

        $gameQuestion->lockForUser(User::factory()->create());

        $this->postJson(route('games.questions.answer', [$game, $question]), [
            'answer' => $question->correct_answer,
        ])->assertForbidden();
    }

    #[Test]
    public function forbidden_to_submit_if_last_fetched_by_another_user()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $gameQuestion = GameQuestion::factory()
            ->unanswered()
            ->for($game = Game::factory()->create(['status' => GameStatus::IN_PROGRESS->value]))
            ->for($question = Question::factory()->create())
            ->create();

        $game->users()->attach($user);

        $gameQuestion->lockForUser(User::factory()->create());

        $this->postJson(route('games.questions.answer', [$game, $question]), [
            'answer' => $question->correct_answer,
        ])->assertForbidden();
    }

    #[Test]
    public function forbidden_to_submit_if_has_not_been_locked_by_auth_user()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $gameQuestion = GameQuestion::factory()
            ->unanswered()
            ->for($game = Game::factory()->create(['status' => GameStatus::IN_PROGRESS->value]))
            ->for($question = Question::factory()->create())
            ->create();

        $game->users()->attach($user);

        $this->postJson(route('games.questions.answer', [$game, $question]), [
            'answer' => $question->correct_answer,
        ])->assertForbidden();
    }

    #[Test]
    public function validate_if_answer_is_correct_on_submission()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $gameQuestion = GameQuestion::factory()
            ->unanswered()
            ->for($game = Game::factory()->create(['status' => GameStatus::IN_PROGRESS->value]))
            ->for($question = Question::factory()->create(['correct_answer' => 'correct_answer']))
            ->create();

        $game->users()->attach($user);

        $gameQuestion->lockForUser($user);

        $this->postJson(route('games.questions.answer', [$game, $question]), [
            'answer' => 'wrong_answer',
        ])->assertOk();
    }
}
