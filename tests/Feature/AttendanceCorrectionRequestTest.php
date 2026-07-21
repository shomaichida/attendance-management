<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_correction_without_updating_attendance(): void
    {
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $attendance->breaks()->create([
            'break_start' => '2026-07-10 12:00:00',
            'break_end' => '2026-07-10 13:00:00',
            'break_minutes' => 60,
        ]);

        $response = $this->actingAs($employee)
            ->post(route('attendance-correction-requests.store', $attendance), [
                'clock_in' => '08:30',
                'clock_out' => '17:30',
                'breaks' => [
                    ['break_start' => '12:00', 'break_end' => '12:30'],
                    ['break_start' => '15:00', 'break_end' => '15:20'],
                ],
                'reason' => '入力内容を修正したいため',
            ]);

        $correctionRequest = AttendanceCorrectionRequest::query()->firstOrFail();

        $response
            ->assertRedirect(route('correction-requests.show', $correctionRequest))
            ->assertSessionHas('success', '修正申請を送信しました');

        $attendance->refresh();

        $this->assertSame('09:00', $attendance->clock_in->format('H:i'));
        $this->assertSame('18:00', $attendance->clock_out->format('H:i'));
        $this->assertSame('09:00', $correctionRequest->original_clock_in->format('H:i'));
        $this->assertSame('08:30', $correctionRequest->requested_clock_in->format('H:i'));
        $this->assertSame(AttendanceCorrectionRequest::STATUS_PENDING, $correctionRequest->status);
        $this->assertSame([
            ['break_start' => '12:00', 'break_end' => '13:00'],
        ], $correctionRequest->original_breaks);
        $this->assertSame([
            ['break_start' => '12:00', 'break_end' => '12:30'],
            ['break_start' => '15:00', 'break_end' => '15:20'],
        ], $correctionRequest->requested_breaks);
    }

    public function test_correction_request_requires_reason_and_complete_breaks(): void
    {
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);

        $this->actingAs($employee)
            ->post(route('attendance-correction-requests.store', $attendance), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '12:00', 'break_end' => null],
                ],
                'reason' => '',
            ])
            ->assertSessionHasErrors(['breaks.0.break_end', 'reason']);

        $this->assertDatabaseCount('attendance_correction_requests', 0);
    }

    public function test_pending_request_cannot_be_submitted_twice_for_same_attendance(): void
    {
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $this->createCorrectionRequest($attendance, $employee, '既存の承認待ち申請');

        $this->actingAs($employee)
            ->from(route('attendances.show', $attendance))
            ->post(route('attendance-correction-requests.store', $attendance), [
                'clock_in' => '08:30',
                'clock_out' => '17:30',
                'breaks' => [],
                'reason' => '重複する申請',
            ])
            ->assertRedirect(route('attendances.show', $attendance))
            ->assertSessionHasErrors(['attendance']);

        $this->assertDatabaseCount('attendance_correction_requests', 1);
    }

    public function test_employee_cannot_submit_request_for_another_employee_attendance(): void
    {
        $employee = User::factory()->create();
        $otherEmployee = User::factory()->create();
        $attendance = $this->createAttendance($otherEmployee);

        $this->actingAs($employee)
            ->post(route('attendance-correction-requests.store', $attendance), [
                'reason' => '不正な申請',
            ])
            ->assertForbidden();
    }

    public function test_employee_cannot_view_another_employee_attendance(): void
    {
        $employee = User::factory()->create();
        $otherEmployee = User::factory()->create();
        $attendance = $this->createAttendance($otherEmployee);

        $this->actingAs($employee)
            ->get(route('attendances.show', $attendance))
            ->assertForbidden();
    }

    public function test_blank_additional_break_row_is_allowed(): void
    {
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);

        $this->actingAs($employee)
            ->post(route('attendance-correction-requests.store', $attendance), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '', 'break_end' => ''],
                ],
                'reason' => '出退勤のみ修正するため',
            ])
            ->assertRedirect();

        $this->assertSame([], AttendanceCorrectionRequest::query()->firstOrFail()->requested_breaks);
    }

    public function test_employee_history_only_displays_own_requests_for_selected_status(): void
    {
        $employee = User::factory()->create();
        $otherEmployee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $otherAttendance = $this->createAttendance($otherEmployee);
        $pendingRequest = $this->createCorrectionRequest($attendance, $employee, '自分の承認待ち');
        $approvedRequest = $this->createCorrectionRequest($attendance, $employee, '自分の承認済み', AttendanceCorrectionRequest::STATUS_APPROVED);
        $otherRequest = $this->createCorrectionRequest($otherAttendance, $otherEmployee, '他人の申請');

        $this->actingAs($employee)
            ->get(route('correction-requests.index'))
            ->assertOk()
            ->assertSee(route('correction-requests.show', $pendingRequest), false)
            ->assertDontSee(route('correction-requests.show', $approvedRequest), false)
            ->assertDontSee(route('correction-requests.show', $otherRequest), false);

        $this->actingAs($employee)
            ->get(route('correction-requests.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee(route('correction-requests.show', $approvedRequest), false)
            ->assertDontSee(route('correction-requests.show', $otherRequest), false);
    }

    public function test_employee_can_view_own_request_but_not_another_users_request(): void
    {
        $employee = User::factory()->create();
        $otherEmployee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $correctionRequest = $this->createCorrectionRequest($attendance, $employee, '自分の申請');

        $this->actingAs($employee)
            ->get(route('correction-requests.show', $correctionRequest))
            ->assertOk()
            ->assertSee('承認待ち')
            ->assertSee('自分の申請');

        $this->actingAs($otherEmployee)
            ->get(route('correction-requests.show', $correctionRequest))
            ->assertForbidden();
    }

    public function test_attendance_details_displays_latest_request_status(): void
    {
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $this->createCorrectionRequest(
            $attendance,
            $employee,
            '承認された申請',
            AttendanceCorrectionRequest::STATUS_APPROVED,
        );

        $this->actingAs($employee)
            ->get(route('attendances.show', $attendance))
            ->assertOk()
            ->assertSee('最新の修正申請：承認済み');
    }

    private function createAttendance(User $employee): Attendance
    {
        return Attendance::query()->create([
            'user_id' => $employee->id,
            'work_date' => '2026-07-10',
            'clock_in' => '2026-07-10 09:00:00',
            'clock_out' => '2026-07-10 18:00:00',
        ]);
    }

    private function createCorrectionRequest(
        Attendance $attendance,
        User $employee,
        string $reason,
        string $status = AttendanceCorrectionRequest::STATUS_PENDING,
    ): AttendanceCorrectionRequest {
        return AttendanceCorrectionRequest::query()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $employee->id,
            'status' => $status,
            'reason' => $reason,
            'original_clock_in' => $attendance->clock_in,
            'requested_clock_in' => $attendance->clock_in,
            'original_clock_out' => $attendance->clock_out,
            'requested_clock_out' => $attendance->clock_out,
            'original_breaks' => [],
            'requested_breaks' => [],
        ]);
    }
}
