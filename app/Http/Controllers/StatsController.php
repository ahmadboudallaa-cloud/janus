<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Habits\HabitStatsRequest;
use App\Http\Requests\Stats\OverviewStatsRequest;
use App\Models\HabitLog;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class StatsController extends Controller
{
    use ApiResponse;

    public function habitStats(HabitStatsRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(new \stdClass(), 'Unauthenticated.', 401);
        }

        $habit = $user->habits()->find($id);

        if ($habit === null) {
            return $this->error(new \stdClass(), 'Habit not found.', 404);
        }

        $dates = $habit->logs()
            ->orderByDesc('completed_at')
            ->pluck('completed_at')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->all();

        $totalCompletions = count($dates);
        $currentStreak = $this->calculateCurrentStreak($dates);
        $longestStreak = $this->calculateLongestStreak($dates);

        $today = Carbon::today();
        $from = $today->copy()->subDays(29)->toDateString();
        $to = $today->toDateString();

        $last30Count = $habit->logs()
            ->whereBetween('completed_at', [$from, $to])
            ->count();

        $completionRate = $this->rate($last30Count, 30);

        return $this->success([
            'habit_id' => $habit->id,
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'total_completions' => $totalCompletions,
            'completion_rate' => $completionRate,
        ], 'Habit stats retrieved.');
    }

    public function overview(OverviewStatsRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(new \stdClass(), 'Unauthenticated.', 401);
        }

        $activeHabits = $user->habits()
            ->where('is_active', true)
            ->get(['id', 'title', 'frequency']);
        $activeCount = $activeHabits->count();

        $today = Carbon::today()->toDateString();
        $completedToday = HabitLog::query()
            ->whereHas('habit', function ($query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->where('completed_at', $today)
            ->distinct('habit_id')
            ->count('habit_id');

        $activeIds = $activeHabits->pluck('id')->all();
        $logsByHabit = [];

        if ($activeIds !== []) {
            $rows = HabitLog::query()
                ->whereIn('habit_id', $activeIds)
                ->orderBy('habit_id')
                ->orderByDesc('completed_at')
                ->get(['habit_id', 'completed_at']);

            foreach ($rows as $row) {
                $logsByHabit[$row->habit_id][] = Carbon::parse($row->completed_at)->toDateString();
            }
        }

        $longestActive = $this->findLongestActiveStreak($activeHabits, $logsByHabit);

        $from = Carbon::today()->subDays(6)->toDateString();
        $to = $today;

        $last7Count = HabitLog::query()
            ->whereHas('habit', function ($query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->where('is_active', true);
            })
            ->whereBetween('completed_at', [$from, $to])
            ->count();

        $globalRate = $activeCount > 0
            ? $this->rate($last7Count, $activeCount * 7)
            : 0.0;

        return $this->success([
            'total_active_habits' => $activeCount,
            'completed_today' => $completedToday,
            'longest_active_streak' => $longestActive,
            'completion_rate_7_days' => $globalRate,
        ], 'Overview stats retrieved.');
    }

    /**
     * @param array<int, string> $datesDesc
     */
    private function calculateCurrentStreak(array $datesDesc): int
    {
        if ($datesDesc === []) {
            return 0;
        }

        $streak = 1;
        $prev = Carbon::parse($datesDesc[0]);

        $count = count($datesDesc);
        for ($i = 1; $i < $count; $i++) {
            $current = Carbon::parse($datesDesc[$i]);
            $diff = $prev->diffInDays($current);

            if ($diff !== 1) {
                break;
            }

            $streak++;
            $prev = $current;
        }

        return $streak;
    }

    /**
     * @param array<int, string> $datesDesc
     */
    private function calculateLongestStreak(array $datesDesc): int
    {
        if ($datesDesc === []) {
            return 0;
        }

        $max = 1;
        $currentStreak = 1;
        $prev = Carbon::parse($datesDesc[0]);

        $count = count($datesDesc);
        for ($i = 1; $i < $count; $i++) {
            $current = Carbon::parse($datesDesc[$i]);
            $diff = $prev->diffInDays($current);

            if ($diff === 1) {
                $currentStreak++;
            } else {
                $currentStreak = 1;
            }

            if ($currentStreak > $max) {
                $max = $currentStreak;
            }

            $prev = $current;
        }

        return $max;
    }

    /**
     * @param iterable<\App\Models\Habit> $habits
     * @param array<int, array<int, string>> $logsByHabit
     * @return array<string, mixed>
     */
    private function findLongestActiveStreak(iterable $habits, array $logsByHabit): array
    {
        $best = [
            'habit' => null,
            'streak' => 0,
        ];

        foreach ($habits as $habit) {
            $dates = $logsByHabit[$habit->id] ?? [];

            $current = $this->calculateCurrentStreak($dates);

            if ($current > $best['streak']) {
                $best = [
                    'habit' => [
                        'id' => $habit->id,
                        'title' => $habit->title,
                        'frequency' => $habit->frequency,
                    ],
                    'streak' => $current,
                ];
            }
        }

        return $best;
    }

    private function rate(int $count, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(($count / $total) * 100, 2);
    }
}
