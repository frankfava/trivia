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
            'answer' => ($answer = $this->faker->optional()->words(2, true)),
            'answered_by_id' => ((bool) $answer ? User::factory() : null),
            'is_correct' => null,
            'answered_at' => (bool) $answer ? $this->faker->dateTime : null,
            'last_fetched_at' => null,
            'last_fetched_by' => null,
        ];
    }

    public function unanswered(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer' => null,
            'answered_by_id' => null,
            'answered_at' => null,
        ]);
    }

    public function answered(): static
    {
        return $this->state(fn (array $attributes) => [
            'answer' => $this->faker->words(2, true),
            'answered_by_id' => User::factory(),
            'answered_at' => $this->faker->dateTime,
        ]);
    }
}
