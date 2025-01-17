<?php

namespace Tests\Http;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\GameQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GameStatusTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function only_game_owner_can_change_the_status_of_game_to_in_progress()
    {
        $owner = $this->makeUserAndAuthenticateWithToken();
        $otherUser = $this->makeUser();

        $game = Game::factory()
            ->for($owner, 'owner')
            ->has(GameQuestion::factory()->count(1), 'gameQuestions')
            ->create(['status' => GameStatus::PENDING]);

        // Owner can change status
        $this->putJson(route('games.start', $game))
            ->assertOk()
            ->assertJson(['status' => GameStatus::IN_PROGRESS->value]);

        // Other user cannot change status
        $this->actingAs($otherUser);
        $this->putJson(route('games.start', $game))
            ->assertForbidden();
    }

    #[Test]
    public function can_only_change_status_to_in_progress_if_has_questions()
    {
        $owner = $this->makeUserAndAuthenticateWithToken();

        $gameWithoutQuestions = Game::factory()
            ->for($owner, 'owner')
            ->create(['status' => GameStatus::PENDING]);

        // Game without questions cannot start
        $this->putJson(route('games.start', $gameWithoutQuestions))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['questions']);

        // Game with questions can start
        $gameWithQuestions = Game::factory()
            ->for($owner, 'owner')
            ->has(GameQuestion::factory()->count(5), 'gameQuestions')
            ->create(['status' => GameStatus::PENDING]);

        $this->putJson(route('games.start', $gameWithQuestions))
            ->assertOk()
            ->assertJson(['status' => GameStatus::IN_PROGRESS->value]);
    }

    #[Test]
    public function only_game_owner_can_cancel_the_game()
    {
        $owner = $this->makeUserAndAuthenticateWithToken();
        $otherUser = $this->makeUser();

        $game = Game::factory()
            ->for($owner, 'owner')
            ->has(GameQuestion::factory()->count(5), 'gameQuestions')
            ->create(['status' => GameStatus::IN_PROGRESS]);

        // Owner can cancel the game
        $this->deleteJson(route('games.cancel', $game))
            ->assertOk()
            ->assertJson(['status' => 'cancelled']);

        // Other user cannot cancel the game
        $this->actingAs($otherUser);
        $this->deleteJson(route('games.cancel', $game))
            ->assertForbidden();
    }

    #[Test]
    public function a_cancelled_game_can_be_resumed()
    {
        $owner = $this->makeUserAndAuthenticateWithToken();

        $gameWithoutQuestions = Game::factory()
            ->for($owner, 'owner')
            ->create(['status' => GameStatus::CANCELLED]);

        // Game without questions cannot start
        $this->putJson(route('games.resume', $gameWithoutQuestions))
            ->assertOk()
            ->assertJson(['status' => GameStatus::PENDING->value]);

        $gameWithQuestions = Game::factory()
            ->for($owner, 'owner')
            ->has(GameQuestion::factory()->count(5), 'gameQuestions')
            ->create(['status' => GameStatus::CANCELLED]);

        $this->putJson(route('games.resume', $gameWithQuestions))
            ->assertOk()
            ->assertJson(['status' => GameStatus::IN_PROGRESS->value]);
    }
}
