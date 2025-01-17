<?php

namespace Tests\Http;

use App\Models\Category;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Question;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuestionsTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function a_new_question_can_be_created_by_anyone()
    {
        $this->withoutExceptionHandling();

        $user = $this->makeUserAndAuthenticateWithToken();

        $data = [
            'type' => 'multiple',
            'question' => 'What is the capital of Australia?',
            'category' => 'Geography',
            'correct_answer' => 'Canberra',
            'incorrect_answers' => ['Sydney', 'Melbourne', 'Perth'],
        ];

        $this->postJson(route('questions.store'), $data)
            ->assertCreated();

        $this->assertDatabaseHas('questions', ['question' => $data['question']]);
    }

    #[Test]
    public function a_question_throw_validation_errors_if_not_submitted_properly()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $data = ['type' => '', 'category' => 'random', 'question' => ''];

        $this->postJson(route('questions.store'), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'question']);
    }

    #[Test]
    public function a_new_question_automatically_creates_new_categories_if_needed()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $data = [
            'type' => 'boolean',
            'question' => 'Is the earth flat?',
            'difficulty' => 'easy',
            'category' => 'Science',
            'correct_answer' => 'No',
            'incorrect_answers' => ['Yes'],
        ];

        $this->postJson(route('questions.store'), $data);

        $this->assertDatabaseHas('categories', ['name' => $data['category']]);
    }

    #[Test]
    public function a_new_question_automatically_uses_existing_category_if_found()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $category = Category::factory()->create(['name' => 'Technology']);

        $data = [
            'type' => 'multiple',
            'difficulty' => 'easy',
            'question' => 'What is AI?',
            'category' => 'Technology',
            'correct_answer' => 'Artificial Intelligence',
            'incorrect_answers' => ['Automated Innovation', 'Artificial Impulse', 'None of the above'],
        ];

        $this->postJson(route('questions.store'), $data);

        $this->assertEquals(1, Category::where('name', 'Technology')->count());

        $this->assertDatabaseHas('questions', ['question' => $data['question']]);
    }

    #[Test]
    public function a_question_can_be_deleted_if_not_attached_to_a_game()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $question = Question::factory()->create();

        $response = $this->deleteJson(route('questions.destroy', $question));

        $response->assertNoContent();
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    #[Test]
    public function a_question_cannot_be_deleted_if_attached_to_a_game()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $question = Question::factory()->create();
        $game = Game::factory()
            ->has(GameQuestion::factory(), 'gameQuestions')
            ->create();

        $question = $game->questions()->first();

        $response = $this->deleteJson(route('questions.destroy', $question));

        $response->assertForbidden();
        $this->assertDatabaseHas('questions', ['id' => $question->id]);
    }
}
