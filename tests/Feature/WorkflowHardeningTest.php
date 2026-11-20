<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ChatConversation;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.ai.auto_evaluate' => false]);
        $this->seed(\Database\Seeders\DemoSeeder::class);
    }

    public function test_adviser_creates_subject_and_classroom_without_admin_help(): void
    {
        $adviser = User::where('email', 'adviser@school.test')->firstOrFail();
        $response = $this->actingAs($adviser)->post(route('adviser.subjects.store'), [
            'name' => 'Test Project Laboratory', 'room_name' => 'Computer Laboratory', 'section' => '3B',
            'description' => 'Practical laboratory tasks', 'school_year' => '2026-2027',
            'semester' => '1st Semester',
        ])->assertSessionHasNoErrors();

        $subject = Subject::where('name', 'Test Project Laboratory')->firstOrFail();
        $this->assertSame($adviser->id, $subject->adviser_id);
        $room = Room::where('subject_id', $subject->id)->firstOrFail();
        $response->assertRedirect(route('adviser.rooms.show', $room));
        $this->assertSame('Computer Laboratory', $room->name);
        $this->assertNotEmpty($room->enrollment_key);
        $this->actingAs($adviser)->get(route('adviser.rooms.index'))->assertOk()->assertSee('Test Project Laboratory')->assertDontSee('Subject Code');
    }

    public function test_adviser_cannot_edit_another_advisers_subject(): void
    {
        $another = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $subject = Subject::firstOrFail();
        $this->actingAs($another)->put(route('adviser.subjects.update', $subject), [
            'name' => 'Unauthorized change',
            'school_year' => '2026-2027', 'semester' => '1st Semester',
        ])->assertForbidden();
    }

    public function test_student_cannot_see_unpublished_assignment_via_direct_url(): void
    {
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $activity = Activity::firstOrFail();
        $activity->update(['status' => 'draft']);
        $this->actingAs($student)->get(route('student.activities.show', $activity))->assertNotFound();
    }

    public function test_adviser_and_student_cannot_chat_after_enrollment_removed(): void
    {
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $room = $student->rooms()->with('subject')->firstOrFail();
        $adviser = $room->subject->adviser;
        $conversation = ChatConversation::create([
            'room_id' => $room->id, 'student_id' => $student->id,
            'adviser_id' => $adviser->id,
        ]);
        Enrollment::where('student_id', $student->id)->where('room_id', $room->id)->delete();
        $this->actingAs($student)->get(route('chat.show', $conversation))->assertForbidden();
        $this->actingAs($adviser)->get(route('chat.show', $conversation))->assertForbidden();
        $this->post(route('chat.send', $conversation), ['body' => 'Not allowed'])->assertForbidden();
    }

    public function test_admin_cannot_register_invalid_name_or_weak_password(): void
    {
        $admin = User::where('email', 'admin@school.test')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Student123', 'email' => 'newstudent@example.test',
            'password' => 'password', 'role' => 'student',
        ])->assertSessionHasErrors(['name', 'password']);
        $this->assertDatabaseMissing('users', ['email' => 'newstudent@example.test']);
    }
}
