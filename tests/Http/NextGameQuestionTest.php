<?php

namespace Tests\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NextGameQuestionTest extends TestCase
{
    use WithFaker;

    // can_fetch_the_next_question_in_a_game

    // cannot_fetch_next_question_if_user_is_not_in_game

    // does_not_fetch_a_locked_question

    // can_fetch_a_locked_if_more_than_5_minutes_passed_and_is_unanswered

    // show_404_if_game_is_not_in_progress

    // show_404_if_no_more_questions_are_available

    // fetching_the_question_locks_it
}
