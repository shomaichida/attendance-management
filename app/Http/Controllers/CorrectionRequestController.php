<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use App\ViewModels\CorrectionBreakComparison;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CorrectionRequestController extends Controller
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
            ->with('attendance')
            ->where('user_id', $request->user()->id)
            ->where('status', $status)
            ->latest()
            ->get();

        return view('correction-requests.index', compact('correctionRequests', 'status'));
    }

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
