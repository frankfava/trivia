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
            'answer' => null,
            'answered_by_id' => null,
            'answered_at' => null,
            'is_correct' => null,
            'last_fetched_at' => null,
            'last_fetched_by' => null,
        ];
    }

    public function answered(bool $correctly = true, ?User $user = null): static
    {
        return $this->state(function (array $attributes) use ($correctly, $user) {
            // Get Question
            $questionAttr = $attributes['question_id'];

            /** @var Question */
            $question = match (true) {
                $questionAttr instanceof \Database\Factories\QuestionFactory => $questionAttr->create(),
                $questionAttr instanceof Question => $questionAttr,
                is_int($questionAttr) => Question::find($questionAttr)
            };

            $user ??= User::factory();

            return [
                'question_id' => $question,
                'answer' => $correctly ? $question->correct_answer : $this->faker->words(2, true),
                'answered_by_id' => $user,
                'answered_at' => now(),
                'is_correct' => $correctly,
                'last_fetched_at' => now()->subMinutes(3),
                'last_fetched_by' => $user,
            ];
        });
    }
}
