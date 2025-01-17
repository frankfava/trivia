<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameJoinController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Game::class);

        // If owner, then add owner to pivot and change status
        // Add the user to the pivot table.
        // Prevent joining if the game is full.
    }
}
