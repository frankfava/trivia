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

class GamesTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function you_need_to_be_authenticated_to_list_games()
    {
        $this->getJson(route('games.index'))
            ->assertUnauthorized();
    }

    #[Test]
    public function can_list_games_in_oldest_order()
    {
        $this->makeUserAndAuthenticateWithToken();

        Game::factory(4)->create();

        $res = $this->getJson(route('games.index', [
            'per_page' => 2,
            'page' => 1,
        ]));
        $res->assertJsonCount(2, 'data');
    }

    #[Test]
    public function can_list_games_in_that_user_is_owner()
    {
        $owner = $this->makeUserAndAuthenticateWithToken();
        $otherUser = $this->makeUser();

        Game::factory()
            ->for($owner, 'owner')
            ->count(2)
            ->create();

        Game::factory()
            ->for($otherUser, 'owner')
            ->count(2)
            ->create();

        $response = $this->getJson(route('games.index', [
            'is_owner' => true,
            'per_page' => 4,
            'page' => 1,
        ]));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['created_by_id' => $owner->id]);
    }

    #[Test]
    public function can_list_games_in_that_user_is_player()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        // Games with no Player
        Game::factory()
            ->count(2)
            ->create();

        // Game that user belongs to
        $validGames = Game::factory()
            ->count(2)
            ->create()
            ->each(function ($game) use ($user) {
                $game->users()->attach($user->id);
            });

        $response = $this->getJson(route('games.index', [
            'is_player' => true,
            'per_page' => 4,
            'page' => 1,
        ]));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $validGames->first()->id]);
    }

    #[Test]
    public function can_list_games_in_that_user_can_join()
    {
        $user = $this->makeUserAndAuthenticateWithToken(); // Create and authenticate the user

        // A valid game
        Game::factory()
            ->create(['status' => GameStatus::PENDING]);

        // Invalid: InProgress
        Game::factory()
            ->create(['status' => GameStatus::IN_PROGRESS]);

        // Invalid: Use is already on it
        Game::factory()
            ->create(['status' => GameStatus::PENDING])
            ->users()->attach($user->id);

        // Invalid: Max Players reaches
        Game::factory()
            ->create([
                'meta' => ['max_players' => 2],
                'status' => GameStatus::PENDING,
            ])
            ->users()->attach(
                User::factory(2)->create()->pluck('id')->toArray()
            );

        $response = $this->getJson(route('games.index', [
            'can_join' => true,
            'per_page' => 6,
            'page' => 1,
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['status' => GameStatus::PENDING->value]);
    }

    #[Test]
    public function you_can_view_a_single_game_if_owner()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->for($user, 'owner')
            ->create();

        $this->getJson(route('games.show', [$game]))
            ->assertOk()
            ->assertJsonFragment(['id' => $game->id]);
    }

    #[Test]
    public function can_view_game_if_not_owner_and_is_on_game()
    {
        $game = Game::factory()
            ->create(['status' => GameStatus::PENDING]);

        $user = $this->makeUserAndAuthenticateWithToken();

        $game->users()->attach($user->id);

        $response = $this->getJson(route('games.show', $game));

        $response->assertOk()
            ->assertJsonFragment(['id' => $game->id]);
    }

    #[Test]
    public function cannot_view_game_if_not_owner_and_not_on_game()
    {
        $game = Game::factory()->create(['status' => GameStatus::PENDING]);

        $this->makeUserAndAuthenticateWithToken();

        $response = $this->getJson(route('games.show', $game));

        $response->assertForbidden();
    }

    #[Test]
    public function a_game_can_be_created()
    {
        $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()->raw();

        $this->postJson(route('games.store'), $game)
            ->assertStatus(201);

        $this->assertTrue(Game::where('label', $game['label'])->exists());
    }

    #[Test]
    public function a_new_game_has_a_label()
    {
        $this->makeUserAndAuthenticateWithToken();

        $response = $this->postJson(route('games.store'), ['label' => ''])->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'label',
        ]);
    }

    #[Test]
    public function a_new_game_is_pending()
    {
        $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()->raw();

        $res = $this->postJson(route('games.store'), $game)
            ->assertStatus(201);

        $game = $this->original($res);

        $this->assertEquals($game->status, GameStatus::PENDING);
    }

    #[Test]
    public function a_new_game_is_owned_by_auth_user()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()->raw();

        $res = $this->postJson(route('games.store'), $game)
            ->assertStatus(201);

        $game = $this->original($res);

        $this->assertTrue($game->owner->is($user));
    }

    #[Test]
    public function a_new_game_is_assigned_questions()
    {
        $this->makeUserAndAuthenticateWithToken();

        Question::factory(2)->create();

        $res = $this->postJson(route('games.store'), [
            'label' => $this->faker->words(2, true),
            'number_of_questions' => 2,
        ])
            ->assertStatus(201);

        $game = $this->original($res);

        $this->assertCount(2, $game->gameQuestions);
    }

    #[Test]
    public function owner_can_delete_game_when_pending()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->for($user, 'owner')
            ->create(['status' => GameStatus::PENDING]);

        $this->deleteJson(route('games.destroy', [$game]))
            ->assertStatus(204);

        $this->assertCount(0, Game::all());
    }

    #[Test]
    public function non_owner_cannot_delete_game()
    {
        $owner = $this->makeUser();

        $game = Game::factory()
            ->for($owner, 'owner')
            ->create(['status' => GameStatus::PENDING]);

        $this->makeUserAndAuthenticateWithToken();

        $response = $this->deleteJson(route('games.destroy', $game));

        $response->assertForbidden();
        $this->assertDatabaseHas('games', ['id' => $game->id]);
    }

    #[Test]
    public function game_cannot_be_deleted_by_anyone_if_not_pending()
    {
        $owner = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->for($owner, 'owner')
            ->create(['status' => GameStatus::COMPLETED]);

        $response = $this->deleteJson(route('games.destroy', $game));

        $response->assertUnprocessable();
        $this->assertDatabaseHas('games', ['id' => $game->id]);
    }

    #[Test]
    public function game_cannot_be_deleted_by_anyone_if_any_questions_have_been_answered()
    {
        $owner = $this->makeUserAndAuthenticateWithToken();

        $game = Game::factory()
            ->for($owner, 'owner')
            ->has(GameQuestion::factory()->state(fn () => ['answered_at' => now()->subMinutes(4)]), 'gameQuestions')
            ->create(['status' => GameStatus::PENDING]);

        $response = $this->deleteJson(route('games.destroy', $game))
            ->assertUnprocessable();

        $this->assertDatabaseHas('games', ['id' => $game->id]);
    }
}
