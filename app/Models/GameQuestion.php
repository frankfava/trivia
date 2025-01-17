<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\GameQuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'game_id',
        'question_id',
        'answered_by_id',
        'answer',
        'is_correct',
        'answered_at',
        'locked_at',
    ];

    protected $casts = [
        'game_id' => 'integer',
        'question_id' => 'integer',
        'answered_by_id' => 'integer',
        'answer' => 'string',
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    /**
     * Scope to filter unlocked questions or questions with stale locks.
     */
    public function scopeIsUnlocked($query)
    {
        $query->where(function ($query) {
            $query->whereNull('locked_at');
        });
    }

    /**
     * Determine if the question is stale-locked.
     */
    public function isStaleLocked(): bool
    {
        return $this->locked_at;
    }

    /** Scope a query to only include answered questions. */
    public function scopeAnswered($query)
    {
        return $query->whereNotNull('answered_at');
    }

    /** Game this entry belongs to */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /** Question this entry belongs to/ references */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /** User who answered this question */
    public function answeredBy()
    {
        return $this->belongsTo(User::class, 'answered_by_id');
    }
}
