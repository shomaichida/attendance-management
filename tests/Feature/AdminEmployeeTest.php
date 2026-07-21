<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_employee_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create([
            'name' => '詳細表示社員',
            'department' => '開発部',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.employees.show', $employee))
            ->assertOk()
            ->assertSee('詳細表示社員')
            ->assertSee($employee->employee_number)
            ->assertSee($employee->email)
            ->assertSee('開発部');
    }

    public function test_employee_cannot_access_employee_details(): void
    {
        $employee = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.employees.show', $target))
            ->assertForbidden();
    }

    public function test_admin_user_cannot_be_viewed_as_an_employee(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.employees.show', $otherAdmin))
            ->assertNotFound();
    }

    public function test_only_attendances_for_the_target_month_are_displayed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();

        $this->createAttendance($employee, '2026-06-10');
        $this->createAttendance($employee, '2026-07-10');

        $this->actingAs($admin)
            ->get(route('admin.employees.show', [
                'user' => $employee,
                'month' => '2026-07',
            ]))
            ->assertOk()
            ->assertSee('2026/07/10')
            ->assertDontSee('2026/06/10');
    }

    public function test_month_query_switches_the_displayed_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();

        $this->createAttendance($employee, '2026-05-15');

        $this->actingAs($admin)
            ->get(route('admin.employees.show', [
                'user' => $employee,
                'month' => '2026-05',
            ]))
            ->assertOk()
            ->assertSee('2026年5月の勤怠')
            ->assertSee('2026/05/15');
    }

    public function test_employee_list_has_correct_detail_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.employees.index'))
            ->assertOk()
            ->assertSee(route('admin.employees.show', $employee), false);
    }

    public function test_monthly_attendance_summary_is_displayed_for_target_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $firstAttendance = $this->createAttendance($employee, '2026-07-10');
        $secondAttendance = $this->createAttendance($employee, '2026-07-11', '08:30', '17:30');
        $otherMonthAttendance = $this->createAttendance($employee, '2026-06-10', '09:00', '20:00');

        $firstAttendance->breaks()->create([
            'break_start' => '2026-07-10 12:00:00',
            'break_end' => '2026-07-10 13:00:00',
            'break_minutes' => 60,
        ]);
        $secondAttendance->breaks()->create([
            'break_start' => '2026-07-11 12:00:00',
            'break_end' => '2026-07-11 12:30:00',
            'break_minutes' => 30,
        ]);
        $otherMonthAttendance->breaks()->create([
            'break_start' => '2026-06-10 12:00:00',
            'break_end' => '2026-06-10 13:00:00',
            'break_minutes' => 60,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.employees.show', [
                'user' => $employee,
                'month' => '2026-07',
            ]))
            ->assertOk()
            ->assertSee('2日')
            ->assertSee('16時間30分')
            ->assertSee('1時間30分')
            ->assertSee('8時間15分')
            ->assertDontSee('10時間0分');
    }

    public function test_monthly_summary_displays_zero_when_there_are_no_attendances(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.employees.show', [
                'user' => $employee,
                'month' => '2026-07',
            ]))
            ->assertOk()
            ->assertSee('0日')
            ->assertSee('0時間0分');
    }

    public function test_month_query_switches_monthly_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $this->createAttendance($employee, '2026-05-10', '09:00', '17:00');
        $this->createAttendance($employee, '2026-06-10', '09:00', '19:00');

        $this->actingAs($admin)
            ->get(route('admin.employees.show', [
                'user' => $employee,
                'month' => '2026-05',
            ]))
            ->assertOk()
            ->assertSee('2026年5月の勤怠')
            ->assertSee('1日')
            ->assertSee('8時間0分')
            ->assertDontSee('10時間0分');
    }

    public function test_admin_can_export_target_month_attendances_as_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['name' => 'CSV対象社員']);
        $attendance = $this->createAttendance($employee, '2026-07-10');
        $this->createAttendance($employee, '2026-06-10');
        $otherEmployee = User::factory()->create(['name' => 'CSV対象外社員']);
        $this->createAttendance($otherEmployee, '2026-07-11');

        $attendance->breaks()->create([
            'break_start' => '2026-07-10 12:00:00',
            'break_end' => '2026-07-10 12:30:00',
            'break_minutes' => 30,
        ]);
        $attendance->breaks()->create([
            'break_start' => '2026-07-10 15:00:00',
            'break_end' => '2026-07-10 15:15:00',
            'break_minutes' => 15,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.employees.attendances.export', [
                'user' => $employee,
                'month' => '2026-07',
            ]));

        $response->assertOk()
            ->assertDownload('attendance-2026-07.csv');

        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('日付,氏名,出勤時刻,退勤時刻,休憩時間,合計勤務時間', $csv);
        $this->assertStringContainsString('2026/07/10,CSV対象社員,09:00,18:00,0時間45分,8時間15分', $csv);
        $this->assertStringNotContainsString('2026/06/10', $csv);
        $this->assertStringNotContainsString('CSV対象外社員', $csv);
    }

    public function test_csv_export_handles_missing_time_and_break_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['name' => '未打刻社員']);
        Attendance::query()->create([
            'user_id' => $employee->id,
            'work_date' => '2026-07-12',
            'clock_in' => null,
            'clock_out' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.employees.attendances.export', [
                'user' => $employee,
                'month' => '2026-07',
            ]));

        $response->assertOk();
        $this->assertStringContainsString('2026/07/12,未打刻社員,,,0時間0分,0時間0分', $response->streamedContent());
    }

    public function test_employee_cannot_export_admin_attendance_csv(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.employees.attendances.export', [
                'user' => $employee,
                'month' => '2026-07',
            ]))
            ->assertForbidden();
    }

    public function test_employee_details_has_csv_link_for_displayed_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $exportUrl = route('admin.employees.attendances.export', [
            'user' => $employee,
            'month' => '2026-05',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.employees.show', [
                'user' => $employee,
                'month' => '2026-05',
            ]))
            ->assertOk()
            ->assertSee('CSV出力')
            ->assertSee($exportUrl, false);
    }

    private function createAttendance(
        User $employee,
        string $workDate,
        string $clockIn = '09:00',
        string $clockOut = '18:00',
    ): Attendance {
        return Attendance::query()->create([
            'user_id' => $employee->id,
            'work_date' => $workDate,
            'clock_in' => "$workDate $clockIn:00",
            'clock_out' => "$workDate $clockOut:00",
        ]);
    }
}
