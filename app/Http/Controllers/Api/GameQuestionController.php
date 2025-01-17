<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameQuestionController extends Controller
{
    public function index(Request $request, Game $game)
    {
        $this->authorize('view', $game);
    }
}
