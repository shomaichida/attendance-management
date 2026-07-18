<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_view_employee_attendance(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);

        $this->actingAs($admin)
            ->get(route('admin.employees.attendances.show', [$employee, $attendance]))
            ->assertOk()
            ->assertSee($employee->name)
            ->assertSee('2026年07月10日');

        $this->actingAs($employee)
            ->get(route('admin.employees.attendances.show', [$employee, $attendance]))
            ->assertForbidden();
    }

    public function test_only_admin_can_edit_employee_attendance(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);

        $this->actingAs($admin)
            ->get(route('admin.employees.attendances.edit', [$employee, $attendance]))
            ->assertOk()
            ->assertSee('勤怠編集');

        $this->actingAs($employee)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]), [])
            ->assertForbidden();
    }

    public function test_attendance_owned_by_another_employee_cannot_be_edited(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $otherEmployee = User::factory()->create();
        $attendance = $this->createAttendance($otherEmployee);

        $this->actingAs($admin)
            ->get(route('admin.employees.attendances.edit', [$employee, $attendance]))
            ->assertNotFound();

        $this->actingAs($admin)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
            ])
            ->assertNotFound();
    }

    public function test_attendance_update_validation_rejects_invalid_times(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);

        $this->actingAs($admin)
            ->from(route('admin.employees.attendances.edit', [$employee, $attendance]))
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]), [
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'breaks' => [[
                    'break_start' => '13:00',
                    'break_end' => '12:00',
                ]],
                'memo' => str_repeat('a', 1001),
            ])
            ->assertSessionHasErrors(['clock_out', 'breaks.0.break_end', 'memo']);
    }

    public function test_admin_can_update_employee_attendance(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);

        $this->actingAs($admin)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]), [
                'clock_in' => '08:30',
                'clock_out' => '17:30',
                'breaks' => [[
                    'id' => null,
                    'break_start' => '12:00',
                    'break_end' => '12:45',
                ]],
                'memo' => '管理者が修正しました。',
            ])
            ->assertRedirect(route('admin.employees.attendances.show', [$employee, $attendance]));

        $attendance->refresh();
        $attendanceBreak = $attendance->breaks()->firstOrFail();

        $this->assertSame('08:30', $attendance->clock_in->format('H:i'));
        $this->assertSame('17:30', $attendance->clock_out->format('H:i'));
        $this->assertSame('管理者が修正しました。', $attendance->memo);
        $this->assertSame('12:00', $attendanceBreak->break_start->format('H:i'));
        $this->assertSame('12:45', $attendanceBreak->break_end->format('H:i'));
        $this->assertSame(45, $attendanceBreak->break_minutes);
    }

    public function test_admin_can_edit_multiple_breaks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $firstBreak = $attendance->breaks()->create($this->breakAttributes('12:00', '12:30'));
        $secondBreak = $attendance->breaks()->create($this->breakAttributes('15:00', '15:15'));

        $this->actingAs($admin)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]),
                $this->validPayload([
                    ['id' => $firstBreak->id, 'break_start' => '12:10', 'break_end' => '12:40'],
                    ['id' => $secondBreak->id, 'break_start' => '15:10', 'break_end' => '15:30'],
                ]))
            ->assertRedirect();

        $this->assertSame('12:10', $firstBreak->refresh()->break_start->format('H:i'));
        $this->assertSame('15:30', $secondBreak->refresh()->break_end->format('H:i'));
    }

    public function test_break_omitted_from_request_is_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $retainedBreak = $attendance->breaks()->create($this->breakAttributes('12:00', '12:30'));
        $deletedBreak = $attendance->breaks()->create($this->breakAttributes('15:00', '15:15'));

        $this->actingAs($admin)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]),
                $this->validPayload([
                    ['id' => $retainedBreak->id, 'break_start' => '12:00', 'break_end' => '12:30'],
                ]))
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_breaks', ['id' => $retainedBreak->id]);
        $this->assertDatabaseMissing('attendance_breaks', ['id' => $deletedBreak->id]);
    }

    public function test_new_break_can_be_added(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);

        $this->actingAs($admin)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]),
                $this->validPayload([
                    ['id' => null, 'break_start' => '12:00', 'break_end' => '12:50'],
                ]))
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_minutes' => 50,
        ]);
    }

    public function test_break_owned_by_another_attendance_cannot_be_updated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $otherAttendance = Attendance::query()->create([
            'user_id' => $employee->id,
            'work_date' => '2026-07-11',
            'clock_in' => '2026-07-11 09:00:00',
            'clock_out' => '2026-07-11 18:00:00',
        ]);
        $otherBreak = $otherAttendance->breaks()->create($this->breakAttributes('12:00', '12:30'));

        $this->actingAs($admin)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]),
                $this->validPayload([
                    ['id' => $otherBreak->id, 'break_start' => '13:00', 'break_end' => '13:30'],
                ]))
            ->assertSessionHasErrors(['breaks.0.id']);

        $this->assertSame('12:00', $otherBreak->refresh()->break_start->format('H:i'));
    }

    public function test_incomplete_break_is_rejected_and_input_is_preserved(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);
        $url = route('admin.employees.attendances.edit', [$employee, $attendance]);

        $this->actingAs($admin)
            ->from($url)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]),
                $this->validPayload([
                    ['id' => null, 'break_start' => '12:00', 'break_end' => null],
                    ['id' => null, 'break_start' => null, 'break_end' => '15:00'],
                ]))
            ->assertRedirect($url)
            ->assertSessionHasErrors(['breaks.0.break_end', 'breaks.1.break_start'])
            ->assertSessionHasInput('breaks.0.break_start', '12:00');
    }

    public function test_break_ending_before_it_starts_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);

        $this->actingAs($admin)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]),
                $this->validPayload([
                    ['id' => null, 'break_start' => '13:00', 'break_end' => '12:00'],
                ]))
            ->assertSessionHasErrors(['breaks.0.break_end']);
    }

    public function test_break_outside_working_hours_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);

        $this->actingAs($admin)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]),
                $this->validPayload([
                    ['id' => null, 'break_start' => '08:30', 'break_end' => '09:00'],
                    ['id' => null, 'break_start' => '18:00', 'break_end' => '18:30'],
                ]))
            ->assertSessionHasErrors(['breaks.0.break_start', 'breaks.1.break_end']);
    }

    public function test_overlapping_breaks_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $attendance = $this->createAttendance($employee);

        $this->actingAs($admin)
            ->put(route('admin.employees.attendances.update', [$employee, $attendance]),
                $this->validPayload([
                    ['id' => null, 'break_start' => '12:00', 'break_end' => '13:00'],
                    ['id' => null, 'break_start' => '12:30', 'break_end' => '13:30'],
                ]))
            ->assertSessionHasErrors(['breaks.1.break_start']);
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
     * @param  array<int, array<string, mixed>>  $breaks
     * @return array<string, mixed>
     */
    private function validPayload(array $breaks): array
    {
        return [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => $breaks,
            'memo' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function breakAttributes(string $start, string $end): array
    {
        return [
            'break_start' => "2026-07-10 $start:00",
            'break_end' => "2026-07-10 $end:00",
            'break_minutes' => 30,
        ];
    }
}
