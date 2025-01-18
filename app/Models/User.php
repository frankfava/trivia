<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Scope to get users with active games */
    public function scopeActiveGames($query)
    {
        return $query->whereHas('games', function ($query) {
            $query->where('status', GameStatus::IN_PROGRESS);
        });
    }

    public function scopePlayersInGame($query, int|Game $gameId, bool $activeStatus = true)
    {
        $gameId = $gameId instanceof Game ? $gameId->id : $gameId;
        $query
            ->whereHas('games', function ($q) use ($gameId, $activeStatus) {
                $q
                    ->when($activeStatus, fn ($query) => $query->activeStatus())
                    ->where((new Game)->getQualifiedKeyName(), $gameId);
            });
    }

    /** Games this user is on (Pivot) */
    public function games()
    {
        return $this->belongsToMany(Game::class, (new GameUser)->getTable())
            ->using(GameUser::class);
    }

    /** Games this user created */
    public function createdGames()
    {
        return $this->hasMany(Game::class, 'created_by_id');
    }

    /** Questions this user answered */
    public function answeredQuestions()
    {
        return $this->hasMany(GameQuestion::class, 'answered_by_id');
    }
}
