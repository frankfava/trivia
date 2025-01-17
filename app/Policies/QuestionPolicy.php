<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return false; // Hidden
    }

    public function view(User $user, Question $question)
    {
        return false; // Hidden
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Question $question)
    {
        return ! $question->games()->exists();
    }

    public function delete(User $user, Question $question)
    {
        return ! $question->games()->exists();
    }
}
