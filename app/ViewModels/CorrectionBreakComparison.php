<?php

namespace App\ViewModels;

use Carbon\Carbon;

final class CorrectionBreakComparison
{
    /**
     * @param  array<int, array<string, mixed>>|null  $breaks
     * @return array<int, array<string, string>>
     */
    public static function rows(?array $breaks): array
    {
        return collect($breaks ?? [])->map(function (array $break): array {
            $start = self::time($break['break_start'] ?? null);
            $end = self::time($break['break_end'] ?? null);
            $minutes = ($start && $end) ? $start->diffInMinutes($end) : 0;

            return [
                'start' => $start?->format('H:i') ?? '-',
                'end' => $end?->format('H:i') ?? '-',
                'duration' => intdiv($minutes, 60).'時間'.($minutes % 60).'分',
            ];
        })->all();
    }

    private static function time(mixed $value): ?Carbon
    {
        return blank($value) ? null : Carbon::parse($value);
    }
}
