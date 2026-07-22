<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole(['admin', 'bph']) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        if ($task->is_global) {
            return true;
        }

        if ($user->isKabinet()) {
            return $user->department_id !== null
                && $user->department_id === $this->departmentId($task);
        }

        if (! $user->isStaff()) {
            return false;
        }

        if ($task->assigned_to === $user->id || $task->department_id === $user->department_id) {
            return true;
        }

        $task->loadMissing('program');

        return $task->program?->hasMemberOrPic($user->id) ?? false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->isKabinet()
            && $user->department_id !== null
            && $user->department_id === $this->departmentId($task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function updateStatus(User $user, Task $task): bool
    {
        return $this->update($user, $task)
            || ($user->isStaff() && $task->assigned_to === $user->id);
    }

    private function departmentId(Task $task): ?int
    {
        $task->loadMissing('program');

        return $task->program?->department_id ?? $task->department_id;
    }
}
