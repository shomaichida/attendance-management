<?php

namespace App\Http\Controllers;

use App\Actions\Admin\UpdateAttendance;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Services\Admin\MonthlyAttendanceCsvExporter;
use App\ViewModels\MonthlyAttendanceSummary;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 管理者向けの社員・勤怠表示、編集、CSV出力を管理するController。
 */
class AdminController extends Controller
{
    /**
     * 当日の勤務状況を集計して管理者ダッシュボードを表示する。
     */
    public function index(): View
    {
        $todayAttendances = Attendance::query()
            ->with('breaks')
            ->whereDate('work_date', today())
            ->get();

        $workingCount = $todayAttendances
            ->filter(fn (Attendance $attendance) => $attendance->isWorking())
            ->count();

        $onBreakCount = $todayAttendances
            ->filter(fn (Attendance $attendance) => $attendance->isOnBreak())
            ->count();

        $finishedCount = $todayAttendances
            ->filter(fn (Attendance $attendance) => $attendance->isFinished())
            ->count();

        $employeeCount = User::query()
            ->where('role', 'employee')
            ->where('is_active', true)
            ->count();

        return view('admin.dashboard', compact(
            'workingCount',
            'onBreakCount',
            'finishedCount',
            'employeeCount'
        ));
    }

    /**
     * 有効な一般社員と当日の勤怠状況を一覧表示する。
     */
    public function employees(): View
    {
        $employees = User::query()
            ->where('role', 'employee')
            ->where('is_active', true)
            ->with('todayAttendance.breaks')
            ->orderBy('employee_number')
            ->get();

        return view('admin.employees.index', compact('employees'));
    }

    /**
     * 指定社員の対象月の勤怠一覧と月次集計を表示する。
     */
    public function employeeShow(Request $request, User $user): View
    {
        abort_unless($user->isEmployee(), 404);

        $targetMonth = $this->targetMonth($request);

        $attendances = $user->attendances()
            ->with('breaks')
            ->whereYear('work_date', $targetMonth->year)
            ->whereMonth('work_date', $targetMonth->month)
            ->orderBy('work_date')
            ->get();

        $previousMonth = $targetMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $targetMonth->copy()->addMonth()->format('Y-m');
        $monthlySummary = MonthlyAttendanceSummary::from($attendances);

        return view('admin.employees.show', compact(
            'user',
            'attendances',
            'targetMonth',
            'previousMonth',
            'nextMonth',
            'monthlySummary',
        ));
    }

    /**
     * 指定社員の対象月の勤怠をCSV形式で出力する。
     */
    public function attendanceExport(
        Request $request,
        User $user,
        MonthlyAttendanceCsvExporter $exporter,
    ): StreamedResponse {
        abort_unless($user->isEmployee(), 404);

        return $exporter->download($user, $this->targetMonth($request));
    }

    /**
     * 指定社員が所有する勤怠の詳細を表示する。
     */
    public function attendanceShow(User $user, Attendance $attendance): View
    {
        abort_unless($user->isEmployee(), 404);

        $attendance->load('breaks');

        return view('admin.employees.attendances.show', compact('user', 'attendance'));
    }

    /**
     * 指定社員が所有する勤怠の編集画面を表示する。
     */
    public function attendanceEdit(User $user, Attendance $attendance): View
    {
        abort_unless($user->isEmployee(), 404);

        $attendance->load('breaks');
        $breakRows = old('breaks', $attendance->breaks->map(fn ($break): array => [
            'id' => $break->id,
            'break_start' => $break->break_start->format('H:i'),
            'break_end' => $break->break_end?->format('H:i'),
        ])->all());

        return view('admin.employees.attendances.edit', compact(
            'user',
            'attendance',
            'breakRows',
        ));
    }

    /**
     * 指定社員の勤怠本体と複数休憩を更新する。
     */
    public function attendanceUpdate(
        AdminAttendanceUpdateRequest $request,
        User $user,
        Attendance $attendance,
        UpdateAttendance $updateAttendance,
    ): RedirectResponse {
        abort_unless($user->isEmployee(), 404);

        $updateAttendance->execute($attendance, $request->validated());

        return redirect()
            ->route('admin.employees.attendances.show', [$user, $attendance])
            ->with('success', '勤怠を更新しました。');
    }

    private function targetMonth(Request $request): Carbon
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        return isset($validated['month'])
            ? Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()
            : now()->startOfMonth();
    }
}
