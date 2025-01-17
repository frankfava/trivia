<?php

namespace Tests\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ScoreborardTest extends TestCase
{
    use WithFaker;

    // user_can_get_their_score_for_a_particular_game

    // user_cannot_see_score_if_they_are_not_in_game

    // will_show_correct_answers_for_the_questions_if_the_game_is_completed_or_cancelled

    // user_can_get_the_leaderboard_for_a_game

    // leaderboard_does_not_show_player_scores_unless_in_progress_or_completed

    // user_cannot_leaderboard_if_they_are_not_in_game
}
