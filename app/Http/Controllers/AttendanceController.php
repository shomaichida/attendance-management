<?php

namespace App\Http\Controllers;

use App\Actions\SubmitAttendanceCorrection;
use App\Http\Requests\AttendanceCorrectionStoreRequest;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $targetMonth = request('month')
            ? Carbon::createFromFormat('Y-m', request('month'))->startOfMonth()
            : now()->startOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereYear('work_date', $targetMonth->year)
            ->whereMonth('work_date', $targetMonth->month)
            ->orderBy('work_date')
            ->get();

        $previousMonth = $targetMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $targetMonth->copy()->addMonth()->format('Y-m');

        $workingDays = $attendances
            ->filter(fn (Attendance $attendance) => $attendance->clock_in !== null)
            ->count();

        $totalWorkedMinutes = $attendances
            ->sum(fn (Attendance $attendance) => $attendance->workedMinutes());

        $totalBreakMinutes = $attendances
            ->sum(fn (Attendance $attendance) => $attendance->totalBreakMinutes());

        return view('attendances.index', compact(
            'attendances',
            'targetMonth',
            'previousMonth',
            'nextMonth',
            'workingDays',
            'totalWorkedMinutes',
            'totalBreakMinutes'
        ));
    }

    public function show(Attendance $attendance)
    {
        abort_if($attendance->user_id !== Auth::id(), 403);

        $attendance->load(['breaks', 'correctionRequests' => fn ($query) => $query->latest()]);
        $latestCorrectionRequest = $attendance->correctionRequests->first();
        $breakRows = old('breaks', $attendance->breaks->map(fn ($break): array => [
            'break_start' => $break->break_start->format('H:i'),
            'break_end' => $break->break_end?->format('H:i'),
        ])->all());

        return view('attendances.show', compact(
            'attendance',
            'latestCorrectionRequest',
            'breakRows',
        ));
    }

    public function clockIn()
    {
        $today = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'work_date' => $today,
            ],
            [
                'clock_in' => now(),
            ]
        );

        if (! $attendance->wasRecentlyCreated && $attendance->clock_in !== null) {
            return back()->with('error', '本日はすでに出勤済みです。');
        }

        if ($attendance->clock_in === null) {
            $attendance->update([
                'clock_in' => now(),
            ]);
        }

        return back()->with('success', '出勤を記録しました。');
    }

    public function clockOut()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();

        if (! $attendance) {
            return back()->with('error', '本日の出勤記録がありません。');
        }

        if ($attendance->clock_out !== null) {
            return back()->with('error', '本日はすでに退勤済みです。');
        }

        if ($attendance->breaks()->whereNull('break_end')->exists()) {
            return back()->with('error', '休憩を終了してから退勤してください。');
        }

        $attendance->update([
            'clock_out' => now(),
        ]);

        return back()->with('success', '退勤を記録しました。');
    }

    public function breakStart()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();

        if (! $attendance || ! $attendance->clock_in) {
            return back()->with('error', '出勤後に休憩を開始してください。');
        }

        if ($attendance->clock_out !== null) {
            return back()->with('error', '退勤後は休憩を開始できません。');
        }

        if ($attendance->breaks()->whereNull('break_end')->exists()) {
            return back()->with('error', '現在すでに休憩中です。');
        }

        $attendance->breaks()->create([
            'break_start' => now(),
        ]);

        return back()->with('success', '休憩を開始しました。');
    }

    public function breakEnd()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();

        if (! $attendance) {
            return back()->with('error', '本日の勤怠記録がありません。');
        }

        $activeBreak = $attendance->breaks()
            ->whereNull('break_end')
            ->latest('break_start')
            ->first();

        if (! $activeBreak) {
            return back()->with('error', '現在休憩中ではありません。');
        }

        $breakEnd = now();

        $activeBreak->update([
            'break_end' => $breakEnd,
            'break_minutes' => $activeBreak->break_start->diffInMinutes($breakEnd),
        ]);

        return back()->with('success', '休憩を終了しました。');
    }

    public function update(
        AttendanceCorrectionStoreRequest $request,
        Attendance $attendance,
        SubmitAttendanceCorrection $submitAttendanceCorrection,
    ) {
        $correctionRequest = $submitAttendanceCorrection->execute(
            $attendance,
            $request->user(),
            $request->validated(),
        );

        return redirect()
            ->route('correction-requests.show', $correctionRequest)
            ->with('success', '修正申請を送信しました');
    }
}
