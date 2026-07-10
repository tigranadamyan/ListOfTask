<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Seed the tasks table with demo data.
     *
     * Creates two test users, each with 25 tasks across all statuses
     * to demonstrate filtering, sorting, and pagination.
     */
    public function run(): void
    {
        $users = User::factory(2)->create();

        foreach ($users as $user) {
            // Create a mix of tasks with different statuses and due dates
            Task::factory(10)->pending()->for($user)->create();
            Task::factory(10)->inProgress()->for($user)->create();
            Task::factory(5)->completed()->for($user)->create();
        }
    }
}
