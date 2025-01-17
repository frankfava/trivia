<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Category $category)
    {
        return true;
    }

    public function create(User $user)
    {
        return false; // Categories are created with questions
    }

    public function update(User $user, Category $category)
    {
        return false; // Categories cannot be updated
    }

    public function delete(User $user, Category $category)
    {
        return ! $category->questions()->exists();
    }
}
