<?php

namespace Database\Factories;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(QuestionType::cases())->value,
            'difficulty' => $this->faker->randomElement(QuestionDifficulty::cases())->value,
            'category_id' => Category::factory(),
            'question' => $this->faker->sentence,
            'correct_answer' => $this->faker->word,
            'incorrect_answers' => json_encode($this->faker->words(3)),
            'content_hash' => substr($this->faker->sha256, 0, 12)
        ];
    }
}
