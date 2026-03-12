<?php

declare(strict_types=1);

namespace App\Http\Requests\Habits;

use App\Http\Requests\ApiRequest;

class HabitShowRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

       
                                   
       
    public function rules(): array
    {
        return [];
    }
}
