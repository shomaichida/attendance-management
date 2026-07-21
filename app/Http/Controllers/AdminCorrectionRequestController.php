<?php

namespace App\Http\Controllers;

use App\Actions\Admin\ApproveAttendanceCorrection;
use App\Models\AttendanceCorrectionRequest;
use App\ViewModels\CorrectionBreakComparison;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCorrectionRequestController extends Controller
{
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
