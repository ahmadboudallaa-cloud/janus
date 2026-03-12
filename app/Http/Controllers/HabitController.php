<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Habits\HabitDestroyRequest;
use App\Http\Requests\Habits\HabitIndexRequest;
use App\Http\Requests\Habits\HabitShowRequest;
use App\Http\Requests\Habits\HabitStoreRequest;
use App\Http\Requests\Habits\HabitUpdateRequest;
use App\Models\Habit;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class HabitController extends Controller
{
    use ApiResponse;

    public function index(HabitIndexRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(new \stdClass(), 'Unauthenticated.', 401);
        }

        $query = $user->habits();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        } else {
            $query->where('is_active', true);
        }

        $habits = $query->orderByDesc('id')->get();

        return $this->success([
            'habits' => $habits,
        ], 'Habits retrieved.');
    }

    public function store(HabitStoreRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(new \stdClass(), 'Unauthenticated.', 401);
        }

        $data = $request->validated();
        $data['user_id'] = $user->id;

        $habit = Habit::create($data);

        return $this->success([
            'habit' => $habit,
        ], 'Habit created.', 201);
    }

    public function show(HabitShowRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(new \stdClass(), 'Unauthenticated.', 401);
        }

        $habit = $user->habits()->find($id);

        if ($habit === null) {
            return $this->error(new \stdClass(), 'Habit not found.', 404);
        }

        return $this->success([
            'habit' => $habit,
        ], 'Habit retrieved.');
    }

    public function update(HabitUpdateRequest $request, int $id): JsonResponse
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
        $habit->fill($data);
        $habit->save();

        return $this->success([
            'habit' => $habit,
        ], 'Habit updated.');
    }

    public function destroy(HabitDestroyRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(new \stdClass(), 'Unauthenticated.', 401);
        }

        $habit = $user->habits()->find($id);

        if ($habit === null) {
            return $this->error(new \stdClass(), 'Habit not found.', 404);
        }

        $habit->delete();

        return $this->success(null, 'Habit deleted.');
    }
}
