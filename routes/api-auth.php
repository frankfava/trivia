<?php

use App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => 'pong');

Route::get('/user', fn (Request $request) => $request->user());

// Manage Games
Route::apiResource('games', Api\GameController::class);

// Change Game Status
Route::post('games/{game}/start', [Api\GameStatusController::class, 'store'])->name('games.start');

// Cancel the Game
Route::post('games/{game}/cancel', [Api\GameStatusController::class, 'destory'])->name('games.end');

// Add User to Game
Route::post('games/{game}/join', [Api\GameJoinController::class, 'store'])->name('games.join');

// Manage Questions
Route::apiResource('questions', Api\QuestionController::class)->only(['store', 'destroy']);

// Managing Categories
Route::apiResource('categories', Api\CategoryController::class)->only(['index', 'show', 'destroy']);

// Get Questions on a Game
Route::post('games/{game}/questions', [Api\GameQuestionController::class, 'index'])->name('games.questions.index');

// Get the Next Question
Route::get('games/{game}/questions/next', [Api\NextQuestionController::class, 'show'])->name('games.questions.next');

// Submit an answer
Route::post('games/{game}/questions/{question}/answer', [Api\QuestionSubmissionController::class, 'store'])->name('games.questions.answer');

// Get the Current Users score
Route::get('games/{game}/score', [Api\ScoreController::class, 'show'])->name('games.myscore');

// Get the game Leaderboard
Route::get('games/{game}/leaderboard', [Api\ScoreController::class, 'index'])->name('games.leaderboard');
