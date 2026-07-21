<?php

namespace App\Services\Admin;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthlyAttendanceCsvExporter
{
    public function download(User $user, Carbon $targetMonth): StreamedResponse
    {
        $attendances = $user->attendances()
            ->with('breaks')
            ->whereYear('work_date', $targetMonth->year)
            ->whereMonth('work_date', $targetMonth->month)
            ->orderBy('work_date')
            ->get();

        $filename = sprintf(
            'attendance-%s.csv',
            $targetMonth->format('Y-m'),
        );

        return response()->streamDownload(
            function () use ($attendances, $user): void {
                $stream = fopen('php://output', 'wb');

                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, ['日付', '氏名', '出勤時刻', '退勤時刻', '休憩時間', '合計勤務時間']);

                $attendances->each(function (Attendance $attendance) use ($stream, $user): void {
                    fputcsv($stream, [
                        $attendance->work_date->format('Y/m/d'),
                        $user->name,
                        $attendance->clock_in?->format('H:i') ?? '',
                        $attendance->clock_out?->format('H:i') ?? '',
                        $attendance->breakTime(),
                        $attendance->workedTime(),
                    ]);
                });

                fclose($stream);
            },
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
