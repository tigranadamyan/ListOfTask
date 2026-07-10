<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Creates a test user with known credentials, plus 2 additional
     * users with a total of 50 demo tasks across all statuses.
     */
    public function run(): void
    {
        // Тестовый аккаунт — администратор
        $testUser = User::factory()->create([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Обычный пользователь для тестирования прав
        User::factory()->create([
            'name' => 'Обычный пользователь',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        // Демо-задачи для тестового пользователя
        Task::factory(5)->pending()->for($testUser)->create();
        Task::factory(5)->inProgress()->for($testUser)->create();
        Task::factory(2)->completed()->for($testUser)->create();

        $this->call(TaskSeeder::class);
    }
}
