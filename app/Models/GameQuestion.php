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
        'answered_at',
    ];

    protected $dates = [
        'answered_at',
    ];

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
