<?php

namespace App\Enums;

/**
 * Represents the lifecycle states of a Task.
 *
 * Uses a native PHP 8.4 enum for type safety, automatic Eloquent casting,
 * and single source of truth for validation rules.
 */
enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    /**
     * Human-readable labels for UI display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает',
            self::InProgress => 'В работе',
            self::Completed => 'Завершена',
        };
    }

    /**
     * Returns all status values as a flat array for validation rules.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
