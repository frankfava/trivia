<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameQuestion>
 */
class GameQuestionFactory extends Factory
{
    protected $model = GameQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'question_id' => Question::factory(),
            'answered_by_id' => User::factory(),
            'answer' => $this->faker->words(2, true), // Answered or not
            'is_correct' => null,
            'answered_at' => $this->faker->optional()->dateTime,
            'last_fetched_at' => null,
            'last_fetched_by' => null,
        ];
    }
}
