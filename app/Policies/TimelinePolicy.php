<?php

namespace App\Policies;

use App\Models\Timeline;
use App\Models\User;

class TimelinePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Timeline $timeline): bool
    {
        if ($timeline->type === 'global' || $user->isBph()) {
            return true;
        }

        return $user->department_id === $this->departmentId($timeline);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['bph', 'kabinet']);
    }

    public function update(User $user, Timeline $timeline): bool
    {
        return $user->isBph()
            || ($user->isKabinet()
                && $timeline->type !== 'global'
                && $user->department_id === $this->departmentId($timeline));
    }

    public function delete(User $user, Timeline $timeline): bool
    {
        return $this->update($user, $timeline);
    }

    public function manageContext(User $user, string $type, ?int $departmentId): bool
    {
        return $user->isBph()
            || ($user->isKabinet()
                && $type !== 'global'
                && $departmentId !== null
                && $user->department_id === $departmentId);
    }

    private function departmentId(Timeline $timeline): ?int
    {
        $timeline->loadMissing('program');

        return $timeline->program?->department_id ?? $timeline->department_id;
    }
}
