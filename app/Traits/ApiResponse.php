<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data ?? new \stdClass(),
            'message' => $message,
        ], $status);
    }

    public function error(mixed $errors = null, string $message = 'An error occurred.', int $status = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors' => $errors ?? new \stdClass(),
            'message' => $message,
        ], $status);
    }
}
