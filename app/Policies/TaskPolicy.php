<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

/**
 * Authorization policy for Task model.
 *
 * Viewing: admin can view any task; regular user can only view own.
 * Update/Delete: only the task owner (regardless of role).
 * This follows the principle of least privilege.
 */
final class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->isAdmin() || $user->id === $task->user_id;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
}