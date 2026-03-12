<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

abstract class ApiRequest extends FormRequest
{
    use ApiResponse;

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->error($validator->errors(), 'Validation error.', 422)
        );
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->error(new \stdClass(), 'Forbidden.', 403)
        );
    }
}
