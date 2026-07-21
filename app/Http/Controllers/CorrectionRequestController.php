<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use App\ViewModels\CorrectionBreakComparison;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * 一般ユーザー自身の勤怠修正申請履歴を管理するController。
 */
class CorrectionRequestController extends Controller
{
    /**
     * ログインユーザーの修正申請を承認状態別に一覧表示する。
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
            ->with('attendance')
            ->where('user_id', $request->user()->id)
            ->where('status', $status)
            ->latest()
            ->get();

        return view('correction-requests.index', compact('correctionRequests', 'status'));
    }

    /**
     * ログインユーザーが所有する修正申請の詳細を表示する。
     */
    public function show(Request $request, AttendanceCorrectionRequest $correctionRequest): View
    {
        abort_unless($correctionRequest->user_id === $request->user()->id, 403);

        $correctionRequest->load('attendance');
        $originalBreaks = CorrectionBreakComparison::rows($correctionRequest->original_breaks);
        $requestedBreaks = CorrectionBreakComparison::rows($correctionRequest->requested_breaks);

        return view('correction-requests.show', compact(
            'correctionRequest',
            'originalBreaks',
            'requestedBreaks',
        ));
    }
}
