<?php

namespace App\Actions\Admin;

use App\Models\AttendanceCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ApproveAttendanceCorrection
{
    public function execute(AttendanceCorrectionRequest $correctionRequest, User $admin): void
    {
        DB::transaction(function () use ($correctionRequest, $admin): void {
            $correctionRequest = AttendanceCorrectionRequest::query()
                ->lockForUpdate()
                ->findOrFail($correctionRequest->id);

            if (! $correctionRequest->isPending()) {
                throw new ConflictHttpException('この申請はすでに承認されています。');
            }

            $attendance = $correctionRequest->attendance()
                ->lockForUpdate()
                ->firstOrFail();

            $attendance->update([
                'clock_in' => $correctionRequest->requested_clock_in,
                'clock_out' => $correctionRequest->requested_clock_out,
            ]);

            $attendance->breaks()->delete();

            foreach ($correctionRequest->requested_breaks ?? [] as $break) {
                $breakStart = $this->dateTime($attendance->work_date->format('Y-m-d'), $break['break_start'] ?? null);
                $breakEnd = $this->dateTime($attendance->work_date->format('Y-m-d'), $break['break_end'] ?? null);

                if ($breakStart === null) {
                    continue;
                }

                $attendance->breaks()->create([
                    'break_start' => $breakStart,
                    'break_end' => $breakEnd,
                    'break_minutes' => $breakEnd === null ? 0 : $breakStart->diffInMinutes($breakEnd),
                ]);
            }

            $correctionRequest->update([
                'status' => AttendanceCorrectionRequest::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);
        });
    }

    private function dateTime(string $workDate, mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        $value = (string) $value;

        return preg_match('/^\d{2}:\d{2}/', $value)
            ? Carbon::parse($workDate.' '.$value)
            : Carbon::parse($value);
    }
}
