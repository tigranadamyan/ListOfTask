<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * RESTful controller for Task CRUD operations.
 *
 * Thin controller — delegates all business logic to TaskService,
 * only handles HTTP concerns (request parsing, response formatting).
 */
final class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
    ) {}

    /**
     * List all tasks for the authenticated user.
     *
     * Supports filtering by status, full-text search, sorting,
     * and pagination via query parameters.
     */
    public function index(Request $request): TaskCollection
    {
        $tasks = $this->taskService->list($request->user(), [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort' => $request->query('sort'),
            'direction' => $request->query('direction'),
            'per_page' => $request->query('per_page'),
        ]);

        return new TaskCollection($tasks);
    }

    /**
     * Create a new task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create($request->user(), $request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show a single task.
     */
    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load('user'));
    }

    /**
     * Update a task.
     */
    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $updated = $this->taskService->update($task, $request->validated());

        return new TaskResource($updated);
    }

    /**
     * Change only the task status.
     */
    public function updateStatus(Request $request, Task $task): TaskResource
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,in_progress,completed'],
        ]);

        $updated = $this->taskService->updateStatus($task, $request->status);

        return new TaskResource($updated);
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->delete($task);

        return response()->json([
            'data' => null,
            'message' => 'Task deleted successfully.',
        ]);
    }
}
