<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    // ========== Scopes

    /** Scope to get games by status */
    public function scopeByStatus($query, GameStatus $status)
    {
        return $query->where('status', $status->value);
    }

    /** Scope to get games by Owner */
    public function scopeByOwner($query, null|int|User $user = null, bool $include = true)
    {
        $user ??= (auth()->check() ? auth()->user() : null);
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('created_by_id', ($include ? '=' : '!='), $userId);
    }

    /** Scope to get games by Player */
    public function scopeByPlayer($query, null|int|User $user = null, bool $include = true)
    {
        $user ??= (auth()->check() ? auth()->user() : null);
        $userId = $user instanceof User ? $user->id : $user;

        return call_user_func(
            [$query, $include ? 'whereHas' : 'whereDoesntHave'],
            'users',
            fn ($query) => $query->where('users.id', $userId)
        );
    }

    /** Scope to get games by Player Limit Reached */
    public function scopeWithPlayerLimitReached($query, bool $include = true)
    {
        return $query->whereRaw("
            JSON_EXTRACT(meta, '$.max_players') IS NOT NULL
            AND CAST(JSON_EXTRACT(meta, '$.max_players') AS INTEGER) ".($include ? '<=' : '>').'
            (SELECT COUNT(*) FROM game_user WHERE game_user.game_id = games.id)
        ');
    }

    /** Scope Games that are Open to Join */
    public function scopeOpenToJoin($query)
    {
        return $query
            ->ByStatus(GameStatus::PENDING)
            ->ByPlayer(include: false)
            ->WithPlayerLimitReached(include : false);
    }

    /** Query Player scores */
    public static function PlayerScoresQuery(int $gameId, ?int $userId = null): Builder
    {
        $game = new Game;
        $user = new User;
        $gameUser = new GameUser;
        $gameQuestion = new GameQuestion;

        $query = DB::table($game->getTable())
            ->addSelect(
                (new User)->getQualifiedKeyName().' as user_id',
                (new User)->qualifyColumn('name'),
                DB::raw('COUNT(game_questions.id) as total_answered'),
                DB::raw('SUM(CASE WHEN game_questions.is_correct THEN 1 ELSE 0 END) as score'),
                DB::raw('SUM(CASE WHEN NOT game_questions.is_correct THEN 1 ELSE 0 END) as incorrect_answers'),
                DB::raw('ROUND(IFNULL(SUM(CASE WHEN game_questions.is_correct THEN 1 ELSE 0 END) * 100.0 / COUNT(game_questions.id), 0),2) as correct_percentage'),
            )
            // Join Users Table
            ->join($gameUser->getTable(), $game->getQualifiedKeyName(), '=', $gameUser->qualifyColumn('game_id'))
            ->join($user->getTable(), $user->qualifyColumn('id'), '=', $gameUser->qualifyColumn('user_id'))
            // Join Game Questions for answer breakdown
            ->leftJoin(
                $gameQuestion->getTable(),
                function ($join) use ($gameQuestion, $user) {
                    $join->on($gameQuestion->qualifyColumn('answered_by_id'), '=', $user->getQualifiedKeyName());
                }
            )
            ->where($game->getQualifiedKeyName(), $gameId)
            // Group by User for Aggregation
            ->groupBy($user->qualifyColumn('id'), $user->qualifyColumn('name'))
            ->orderByDesc('score')
            ->orderByDesc('correct_percentage');

        if ($userId) {
            $query->where((new User)->getQualifiedKeyName(), $userId);
        }

        return $query;
    }

    /** Get Game questions (add answer if its specific to one user) */
    public function getGameQuestionsWithAnswers(?int $userId = null, ?bool $showCorrectAnswers = null): Collection
    {
        $showCorrectAnswers ??= (bool) $userId;

        return $this->gameQuestions()
            ->with(['question'])
            ->when(
                value : $userId,
                callback : fn ($query) => $query->where('answered_by_id', $userId)
            )
            ->get()
            ->map(fn ($gameQuestion) => [
                'question' => $gameQuestion->question->question,
                'submitted_answer' => $gameQuestion->answer,
                ...($userId && $showCorrectAnswers ? [
                    'correct_answer' => $gameQuestion->question->correct_answer,
                    'is_correct' => $gameQuestion->is_correct,
                ] : []),
            ]);
    }

    // ========== Relationships

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
