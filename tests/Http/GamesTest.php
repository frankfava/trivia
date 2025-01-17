<?php

namespace Tests\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GamesTest extends TestCase
{
    use WithFaker;

    // Index Games

    // Can View

    // can_view_game_if_not_owner_and_is_on_game

    // cannot_view_game_if_not_owner_and_not_on_game

    // can_create_game

    // owner_can_update_game_when_pending

    // non_owner_cannot_update_game_even_if_pending

    // game_cannot_be_updated_by_anyone_if_not_pending

    // owner_can_delete_game_when_pending

    // non_owner_cannot_delete_game_even_if_pending

    // game_cannot_be_deleted_by_anyone_if_not_pending

}
