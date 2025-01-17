<?php

namespace Tests\Http;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GameJoinTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function a_user_can_join_a_pending_or_in_progress_game()
    {
        $this->withoutExceptionHandling();

        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->for($user, 'owner')
            ->create(['status' => GameStatus::PENDING]);

        $this->putJson(route('games.join', $game->id))
            ->assertOk();

        $this->assertTrue($game->users->contains($user));
    }

    #[Test]
    public function a_user_cannot_join_a_game_thats_not_pending_or_in_progress()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->for($user, 'owner')
            ->create(['status' => GameStatus::COMPLETED]);

        $this->putJson(route('games.join', $game->id))
            ->assertForbidden();
    }

    #[Test]
    public function a_user_cannot_join_a_game_if_max_players_are_reached()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->for($user, 'owner')
            ->create([
                'status' => GameStatus::PENDING,
                'meta' => ['max_players' => 2],
            ]);
        $game->users()->attach(User::factory()->count(2)->create());

        $this->putJson(route('games.join', $game->id))
            ->assertForbidden();
    }

    #[Test]
    public function when_the_game_owner_joins_it_does_not_update_the_status()
    {
        $owner = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->for($owner, 'owner')
            ->create(['status' => GameStatus::PENDING]);

        $this->putJson(route('games.join', $game->id))
            ->assertOk();

        $game->refresh();

        $this->assertEquals(GameStatus::PENDING, $game->status);
    }

    #[Test]
    public function show_success_if_user_is_already_in_game()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->for($user, 'owner')
            ->create(['status' => GameStatus::IN_PROGRESS]);

        $game->users()->attach($user);

        $this->putJson(route('games.join', $game->id))
            ->assertOk();
    }
}
