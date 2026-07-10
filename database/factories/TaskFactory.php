<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     */
    private const TITLES = [
        'Купить продукты',
        'Оплатить счета',
        'Позвонить маме',
        'Сделать домашку',
        'Прочитать книгу',
        'Записаться к врачу',
        'Убраться в квартире',
        'Полить цветы',
        'Написать отчёт',
        'Подготовить презентацию',
        'Встретиться с друзьями',
        'Пройти курс по TypeScript',
        'Обновить резюме',
        'Вынести мусор',
        'Забрать посылку',
        'Проверить почту',
        'Запланировать отпуск',
        'Починить кран',
        'Купить подарок',
        'Завершить проект',
        'Сходить в спортзал',
        'Изучить Laravel Sanctum',
        'Настроить Docker',
        'Написать тесты',
        'Провести код-ревью',
    ];

    private const DESCRIPTIONS = [
        'Нужно успеть до конца недели.',
        'Важно не забыть.',
        'Сроки поджимают, нужно поторопиться.',
        'Лучше начать заранее.',
        'Поставить напоминание в календарь.',
        'Подготовить все необходимые документы.',
        'Согласовать с командой.',
        'Проверить статус и сообщить руководителю.',
    ];

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->randomElement(self::TITLES),
            'description' => $this->faker->optional(0.7)->randomElement(self::DESCRIPTIONS),
            'due_date' => $this->faker->optional(0.6)->dateTimeBetween('now', '+30 days'),
            'status' => $this->faker->randomElement(TaskStatus::cases()),
        ];
    }

    /**
     * Indicate that the task is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TaskStatus::Pending,
        ]);
    }

    /**
     * Indicate that the task is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TaskStatus::InProgress,
        ]);
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TaskStatus::Completed,
        ]);
    }

    /**
     * Set a specific due date.
     */
    public function dueOn(Carbon|string $date): static
    {
        return $this->state(fn (array $attributes): array => [
            'due_date' => $date instanceof Carbon ? $date : Carbon::parse($date),
        ]);
    }

    /**
     * No due date set.
     */
    public function withoutDueDate(): static
    {
        return $this->state(fn (array $attributes): array => [
            'due_date' => null,
        ]);
    }
}
