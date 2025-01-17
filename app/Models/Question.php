<?php

namespace App\Models;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'difficulty',
        'question',
        'category_id',
        'correct_answer',
        'incorrect_answers',
    ];

    protected $casts = [
        'type' => QuestionType::class,
        'difficulty' => QuestionDifficulty::class,
        'category_id' => 'integer',
        'question' => 'string',
        'correct_answer' => 'string',
        'incorrect_answers' => 'json',
        'content_hash' => 'string',
    ];

    protected $hidden = [
        'content_hash',
    ];

    /** Scope to get questions by type */
    public function scopeByType($query, QuestionType $type)
    {
        return $query->where('type', $type->value);
    }

    /** Scope to get questions by difficulty */
    public function scopeByDifficulty($query, QuestionDifficulty $difficulty)
    {
        return $query->where('difficulty', $difficulty->value);
    }

    /** Scope to get questions for a specific category */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /** Category this Question belongs to */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** GameQuestions this Question is referenced on */
    public function gameQuestions()
    {
        return $this->hasMany(GameQuestion::class);
    }
}
