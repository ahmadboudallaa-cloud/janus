<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HabitLogs\HabitLogDestroyRequest;
use App\Http\Requests\HabitLogs\HabitLogIndexRequest;
use App\Http\Requests\HabitLogs\HabitLogStoreRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class HabitLogController extends Controller
{
    use ApiResponse;

    public function index(HabitLogIndexRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(new \stdClass(), 'Unauthenticated.', 401);
        }

        $habit = $user->habits()->find($id);

        if ($habit === null) {
            return $this->error(new \stdClass(), 'Habit not found.', 404);
        }

        $logs = $habit->logs()
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();

        return $this->success([
            'logs' => $logs,
        ], 'Habit logs retrieved.');
    }

    public function store(HabitLogStoreRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(new \stdClass(), 'Unauthenticated.', 401);
        }

        $habit = $user->habits()->find($id);

        if ($habit === null) {
            return $this->error(new \stdClass(), 'Habit not found.', 404);
        }

        $data = $request->validated();
        $completedAt = $data['completed_at'] ?? Carbon::today()->toDateString();
        $completedAt = Carbon::parse($completedAt)->toDateString();

        $exists = $habit->logs()
            ->where('completed_at', $completedAt)
            ->exists();

        if ($exists) {
            return $this->error(
                ['completed_at' => ['Habit already logged for this date.']],
                'Habit already logged for this date.',
                422
            );
        }

        $log = $habit->logs()->create([
            'completed_at' => $completedAt,
            'note' => $data['note'] ?? null,
        ]);

        return $this->success([
            'log' => $log,
        ], 'Habit log created.', 201);
    }

    public function destroy(HabitLogDestroyRequest $request, int $id, int $logId): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(new \stdClass(), 'Unauthenticated.', 401);
        }

        $habit = $user->habits()->find($id);

        if ($habit === null) {
            return $this->error(new \stdClass(), 'Habit not found.', 404);
        }

        $log = $habit->logs()->whereKey($logId)->first();

        if ($log === null) {
            return $this->error(new \stdClass(), 'Log not found.', 404);
        }

        $log->delete();

        return $this->success(null, 'Habit log deleted.');
    }

}
