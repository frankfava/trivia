<?php

namespace Tests\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GameJoinTest extends TestCase
{
    use WithFaker;

    // a_user_can_join_a_pending_or_in_progress_game

    // a_user_cannot_join_a_game_thats_not_pending_or_in_progress

    // a_user_cannot_join_a_game_if_max_players_are_reached

    // when_the_game_owner_joins_it_changes_the_status_to_in_progress

    // show_success_if_user_is_already_in_game
}
