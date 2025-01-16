<?php

use App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn() => 'pong');
Route::get('/user', function (Request $request) {
    return $request->user();
});
