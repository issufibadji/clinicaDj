<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        if (! $user->can('users.edit')) {
            return false;
        }
        // só admin pode editar outros admins
        if ($model->hasRole('admin') && ! $user->hasRole('admin')) {
            return false;
        }
        return true;
    }

    public function delete(User $user, User $model): bool
    {
        if (! $user->can('users.delete')) {
            return false;
        }
        if ($user->id === $model->id) {
            return false;
        }
        if ($model->hasRole('admin') && ! $user->hasRole('admin')) {
            return false;
        }
        return true;
    }

    public function toggleStatus(User $user, User $model): bool
    {
        return $user->can('users.edit') && $user->id !== $model->id;
    }
}
