<?php

namespace Tests\Http;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\GameQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ScoreboardTest extends TestCase
{
    use WithFaker;

    // ==== My Score

    #[Test]
    public function user_can_get_their_score_for_a_particular_game()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()->create(['status' => GameStatus::IN_PROGRESS->value]);

        $game->users()->attach($user);

        GameQuestion::factory()
            ->answered(correctly: true, user: $user)
            ->for($game)
            ->count(2)
            ->create();

        GameQuestion::factory()
            ->answered(correctly: false, user: $user)
            ->for($game)
            ->create();

        $this->getJson(route('games.myscore', [$game]))
            ->assertOk()
            ->assertJsonFragment(['score' => 2]);
    }

    #[Test]
    public function user_cannot_see_score_if_they_are_not_in_game()
    {
        $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()->create(['status' => GameStatus::IN_PROGRESS->value]);

        $this->getJson(route('games.myscore', [$game]))
            ->assertForbidden();
    }

    #[Test]
    public function will_show_correct_answers_for_the_questions_on_my_score_if_the_game_is_completed_or_cancelled()
    {
        $this->withoutExceptionHandling();

        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()->create(['status' => GameStatus::COMPLETED->value]);

        $game->users()->attach($user);

        GameQuestion::factory()
            ->answered(correctly: true, user: $user)
            ->for($game)
            ->count(2)
            ->create();

        GameQuestion::factory()
            ->answered(correctly: false, user: $user)
            ->for($game)
            ->count(2)
            ->create();

        $this->getJson(route('games.myscore', [$game]))
            ->assertOk()
            ->assertJsonStructure([
                'score',
                'answers' => [],
            ]);
    }

    // ==== Leaderboard

    #[Test]
    public function user_can_get_the_leaderboard_for_a_game()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()->create(['status' => GameStatus::IN_PROGRESS->value]);

        $game->users()->attach($user);
        $game->users()->attach($otherUser = $this->makeUser());

        GameQuestion::factory()
            ->for($game)
            ->count(2)
            ->create();

        GameQuestion::factory()
            ->answered(correctly: true, user: $user)
            ->for($game)
            ->count(2)
            ->create();

        GameQuestion::factory()
            ->answered(correctly: true, user: $otherUser)
            ->for($game)
            ->count(2)
            ->create();

        GameQuestion::factory()
            ->answered(correctly: false, user: $otherUser)
            ->for($game)
            ->count(4)
            ->create();

        $response = $this->getJson(route('games.leaderboard', [$game]))
            ->assertOk()
            ->assertJsonCount(2, 'leaderboard')
            ->assertJsonPath('leaderboard.0.score', 2)
            ->assertJsonPath('leaderboard.1.score', 2)
            ->assertJsonPath('total_questions', 10)
            ->assertJsonPath('remaining_questions', 2)
            ->assertJsonPath('correct_answers', 4)
            ->assertJsonPath('incorrect_answers', 4)
            ->assertJsonPath('percentage_correct', 50)
            ->assertJsonPath('percentage_completed', 80);
    }

    #[Test]
    public function user_cannot_leaderboard_if_they_are_not_in_game()
    {
        $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()->create(['status' => GameStatus::IN_PROGRESS->value]);

        $this->getJson(route('games.leaderboard', [$game]))
            ->assertForbidden();
    }

    #[Test]
    public function leaderboard_does_not_show_player_scores_unless_in_progress_or_completed()
    {
        $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()->create(['status' => GameStatus::PENDING->value]);

        $this->getJson(route('games.leaderboard', [$game]))
            ->assertForbidden();
    }
}
