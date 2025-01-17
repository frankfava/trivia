<?php

use App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => 'pong')->name('ping');

Route::get('/user', fn (Request $request) => $request->user())->name('user');

// Manage Games
Route::apiResource('games', Api\GameController::class)->names('games');

// Start a Game
Route::match(['put', 'patch'], 'games/{game}/start', [Api\GameStatusController::class, 'start'])->name('games.start');

// Resume a Cancelled Game
Route::match(['put', 'patch'], 'games/{game}/resume', [Api\GameStatusController::class, 'resume'])->name('games.resume');

// Cancel the Game
Route::delete('games/{game}/cancel', [Api\GameStatusController::class, 'cancel'])->name('games.cancel');

// Add User to Game
Route::match(['put', 'patch'], 'games/{game}/join', [Api\GameJoinController::class, 'join'])->name('games.join');

// Manage Questions
Route::apiResource('questions', Api\QuestionController::class)->only(['store', 'destroy'])->names('questions');

// Managing Categories
Route::apiResource('categories', Api\CategoryController::class)->only(['index', 'show', 'destroy'])->names('categories');

// Get Questions on a Game
Route::get('games/{game}/questions', [Api\GameQuestionController::class, 'index'])->name('games.questions.index');

// Get the Next Question
Route::get('games/{game}/questions/next', [Api\NextQuestionController::class, 'show'])->name('games.questions.next');

// Submit an answer
Route::post('games/{game}/questions/{question}/answer', [Api\QuestionSubmissionController::class, 'store'])->name('games.questions.answer');

// Get the Current Users score
Route::get('games/{game}/score', [Api\ScoreController::class, 'show'])->name('games.myscore');

// Get the game Leaderboard
Route::get('games/{game}/leaderboard', [Api\ScoreController::class, 'index'])->name('games.leaderboard');
