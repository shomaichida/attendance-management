<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_does_not_see_admin_navigation_link(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('管理画面');
    }

    public function test_admin_sees_admin_navigation_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('管理画面')
            ->assertSee(route('admin.dashboard'), false);
    }

    public function test_admin_dashboard_has_employee_dashboard_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('社員画面へ戻る')
            ->assertSee(route('dashboard'), false);
    }
}
