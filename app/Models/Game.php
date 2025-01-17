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

    /** Scope to get active games only */
    public function scopeActive($query)
    {
        return $query->where('status', GameStatus::IN_PROGRESS);
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
