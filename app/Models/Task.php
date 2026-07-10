<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $due_date
 * @property TaskStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $user
 */
#[Fillable(['title', 'description', 'due_date', 'status'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * Default attributes — ensures status is never null in memory
     * even when omitted from create/update data.
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Each task belongs to exactly one user (owner).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: filter tasks by status.
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if ($status === null) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Scope: search tasks by title (partial match, case-insensitive).
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where('title', 'like', '%'.$search.'%');
    }

    /**
     * Scope: tasks belonging to a specific user.
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Attribute casting — native PHP enum for status, Carbon for dates.
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'due_date' => 'date',
        ];
    }
}
