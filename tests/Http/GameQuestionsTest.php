<?php

namespace Tests\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GameQuestionsTest extends TestCase
{
    use WithFaker;

    // can_index_questions_in_a_game

    // cannot_index_question_if_user_is_not_in_game

    // does_not_show_correct_answers_if_game_is_not_completed
}
