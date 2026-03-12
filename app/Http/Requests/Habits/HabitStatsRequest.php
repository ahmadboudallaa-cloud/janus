<?php

declare(strict_types=1);

namespace App\Http\Requests\Habits;

use App\Http\Requests\ApiRequest;

class HabitStatsRequest extends ApiRequest
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
        return [];
    }
}
