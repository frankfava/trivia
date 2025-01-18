<?php

namespace App\Console\Commands;

use App\Actions\FetchTriviaQuestions;
use Exception;
use Illuminate\Console\Command;

class FetchTriviaQuestionsCommand extends Command
{
    protected $signature = 'fetch:trivia-questions 
                            {--total=1000 : Number of questions to fetch}
                            {--difficulty= : The difficulty level of the questions (easy, medium, hard)}
                            {--type= : The type of question (multiple, boolean)} 
                            {--batch=50 : The number of questions to fetch per API request (max 50)}';

    protected $description = 'Fetch trivia questions from Open Trivia Database and store them in the database';

    public function handle()
    {
        $totalQuestions = $this->option('total');
        $difficulty = $this->option('difficulty');
        $type = $this->option('type');
        $questionsPerBatch = $this->option('batch');

        // Validate that questionsPerBatch is not greater than 50
        if ($questionsPerBatch > 50) {
            throw new Exception('The number of questions per batch cannot exceed 50.');
        }

        $this->info("Fetching $totalQuestions trivia questions...");

        $fetchTriviaQuestionsAction = new FetchTriviaQuestions(
            totalQuestions: $totalQuestions,
            difficulty: $difficulty,
            type: $type,
            questionsPerBatch: $questionsPerBatch,
            afterEachTry: function ($aggregateCount, $fetchedCount) {
                $this->line("Fetched $fetchedCount questions. Total: $aggregateCount");
            },
            onCompletion: fn ($fetchedCount) => $this->info("Fetched $fetchedCount questions.")
        );

        $fetchedCount = $fetchTriviaQuestionsAction->execute();

        $this->info('Successfully fetched trivia questions.');
    }
}
