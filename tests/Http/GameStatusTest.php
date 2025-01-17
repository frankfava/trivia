<?php

namespace Tests\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GameStatusTest extends TestCase
{
    use WithFaker;

    // only_game_owner_can_change_the_status_of_game_to_in_progress

    // only_game_owner_can_cancel_the_game

    // status_of_the_games_changes_to_completed_automatically_when_last_question_is_submitted

    // a_cancelled_game_can_be_changed_back_to_in_progress
}
