<?php

declare(strict_types=1);

namespace App\Http\Requests\Habits;

use App\Http\Requests\ApiRequest;

class HabitStoreRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'target_days' => ['required', 'integer', 'min:1'],
            'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
