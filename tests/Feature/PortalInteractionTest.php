<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_marks_only_the_selected_notification_read_before_redirecting(): void
    {
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $first = AppNotification::create(['user_id' => $student->id, 'title' => 'First', 'message' => 'Update', 'url' => '/student/grades']);
        $second = AppNotification::create(['user_id' => $student->id, 'title' => 'Second', 'message' => 'Update']);
        $this->actingAs($student)->post(route('notifications.read', $first))->assertRedirect('/student/grades');
        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNull($second->fresh()->read_at);
        $readAt = $first->fresh()->read_at;
        $this->travel(1)->minutes();
        $this->post(route('notifications.read', $first))->assertRedirect('/student/grades');
        $this->assertEquals($readAt, $first->fresh()->read_at);
    }

    public function test_another_users_notification_cannot_be_opened_or_marked_read(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create(['role' => 'student', 'active' => true]);
        $notification = AppNotification::create(['user_id' => $owner->id, 'title' => 'Private', 'message' => 'Update']);
        $this->actingAs($other)->post(route('notifications.read', $notification))->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_admin_grading_endpoint_is_removed_and_camera_is_available_to_all_roles(): void
    {
        foreach (['student', 'adviser', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role, 'active' => true]);
            $this->actingAs($user)->get('/profile')->assertOk()->assertSee('Open camera')->assertSee('Retake Photo')->assertSee('Use This Photo');
        }
        $this->post('/admin/submissions/1/grade', ['final_score' => 100, 'decision' => 'approved'])->assertNotFound();
        $this->get('/admin/dashboard')->assertOk()->assertDontSee('Repository Monitor')->assertDontSee('Repository • Chat • AI Rubric');
    }
}


