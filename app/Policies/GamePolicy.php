<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Game $game)
    {
        return $user->id === $game->created_by_id || $game->users->contains($user);
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Game $game)
    {
        return $user->id === $game->created_by_id;
    }

    public function delete(User $user, Game $game)
    {
        return $user->id === $game->created_by_id;
    }
}
