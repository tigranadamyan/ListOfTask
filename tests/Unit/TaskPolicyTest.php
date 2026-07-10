<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TaskPolicy $policy;
    private User $owner;
    private User $otherUser;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new TaskPolicy;
        $this->owner = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->task = Task::factory()->for($this->owner)->create();
    }

    #[Test]
    public function owner_can_view_their_task(): void
    {
        $this->assertTrue(
            $this->policy->view($this->owner, $this->task),
        );
    }

    #[Test]
    public function non_owner_cannot_view_task(): void
    {
        $this->assertFalse(
            $this->policy->view($this->otherUser, $this->task),
        );
    }

    #[Test]
    public function owner_can_update_their_task(): void
    {
        $this->assertTrue(
            $this->policy->update($this->owner, $this->task),
        );
    }

    #[Test]
    public function non_owner_cannot_update_task(): void
    {
        $this->assertFalse(
            $this->policy->update($this->otherUser, $this->task),
        );
    }

    #[Test]
    public function owner_can_delete_their_task(): void
    {
        $this->assertTrue(
            $this->policy->delete($this->owner, $this->task),
        );
    }

    #[Test]
    public function non_owner_cannot_delete_task(): void
    {
        $this->assertFalse(
            $this->policy->delete($this->otherUser, $this->task),
        );
    }

    // ─── Admin role ───────────────────────────────────────────

    #[Test]
    public function admin_can_view_any_task(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue(
            $this->policy->view($admin, $this->task),
        );
    }

    #[Test]
    public function admin_cannot_update_others_task(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertFalse(
            $this->policy->update($admin, $this->task),
        );
    }

    #[Test]
    public function admin_cannot_delete_others_task(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertFalse(
            $this->policy->delete($admin, $this->task),
        );
    }
}
