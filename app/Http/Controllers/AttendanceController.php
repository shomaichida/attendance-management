<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            ->filter(fn(Attendance $attendance) => $attendance->clock_in !== null)
            ->count();

        $totalWorkedMinutes = $attendances
            ->sum(fn(Attendance $attendance) => $attendance->workedMinutes());

        $totalBreakMinutes = $attendances
            ->sum(fn(Attendance $attendance) => $attendance->totalBreakMinutes());

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

        $attendance->load('breaks');

        return view('attendances.show', compact('attendance'));
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
    public function update(Request $request, Attendance $attendance)
    {
        abort_if($attendance->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],
        ]);

        if (!empty($validated['clock_in'])) {
            $attendance->clock_in = Carbon::parse(
                $attendance->work_date->format('Y-m-d') . ' ' . $validated['clock_in']
            );
        }

        if (!empty($validated['clock_out'])) {
            $attendance->clock_out = Carbon::parse(
                $attendance->work_date->format('Y-m-d') . ' ' . $validated['clock_out']
            );
        }

        $attendance->save();

        return redirect()
            ->route('attendances.show', $attendance)
            ->with('success', '勤怠を更新しました。');
    }
}
