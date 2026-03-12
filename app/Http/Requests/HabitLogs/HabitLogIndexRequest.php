<?php

declare(strict_types=1);

namespace App\Http\Requests\HabitLogs;

use App\Http\Requests\ApiRequest;

class HabitLogIndexRequest extends ApiRequest
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
