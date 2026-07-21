<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_requests_are_shown_by_default_in_newest_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $older = $this->createCorrectionRequest($attendance, $employee, ['reason' => '古い申請']);
        $newer = $this->createCorrectionRequest($attendance, $employee, ['reason' => '新しい申請']);
        $older->forceFill(['created_at' => '2026-07-20 09:00:00'])->save();
        $newer->forceFill(['created_at' => '2026-07-20 10:00:00'])->save();
        $this->createCorrectionRequest($attendance, $employee, [
            'status' => AttendanceCorrectionRequest::STATUS_APPROVED,
            'reason' => '承認済み申請',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.correction-requests.index'))
            ->assertOk()
            ->assertSeeInOrder(['新しい申請', '古い申請'])
            ->assertDontSee('承認済み申請');
    }

    public function test_approved_filter_only_shows_approved_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $this->createCorrectionRequest($attendance, $employee, ['reason' => '承認待ち申請']);
        $this->createCorrectionRequest($attendance, $employee, [
            'status' => AttendanceCorrectionRequest::STATUS_APPROVED,
            'reason' => '承認済み申請',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.correction-requests.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('承認済み申請')
            ->assertDontSee('承認待ち申請');
    }

    public function test_employee_cannot_access_admin_correction_requests(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.correction-requests.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_request_details_with_multiple_breaks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['name' => '申請社員']);
        $attendance = $this->createAttendance($employee);
        $correctionRequest = $this->createCorrectionRequest($attendance, $employee);

        $this->actingAs($admin)
            ->get(route('admin.correction-requests.show', $correctionRequest))
            ->assertOk()
            ->assertSee('申請社員')
            ->assertSee('入力時刻を間違えたため')
            ->assertSee('12:00 ～ 12:30')
            ->assertSee('15:00 ～ 15:20');
    }

    public function test_admin_can_approve_request_and_apply_all_changes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $attendance->breaks()->create([
            'break_start' => '2026-07-10 12:00:00',
            'break_end' => '2026-07-10 13:00:00',
            'break_minutes' => 60,
        ]);
        $correctionRequest = $this->createCorrectionRequest($attendance, $employee);

        $this->actingAs($admin)
            ->post(route('admin.correction-requests.approve', $correctionRequest))
            ->assertRedirect(route('admin.correction-requests.show', $correctionRequest));

        $attendance->refresh();
        $correctionRequest->refresh();
        $breaks = $attendance->breaks()->orderBy('break_start')->get();

        $this->assertSame('08:30', $attendance->clock_in->format('H:i'));
        $this->assertSame('17:30', $attendance->clock_out->format('H:i'));
        $this->assertCount(2, $breaks);
        $this->assertSame('12:00', $breaks[0]->break_start->format('H:i'));
        $this->assertSame(30, $breaks[0]->break_minutes);
        $this->assertSame('15:00', $breaks[1]->break_start->format('H:i'));
        $this->assertSame(20, $breaks[1]->break_minutes);
        $this->assertSame(AttendanceCorrectionRequest::STATUS_APPROVED, $correctionRequest->status);
        $this->assertSame($admin->id, $correctionRequest->approved_by);
        $this->assertNotNull($correctionRequest->approved_at);
    }

    public function test_approved_request_cannot_be_approved_twice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $correctionRequest = $this->createCorrectionRequest($attendance, $employee, [
            'status' => AttendanceCorrectionRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.correction-requests.approve', $correctionRequest))
            ->assertConflict();
    }

    public function test_missing_request_returns_not_found(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/correction-requests/999999')
            ->assertNotFound();
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

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createCorrectionRequest(
        Attendance $attendance,
        User $employee,
        array $overrides = [],
    ): AttendanceCorrectionRequest {
        return AttendanceCorrectionRequest::query()->create(array_merge([
            'attendance_id' => $attendance->id,
            'user_id' => $employee->id,
            'status' => AttendanceCorrectionRequest::STATUS_PENDING,
            'reason' => '入力時刻を間違えたため',
            'original_clock_in' => '2026-07-10 09:00:00',
            'requested_clock_in' => '2026-07-10 08:30:00',
            'original_clock_out' => '2026-07-10 18:00:00',
            'requested_clock_out' => '2026-07-10 17:30:00',
            'original_breaks' => [
                ['break_start' => '12:00', 'break_end' => '13:00'],
            ],
            'requested_breaks' => [
                ['break_start' => '12:00', 'break_end' => '12:30'],
                ['break_start' => '15:00', 'break_end' => '15:20'],
            ],
        ], $overrides));
    }
}
