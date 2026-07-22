<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Program $program): bool
    {
        if ($user->isBph()) {
            return true;
        }

        if ($user->isKabinet()) {
            return $user->department_id === $program->department_id;
        }

        return $user->isStaff() && $program->hasMemberOrPic($user->id);
    }

    public function create(User $user): bool
    {
        return $user->isBph() || ($user->isKabinet() && $user->department_id !== null);
    }

    public function update(User $user, Program $program): bool
    {
        return $user->isBph()
            || ($user->isKabinet() && $user->department_id === $program->department_id);
    }

    public function delete(User $user, Program $program): bool
    {
        return $this->update($user, $program);
    }
}
