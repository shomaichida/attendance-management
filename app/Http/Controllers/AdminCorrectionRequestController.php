<?php

namespace App\Http\Controllers;

use App\Actions\Admin\ApproveAttendanceCorrection;
use App\Models\AttendanceCorrectionRequest;
use App\ViewModels\CorrectionBreakComparison;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * 管理者向けの勤怠修正申請一覧・詳細・承認を管理するController。
 */
class AdminCorrectionRequestController extends Controller
{
    /**
     * 全社員の修正申請を承認状態別に一覧表示する。
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in([
                AttendanceCorrectionRequest::STATUS_PENDING,
                AttendanceCorrectionRequest::STATUS_APPROVED,
            ])],
        ]);
        $status = $validated['status'] ?? AttendanceCorrectionRequest::STATUS_PENDING;

        $correctionRequests = AttendanceCorrectionRequest::query()
            ->with(['user', 'attendance'])
            ->where('status', $status)
            ->latest()
            ->get();

        return view('admin.correction-requests.index', compact('correctionRequests', 'status'));
    }

    /**
     * 修正前後の出退勤と複数休憩を比較して申請詳細を表示する。
     */
    public function show(AttendanceCorrectionRequest $correctionRequest): View
    {
        $correctionRequest->load(['user', 'attendance', 'approver']);
        $originalBreaks = CorrectionBreakComparison::rows($correctionRequest->original_breaks);
        $requestedBreaks = CorrectionBreakComparison::rows($correctionRequest->requested_breaks);

        return view('admin.correction-requests.show', compact(
            'correctionRequest',
            'originalBreaks',
            'requestedBreaks',
        ));
    }

    /**
     * 修正申請を承認し、申請内容を対象勤怠へ反映する。
     */
    public function approve(
        AttendanceCorrectionRequest $correctionRequest,
        ApproveAttendanceCorrection $approveAttendanceCorrection,
    ): RedirectResponse {
        $approveAttendanceCorrection->execute($correctionRequest, request()->user());

        return redirect()
            ->route('admin.correction-requests.show', $correctionRequest)
            ->with('success', '勤怠修正申請を承認しました。');
    }
}
