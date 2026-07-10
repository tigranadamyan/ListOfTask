<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates task creation requests.
 *
 * title: required, max 255 chars
 * description: optional, string
 * due_date: optional, valid date, must be today or in the future
 * status: optional, must be a valid TaskStatus value
 */
final class StoreTaskRequest extends FormRequest
{
    /**
     * Anyone authenticated can create a task for themselves.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['nullable', 'string', Rule::enum(TaskStatus::class)],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
        ];
    }
}
