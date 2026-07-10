<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    // ─── Create ───────────────────────────────────────────────

    #[Test]
    public function authenticated_user_can_create_a_task(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/tasks', [
                'title' => 'My New Task',
                'description' => 'A description',
                'due_date' => '2026-12-31',
                'status' => 'pending',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'My New Task')
            ->assertJsonPath('data.status.value', 'pending')
            ->assertJsonPath('data.due_date', '2026-12-31');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'title' => 'My New Task',
        ]);
    }

    #[Test]
    public function task_creation_validates_required_fields(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 422)
            ->assertJsonPath('error.details.title.0', 'The title field is required.');
    }

    #[Test]
    public function task_creation_validates_due_date_is_not_in_past(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/tasks', [
                'title' => 'Test',
                'due_date' => '2020-01-01',
            ]);

        $response->assertStatus(422);
    }

    // ─── Read ─────────────────────────────────────────────────

    #[Test]
    public function user_can_list_their_tasks(): void
    {
        Task::factory(5)->for($this->user)->create();
        Task::factory(3)->create(); // other user's tasks

        $response = $this->withToken($this->token)
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function user_can_filter_tasks_by_status(): void
    {
        Task::factory(2)->pending()->for($this->user)->create();
        Task::factory(3)->completed()->for($this->user)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/tasks?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function user_can_search_tasks_by_title(): void
    {
        Task::factory()->for($this->user)->create(['title' => 'Buy groceries']);
        Task::factory()->for($this->user)->create(['title' => 'Walk the dog']);
        Task::factory()->for($this->user)->create(['title' => 'Buy milk']);

        $response = $this->withToken($this->token)
            ->getJson('/api/tasks?search=Buy');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function user_can_see_a_single_task(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $task->id);
    }

    // ─── Update ───────────────────────────────────────────────

    #[Test]
    public function owner_can_update_their_task(): void
    {
        $task = Task::factory()->for($this->user)->create(['title' => 'Old title']);

        $response = $this->withToken($this->token)
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated title',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated title');
    }

    #[Test]
    public function owner_can_update_task_status(): void
    {
        $task = Task::factory()->pending()->for($this->user)->create();

        $response = $this->withToken($this->token)
            ->patchJson("/api/tasks/{$task->id}/status", [
                'status' => 'completed',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status.value', 'completed');
    }

    #[Test]
    public function status_update_validates_value(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $response = $this->withToken($this->token)
            ->patchJson("/api/tasks/{$task->id}/status", [
                'status' => 'invalid-status',
            ]);

        $response->assertStatus(422);
    }

    // ─── Delete ───────────────────────────────────────────────

    #[Test]
    public function owner_can_delete_their_task(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $response = $this->withToken($this->token)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Task deleted successfully.');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    // ─── Authorization ────────────────────────────────────────

    #[Test]
    public function user_cannot_view_another_users_task(): void
    {
        $otherTask = Task::factory()->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/tasks/{$otherTask->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function user_cannot_update_another_users_task(): void
    {
        $otherTask = Task::factory()->create();

        $response = $this->withToken($this->token)
            ->putJson("/api/tasks/{$otherTask->id}", [
                'title' => 'Hacked!',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function user_cannot_delete_another_users_task(): void
    {
        $otherTask = Task::factory()->create();

        $response = $this->withToken($this->token)
            ->deleteJson("/api/tasks/{$otherTask->id}");

        $response->assertStatus(403);
    }

    // ─── Admin role ───────────────────────────────────────────

    #[Test]
    public function admin_can_list_all_tasks(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $admin->createToken('admin')->plainTextToken;

        Task::factory(3)->for($admin)->create();
        Task::factory(2)->create();

        $response = $this->withToken($adminToken)
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function admin_can_view_any_task(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $admin->createToken('admin')->plainTextToken;

        $otherTask = Task::factory()->create();

        $response = $this->withToken($adminToken)
            ->getJson("/api/tasks/{$otherTask->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $otherTask->id);
    }

    #[Test]
    public function admin_cannot_update_others_task(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $admin->createToken('admin')->plainTextToken;

        $otherTask = Task::factory()->create();

        $response = $this->withToken($adminToken)
            ->putJson("/api/tasks/{$otherTask->id}", [
                'title' => 'Hacked!',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_cannot_delete_others_task(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $admin->createToken('admin')->plainTextToken;

        $otherTask = Task::factory()->create();

        $response = $this->withToken($adminToken)
            ->deleteJson("/api/tasks/{$otherTask->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('tasks', ['id' => $otherTask->id]);
    }

    // ─── 404 ──────────────────────────────────────────────────

    #[Test]
    public function accessing_non_existent_task_returns_404(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/tasks/99999');

        $response->assertStatus(404);
    }
}
