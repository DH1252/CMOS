<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\User;

class EvaluationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'bph', 'kabinet']);
    }

    public function view(User $user, Evaluation $evaluation): bool
    {
        return $this->viewUser($user, $evaluation->user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'bph', 'kabinet']);
    }

    public function update(User $user, Evaluation $evaluation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isBph()) {
            return $evaluation->evaluator_type === 'bph';
        }

        return $user->isKabinet()
            && $evaluation->evaluator_type === 'kabinet'
            && $user->department_id === $evaluation->user?->department_id;
    }

    public function delete(User $user, Evaluation $evaluation): bool
    {
        return $this->update($user, $evaluation);
    }

    public function evaluate(User $user, User $staff): bool
    {
        return $staff->isStaff() && $staff->status === 'active'
            && ($user->isAdmin() || $user->isBph()
                || ($user->isKabinet() && $user->department_id === $staff->department_id));
    }

    public function viewUser(User $user, User $staff): bool
    {
        return $staff->isStaff()
            && ($user->isAdmin() || $user->isBph()
                || ($user->isKabinet() && $user->department_id === $staff->department_id));
    }
}
