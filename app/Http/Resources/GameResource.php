<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class GameResource extends ModelResource
{
    public function toArray(Request $request)
    {
        $isOnGame = auth()->check() ? $this->users->contains('id', auth()->id()) : null;
        $playerCount = $this->users->count();
        $maxPlayers = $this->meta['max_players'] ?? null;

        return [
            ...$this->resource->setHidden(['updated_at', 'meta', 'users'])->toArray(),
            'question_count' => $this->questions->count(),
            'is_owner' => auth()->check() ? $this->created_by_id == auth()->id() : null,
            'max_players' => $maxPlayers,
            'player_count' => $playerCount,
            'is_full' => $maxPlayers ? $playerCount >= $maxPlayers : true,
            'is_joined' => $isOnGame,
            'can_join' => $isOnGame ? false : ($maxPlayers ? $playerCount < $maxPlayers : true),
        ];
    }
}
