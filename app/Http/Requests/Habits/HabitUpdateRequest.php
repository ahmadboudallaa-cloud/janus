<?php

declare(strict_types=1);

namespace App\Http\Requests\Habits;

use App\Http\Requests\ApiRequest;

class HabitUpdateRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

       
                                   
       
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
            'frequency' => ['sometimes', 'in:daily,weekly,monthly'],
            'target_days' => ['sometimes', 'integer', 'min:1'],
            'color' => ['sometimes', 'nullable', 'string', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
