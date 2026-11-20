<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleListsTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_are_grouped_and_role_filters_preserve_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        User::factory()->create(['role' => 'adviser', 'active' => true]);
        $student = User::factory()->create(['name' => 'Matching Student', 'role' => 'student', 'active' => true]);
        $this->actingAs($admin)->get(route('admin.users'))->assertOk()->assertSee('Students')->assertSee('Advisers')->assertSee('Administrators');
        $response = $this->get(route('admin.users', ['role' => 'student', 'q' => 'Matching']))->assertOk();
        $this->assertSame([$student->id], $response->viewData('users')->pluck('id')->all());
        $response->assertSee('role=admin&amp;q=Matching', false);
    }

    public function test_audit_role_filter_and_pagination_do_not_mix_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        foreach (range(1, 41) as $index) AuditLog::create(['user_id' => $student->id, 'action' => 'student_event', 'created_at' => now()]);
        foreach ([$admin, $adviser] as $user) AuditLog::create(['user_id' => $user->id, 'action' => $user->role.'_event', 'created_at' => now()]);
        AuditLog::create(['action' => 'system_event', 'created_at' => now()]);
        $this->actingAs($admin);
        foreach (['admin' => $admin, 'adviser' => $adviser, 'student' => $student] as $role => $user) {
            $response = $this->get(route('admin.audit', ['role' => $role]))->assertOk();
            $this->assertSame([$user->id], $response->viewData('logs')->pluck('user_id')->unique()->values()->all());
        }
        $response = $this->get(route('admin.audit', ['role' => 'student', 'page' => 2]))->assertOk();
        $this->assertCount(1, $response->viewData('logs'));
        $this->assertSame($student->id, $response->viewData('logs')->first()->user_id);
        $response->assertSee('role=student', false);
        $response = $this->get(route('admin.audit', ['role' => 'system']))->assertOk();
        $this->assertSame(['system_event'], $response->viewData('logs')->pluck('action')->all());
        $this->get(route('admin.audit', ['role' => 'invalid']))->assertSessionHasErrors('role');
        $this->actingAs($student)->get(route('admin.audit'))->assertForbidden();
    }
}
