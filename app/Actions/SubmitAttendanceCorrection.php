<?php

namespace App\Actions;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * 一般ユーザーの勤怠修正申請を登録するAction。
 */
class SubmitAttendanceCorrection
{
    /**
     * 勤怠をロックし、重複する承認待ち申請を防いで修正前後の内容を保存する。
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(Attendance $attendance, User $user, array $data): AttendanceCorrectionRequest
    {
        return DB::transaction(function () use ($attendance, $user, $data): AttendanceCorrectionRequest {
            $attendance = Attendance::query()
                ->lockForUpdate()
                ->findOrFail($attendance->id);

            $hasPendingRequest = $attendance->correctionRequests()
                ->where('status', AttendanceCorrectionRequest::STATUS_PENDING)
                ->exists();

            if ($hasPendingRequest) {
                throw ValidationException::withMessages([
                    'attendance' => 'この勤怠には承認待ちの修正申請があります。',
                ]);
            }

            $attendance->load('breaks');
            $workDate = $attendance->work_date->format('Y-m-d');

            return AttendanceCorrectionRequest::query()->create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'status' => AttendanceCorrectionRequest::STATUS_PENDING,
                'reason' => $data['reason'],
                'original_clock_in' => $attendance->clock_in,
                'requested_clock_in' => $this->dateTime($workDate, $data['clock_in'] ?? null),
                'original_clock_out' => $attendance->clock_out,
                'requested_clock_out' => $this->dateTime($workDate, $data['clock_out'] ?? null),
                'original_breaks' => $attendance->breaks->map(fn ($break): array => [
                    'break_start' => $break->break_start->format('H:i'),
                    'break_end' => $break->break_end?->format('H:i'),
                ])->all(),
                'requested_breaks' => collect($data['breaks'] ?? [])
                    ->filter(fn (array $break): bool => filled($break['break_start'] ?? null))
                    ->map(fn (array $break): array => [
                        'break_start' => $break['break_start'],
                        'break_end' => $break['break_end'],
                    ])->values()->all(),
            ]);
        });
    }

    private function dateTime(string $workDate, ?string $time): ?Carbon
    {
        return blank($time) ? null : Carbon::parse($workDate.' '.$time);
    }
}
