<?php

namespace App\Actions;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use Closure;
use Illuminate\Support\Facades\Http;

class FetchTriviaQuestions
{
    const API_URL = 'https://opentdb.com/api.php?';

    protected ?Closure $afterEachTry;

    protected ?Closure $onCompletion;

    public function __construct(
        readonly protected ?int $totalQuestions = 1000,
        readonly protected ?QuestionDifficulty $difficulty = null,
        readonly protected ?QuestionType $type = null,
        readonly protected int $questionsPerBatch = 50,
        ?Closure $afterEachTry = null,
        ?Closure $onCompletion = null,
    ) {
        $this->afterEachTry = $afterEachTry ?: null;
        $this->onCompletion = $onCompletion ?: null;
    }

    public function execute()
    {
        $fetchedQuestions = 0;

        while ($fetchedQuestions < $this->totalQuestions) {
            $response = Http::get(self::API_URL, array_filter([
                'amount' => $this->questionsPerBatch,
                'difficulty' => $this->difficulty->value,
                'type' => $this->type->value,
            ]));
            $json = $response->json();

            if ($json['response_code'] != 0) {
                continue;
            }

            $questions = $json['results'] ?? [];

            if (! empty($questions)) {
                foreach ($questions as $question) {
                    if ($fetchedQuestions >= $this->totalQuestions) {
                        break;
                    }
                    if ($this->storeQuestion($question)) {
                        $fetchedQuestions++;
                    }
                }
            }

            if ($this->afterEachTry instanceof Closure) {
                call_user_func($this->afterEachTry, $fetchedQuestions, count($questions));
            }
        }

        if ($this->onCompletion instanceof Closure) {
            call_user_func($this->onCompletion, $fetchedQuestions);
        }

        return $fetchedQuestions;
    }

    protected function storeQuestion(array $questionData)
    {
        $contentHash = substr(md5(json_encode($questionData)), 0, 12);

        // Check if question already exists based on the content hash
        $existingQuestion = Question::where('content_hash', $contentHash)->exists();

        if (! $existingQuestion) {
            Question::forceCreate([
                'type' => $questionData['type'],
                'question' => $questionData['question'],
                'category_id' => $this->getCategoryId($questionData['category']),
                'difficulty' => $questionData['difficulty'],
                'correct_answer' => $questionData['correct_answer'],
                'incorrect_answers' => count($d = $questionData['incorrect_answers']) == 1 ? $d[0] : $d,
                'content_hash' => $contentHash,
            ]);

            return true;
        }

        return false;
    }

    protected function getCategoryId($categoryName)
    {
        // If Category doesn't exist, create it
        $category = \App\Models\Category::firstOrCreate(['name' => $categoryName]);

        return $category->id;
    }
}
