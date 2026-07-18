<?php

namespace App\Actions\Admin;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateAttendance
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Attendance $attendance, array $data): void
    {
        DB::transaction(function () use ($attendance, $data): void {
            $workDate = $attendance->work_date->format('Y-m-d');

            $attendance->update([
                'clock_in' => $this->dateTime($workDate, $data['clock_in'] ?? null),
                'clock_out' => $this->dateTime($workDate, $data['clock_out'] ?? null),
                'memo' => $data['memo'] ?? null,
            ]);

            $this->syncBreaks($attendance, $workDate, $data['breaks'] ?? []);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $breakRows
     */
    private function syncBreaks(Attendance $attendance, string $workDate, array $breakRows): void
    {
        $existingBreaks = $attendance->breaks()
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
        $retainedIds = [];

        foreach ($breakRows as $index => $breakRow) {
            if (blank($breakRow['break_start'] ?? null) && blank($breakRow['break_end'] ?? null)) {
                continue;
            }

            $attendanceBreak = $this->resolveBreak($existingBreaks, $breakRow['id'] ?? null, $index);
            $breakStart = $this->dateTime($workDate, $breakRow['break_start']);
            $breakEnd = $this->dateTime($workDate, $breakRow['break_end']);

            $attendanceBreak ??= $attendance->breaks()->make();
            $attendanceBreak->fill([
                'break_start' => $breakStart,
                'break_end' => $breakEnd,
                'break_minutes' => $breakStart->diffInMinutes($breakEnd),
            ])->save();

            $retainedIds[] = $attendanceBreak->id;
        }

        $attendance->breaks()
            ->whereNotIn('id', $retainedIds)
            ->delete();
    }

    /**
     * @param  Collection<int, AttendanceBreak>  $existingBreaks
     */
    private function resolveBreak(Collection $existingBreaks, mixed $breakId, int $index): ?AttendanceBreak
    {
        if ($breakId === null || $breakId === '') {
            return null;
        }

        $attendanceBreak = $existingBreaks->get((int) $breakId);

        if ($attendanceBreak === null) {
            throw ValidationException::withMessages([
                "breaks.$index.id" => 'この勤怠に属していない休憩は更新できません。',
            ]);
        }

        return $attendanceBreak;
    }

    private function dateTime(string $workDate, ?string $time): ?Carbon
    {
        return $time === null ? null : Carbon::parse($workDate.' '.$time);
    }
}
