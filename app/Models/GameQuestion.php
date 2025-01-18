<?php

namespace App\Models;

use Carbon\Carbon;
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
        'last_fetched_at',
        'last_fetched_by',
    ];

    protected $casts = [
        'game_id' => 'integer',
        'question_id' => 'integer',
        'answered_by_id' => 'integer',
        'answer' => 'string',
        'answered_at' => 'datetime',
        'is_correct' => 'boolean',
        'last_fetched_at' => 'datetime',
        'last_fetched_by' => 'integer',
    ];

    /** Determine if the question can be answers */
    public function canAnswerQuestion(): bool
    {
        return ! $this->last_fetched_by || $this->isStaleLocked();
    }

    /** Determine if the question is stale-locked. */
    public function isStaleLocked(): bool
    {
        return $this->last_fetched_at && $this->last_fetched_at->lt(Carbon::now()->subMinutes(5));
    }

    /** Mark a question as locked by a user.  */
    public function lockForUser(null|int|User $user, ?Carbon $datetime = null): static
    {
        $user ??= (auth()->check() ? auth()->user() : null);
        $userId = $user instanceof User ? $user->id : $user;

        if (! $this->canAnswerQuestion()) {
            return false;
        }

        $this->update([
            'last_fetched_at' => $datetime ??= Carbon::now(),
            'last_fetched_by' => $userId,
        ]);

        return $this;
    }

    // ========== Scopes

    /** Scope to get Questions that can be answered. Unlocked questions or questions with stale locks. */
    public function scopeCanAnswer($query)
    {
        $query
            ->whereNull('answered_at')
            ->where(function ($query) {
                $query
                    ->whereNull('last_fetched_by')
                    ->orWhere(function ($subQuery) {
                        $subQuery
                            ->whereNull('last_fetched_at')
                            ->orWhere('last_fetched_at', '<', Carbon::now()->subMinutes(5));
                    });
            });
    }

    /** Scope a query to only include answered questions. */
    public function scopeAnswered($query)
    {
        return $query->whereNotNull('answered_at');
    }

    // ========== Relationships

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

    /** User who locked this question */
    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'last_fetched_by');
    }
}
