<?php

namespace Database\Factories;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => $this->faker->words(2, true),
            'status' => $this->faker->randomElement(GameStatus::cases())->value,
            'created_by_id' => User::factory(), // Creates a user if not provided
            'meta' => [
                'max_players' => $this->faker->numberBetween(1, 10),
                'show_correct_answers' => $this->faker->boolean(),
            ],
        ];
    }
}
