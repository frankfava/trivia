<?php

namespace Tests\Feature;

use App\Enums\GameStatus;
use App\Events\GameCompleted;
use App\Listeners\NotifyGameCompleted;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Question;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GameCompletionTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function it_marks_game_as_completed_and_dispatches_event_when_all_questions_are_answered()
    {
        // Arrange
        Event::fake([GameCompleted::class]);

        $game = Game::factory()
            ->create(['status' => GameStatus::IN_PROGRESS]);

        $player = $this->makeUser();

        $game->users()->attach($player->id);

        $gameQuestions = GameQuestion::factory()
            ->for($question = Question::factory()->create())
            ->for($game)
            ->count(4)
            ->create();

        foreach ($gameQuestions as $gameQuestion) {
            $gameQuestion->update([
                'question_id' => $question->id,
                'answer' => $question->correct_answer,
                'answered_by_id' => $player->id,
                'answered_at' => now(),
                'is_correct' => true,
                'last_fetched_at' => now()->subMinutes(3),
                'last_fetched_by' => $player->id,
            ]);
        }

        $game->refresh();

        $this->assertEquals(GameStatus::COMPLETED, $game->status);

        Event::assertDispatched(GameCompleted::class, function ($event) use ($game) {
            return $event->game->id === $game->id;
        });

        Event::assertListening(GameCompleted::class, NotifyGameCompleted::class);
    }
}
