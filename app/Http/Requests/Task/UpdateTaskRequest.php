<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates task update requests.
 *
 * All fields are optional — only provided fields will be updated.
 * status cannot be set via this request; use the dedicated status endpoint.
 */
final class UpdateTaskRequest extends FormRequest
{
    /**
     * Authorization is handled by TaskPolicy via the controller.
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['sometimes', 'string', Rule::enum(TaskStatus::class)],
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
