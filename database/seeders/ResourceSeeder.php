<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Game;
use App\Models\Category;
use App\Models\Question;
use App\Models\GameQuestion;

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        // Create Categories
        $categories = Category::factory()->count(5)->create();


        // Create Questions for Each Category
        $categories->each(function ($category) {
            Question::factory()->count(20)->create([
                'category_id' => $category->id,
            ]);
        });

        // Create Games and Assign Users
        $games = Game::factory()->count(5)->create([
            'created_by_id' => $users->random()->id,
        ]);

        $games->each(function ($game) use ($users) {
            // Assign Users to Games
            $game->users()->attach($users->random(rand(2, 5))->pluck('id')->toArray());

            // Assign Questions to Game
            Question::inRandomOrder()->take(10)->get()->each(function ($question) use ($game) {
                GameQuestion::factory()->create([
                    'game_id' => $game->id,
                    'question_id' => $question->id,
                ]);
            });
        });
    }
}
