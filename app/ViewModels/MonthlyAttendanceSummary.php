<?php

namespace App\ViewModels;

use App\Models\Attendance;
use Illuminate\Support\Collection;

final readonly class MonthlyAttendanceSummary
{
    private function __construct(
        public int $workingDays,
        public string $totalWorkedTime,
        public string $totalBreakTime,
        public string $averageWorkedTime,
    ) {}

    /**
     * @param  Collection<int, Attendance>  $attendances
     */
    public static function from(Collection $attendances): self
    {
        $workingDays = $attendances
            ->filter(fn (Attendance $attendance): bool => $attendance->clock_in !== null)
            ->count();

        $totalWorkedMinutes = $attendances
            ->sum(fn (Attendance $attendance): int => $attendance->workedMinutes());

        $totalBreakMinutes = $attendances
            ->sum(fn (Attendance $attendance): int => $attendance->totalBreakMinutes());

        $averageWorkedMinutes = $workingDays === 0
            ? 0
            : intdiv($totalWorkedMinutes, $workingDays);

        return new self(
            workingDays: $workingDays,
            totalWorkedTime: self::formatMinutes($totalWorkedMinutes),
            totalBreakTime: self::formatMinutes($totalBreakMinutes),
            averageWorkedTime: self::formatMinutes($averageWorkedMinutes),
        );
    }

    private static function formatMinutes(int $minutes): string
    {
        return intdiv($minutes, 60).'時間'.($minutes % 60).'分';
    }
}
