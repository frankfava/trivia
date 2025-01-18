<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;

    protected $fillable = [
        'label',
        'status',
        'created_by_id',
        'meta',
    ];

    protected $casts = [
        'status' => GameStatus::class,
        'created_by_id' => 'integer',
        'meta' => 'json',
    ];

    /** Scope to get games by status */
    public function scopeByStatus($query, GameStatus $status)
    {
        return $query->where('status', $status->value);
    }

    /** Scope to get games by Owner */
    public function scopeByOwner($query, null|int|User $owner = null, bool $include = true)
    {
        $owner ??= (auth()->check() ? auth()->user() : null);
        if ($owner instanceof User) {
            $owner = $owner->id;
        }

        return $query->where('created_by_id', ($include ? '=' : '!='), $owner);
    }

    /** Scope to get games by Player */
    public function scopeByPlayer($query, null|int|User $owner = null, bool $include = true)
    {
        $owner ??= (auth()->check() ? auth()->user() : null);
        if ($owner instanceof User) {
            $owner = $owner->id;
        }

        return call_user_func(
            [$query, $include ? 'whereHas' : 'whereDoesntHave'],
            'users',
            fn ($query) => $query->where('users.id', auth()->id())
        );
    }

    public function scopeWithPlayerLimitReached($query, bool $include = true)
    {
        return $query->whereRaw("
            JSON_EXTRACT(meta, '$.max_players') IS NOT NULL
            AND CAST(JSON_EXTRACT(meta, '$.max_players') AS INTEGER) ".($include ? '<=' : '>').'
            (SELECT COUNT(*) FROM game_user WHERE game_user.game_id = games.id)
        ');
    }

    /** User that created this game */
    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /** Users on this Game (Pivot) */
    public function users()
    {
        return $this->belongsToMany(User::class, (new GameUser)->getTable())
            ->using(GameUser::class);
    }

    /** Questions on this Game */
    public function gameQuestions()
    {
        return $this->hasMany(GameQuestion::class);
    }

    /** Questions on this Game */
    public function questions()
    {
        return $this->hasManyThrough(Question::class, GameQuestion::class, 'game_id', 'id', 'id', 'question_id');
    }
}
