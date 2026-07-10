<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Business logic for Task CRUD operations.
 *
 * The Service layer is justified here because:
 * 1. Task operations involve authorization checks (Policy)
 * 2. Filtering/sorting logic is non-trivial and should be testable
 * 3. Keeps Controllers thin — they only handle HTTP concerns
 *
 * Alternative considered: putting logic directly in Controller.
 * Rejected because: Controllers would become fat, logic not testable in isolation,
 * and authorization checks would be scattered across methods.
 */
final class TaskService
{
    /**
     * List tasks for a user with filtering, sorting, and pagination.
     *
     * @param  array{status?: string, search?: string, sort?: string, direction?: string, per_page?: int}  $filters
     */
    public function list(User $user, array $filters): LengthAwarePaginator
    {
        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        // Admin sees all tasks; regular user sees only own.
        $query = Task::query()->with('user')
            ->byStatus($filters['status'] ?? null)
            ->search($filters['search'] ?? null);

        if (! $user->isAdmin()) {
            $query->ownedBy($user->id);
        }

        return $query
            ->when(
                isset($filters['sort']),
                fn ($q) => $this->applySorting($q, $filters['sort'], $filters['direction'] ?? 'asc'),
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new task for the user.
     */
    public function create(User $user, array $data): Task
    {
        return $user->tasks()->create($data);
    }

    /**
     * Update a task — only the owner.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Task $task, array $data): Task
    {
        Gate::authorize('update', $task);

        $task->update($data);

        return $task->fresh();
    }

    /**
     * Change only the status of a task.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateStatus(Task $task, string $status): Task
    {
        Gate::authorize('update', $task);

        $task->update(['status' => $status]);

        return $task->fresh();
    }

    /**
     * Delete a task — only the owner.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Task $task): void
    {
        Gate::authorize('delete', $task);

        $task->delete();
    }

    /**
     * Apply dynamic sorting based on allowed columns.
     */
    private function applySorting($query, string $sort, string $direction): void
    {
        $allowed = ['due_date', 'status', 'created_at', 'title'];

        if (in_array($sort, $allowed, true)) {
            $query->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');
        }
    }
}
