<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolRepositoryFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.ai.auto_evaluate' => false]);
        $this->seed(\Database\Seeders\DemoSeeder::class);
    }

    public function test_demo_accounts_can_reach_their_own_dashboards(): void
    {
        foreach (['admin', 'adviser', 'student'] as $role) {
            $user = User::where('email', $role.'@school.test')->firstOrFail();
            $this->actingAs($user)->get('/'.$role.'/dashboard')->assertOk();
        }
    }

    public function test_adviser_can_create_activity_with_text_only_instructions_and_rubric(): void
    {
        $adviser = User::where('email', 'adviser@school.test')->firstOrFail();
        $room = \App\Models\Room::whereHas('subject', fn ($q) => $q->where('adviser_id', $adviser->id))->firstOrFail();

        $response = $this->actingAs($adviser)->post('/adviser/activities', [
            'room_id' => $room->id,
            'title' => 'Text Only Test Activity',
            'instructions' => 'Complete the requested Laravel task.',
            'rubric_text' => "Functionality - 60 points\nExplanation - 40 points",
            'max_score' => 100,
            'status' => 'published',
            'allow_text' => 1,
            'allow_file' => 1,
            'allow_late' => 1,
        ]);

        $activity = Activity::where('title', 'Text Only Test Activity')->firstOrFail();
        $response->assertRedirect(route('adviser.activities.show', $activity));
        $this->assertSame('Complete the requested Laravel task.', $activity->instructions);
    }

    public function test_adviser_can_create_activity_with_file_only_instructions_and_rubric(): void
    {
        Storage::fake('local');
        $adviser = User::where('email', 'adviser@school.test')->firstOrFail();
        $room = \App\Models\Room::whereHas('subject', fn ($q) => $q->where('adviser_id', $adviser->id))->firstOrFail();

        $response = $this->actingAs($adviser)->post('/adviser/activities', [
            'room_id' => $room->id,
            'title' => 'File Only Test Activity',
            'instructions' => '',
            'rubric_text' => '',
            'instruction_file' => UploadedFile::fake()->create('instructions.pdf', 100, 'application/pdf'),
            'rubric_file' => UploadedFile::fake()->create('rubric.pdf', 100, 'application/pdf'),
            'max_score' => 100,
            'status' => 'published',
            'allow_text' => 1,
            'allow_file' => 1,
            'allow_late' => 1,
        ]);

        $activity = Activity::where('title', 'File Only Test Activity')->firstOrFail();
        $response->assertRedirect(route('adviser.activities.show', $activity));
        $this->assertNull($activity->instructions);
        $this->assertNotNull($activity->instruction_file_path);
        $this->assertNotNull($activity->rubric_file_path);
    }

    public function test_student_can_submit_text_to_published_activity(): void
    {
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $activity = Activity::where('status', 'published')->firstOrFail();

        $response = $this->actingAs($student)->post('/student/activities/'.$activity->id.'/submit', [
            'text_content' => 'This is a test student answer that follows the activity requirements.',
        ]);

        $response->assertRedirect(route('student.activities.show', $activity));
        $this->assertDatabaseHas('submissions', [
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'version' => 1,
        ]);
    }

    public function test_admin_can_store_an_encrypted_groq_key(): void
    {
        $admin = User::where('email', 'admin@school.test')->firstOrFail();

        $this->actingAs($admin)->post('/admin/ai-settings', [
            'groq_api_key' => 'test-groq-key-placeholder',
            'groq_model' => 'openai/gpt-oss-20b',
        ])->assertSessionHasNoErrors();

        $this->assertSame('test-groq-key-placeholder', SystemSetting::getSecret('groq_api_key'));
        $this->assertSame('openai/gpt-oss-20b', SystemSetting::getValue('groq_model'));
    }



    public function test_role_navigation_pages_load_without_server_errors(): void
    {
        $matrix = [
            'admin@school.test' => [
                '/admin/dashboard', '/admin/users', '/admin/subjects', '/admin/activities',
                '/admin/submissions', '/admin/repository', '/admin/monitor', '/admin/ai-settings',
                '/admin/ai-monitor', '/admin/audit-logs', '/profile', '/notifications',
            ],
            'adviser@school.test' => [
                '/adviser/dashboard', '/adviser/rooms', '/adviser/activities', '/adviser/activities/create',
                '/adviser/scores', '/adviser/reports', '/chat', '/profile', '/notifications',
            ],
            'student@school.test' => [
                '/student/dashboard', '/student/rooms', '/student/activities', '/student/submissions',
                '/student/grades', '/chat', '/profile', '/notifications',
            ],
        ];

        foreach ($matrix as $email => $paths) {
            $user = User::where('email', $email)->firstOrFail();
            foreach ($paths as $path) {
                $this->actingAs($user)->get($path)->assertOk();
            }
        }
    }

    public function test_adviser_can_create_and_open_room_with_generated_key(): void
    {
        $adviser = User::where('email', 'adviser@school.test')->firstOrFail();
        $subject = Subject::where('adviser_id', $adviser->id)->firstOrFail();

        $response = $this->actingAs($adviser)->post('/adviser/rooms', [
            'subject_id' => $subject->id,
            'name' => 'Automated Room Test',
            'section' => 'BSIT TEST',
            'capacity' => 40,
        ]);

        $room = \App\Models\Room::where('name', 'Automated Room Test')->firstOrFail();
        $response->assertRedirect(route('adviser.rooms.show', $room));
        $this->assertNotEmpty($room->enrollment_key);
        $this->actingAs($adviser)->get('/adviser/rooms/'.$room->id)->assertOk();
    }

    public function test_student_can_join_generated_room_key_and_chat_with_adviser(): void
    {
        $adviser = User::where('email', 'adviser@school.test')->firstOrFail();
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $subject = Subject::where('adviser_id', $adviser->id)->firstOrFail();
        $room = \App\Models\Room::create([
            'subject_id' => $subject->id,
            'name' => 'Join and Chat Test Room',
            'section' => 'TEST',
            'enrollment_key' => 'TEST-ROOM-KEY',
            'capacity' => 40,
            'active' => true,
        ]);

        $this->actingAs($student)->post('/student/rooms/join', [
            'enrollment_key' => 'TEST-ROOM-KEY',
        ])->assertRedirect(route('student.rooms'));

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'room_id' => $room->id,
        ]);

        $open = $this->actingAs($student)->get('/chat/rooms/'.$room->id);
        $conversation = \App\Models\ChatConversation::where('room_id', $room->id)
            ->where('student_id', $student->id)
            ->where('adviser_id', $adviser->id)
            ->firstOrFail();
        $open->assertRedirect(route('chat.show', $conversation));

        $this->actingAs($student)->post('/chat/'.$conversation->id.'/messages', [
            'body' => 'Test message from student to adviser.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $student->id,
            'body' => 'Test message from student to adviser.',
        ]);
        $this->actingAs($adviser)->get('/chat/'.$conversation->id)->assertOk();
    }



    public function test_admin_can_manage_user_subject_and_enrollment(): void
    {
        $admin = User::where('email', 'admin@school.test')->firstOrFail();
        $adviser = User::where('email', 'adviser@school.test')->firstOrFail();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Role Test Student',
            'email' => 'role-test-student@school.test',
            'password' => 'Password123!',
            'role' => 'student',
            'school_id' => 'ROLE-TEST-001',
            'course' => 'BSIT',
            'year_level' => '3',
            'section' => 'TEST',
        ])->assertSessionHasNoErrors();

        $student = User::where('email', 'role-test-student@school.test')->firstOrFail();

        $this->actingAs($admin)->post('/admin/subjects', [
            'class_code' => 'TST100',
            'room_name' => 'Role Test Classroom',
            'name' => 'Role Test Subject',
            'adviser_id' => $adviser->id,
            'school_year' => '2026-2027',
            'semester' => '1st Semester',
            'section' => 'TEST',
        ])->assertSessionHasNoErrors();

        $subject = Subject::where('name', 'Role Test Subject')->firstOrFail();
        $room = \App\Models\Room::where('subject_id', $subject->id)->firstOrFail();

        $this->actingAs($admin)->post('/admin/subjects/'.$subject->id.'/enroll', [
            'student_id' => $student->id,
            'room_id' => $room->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('enrollments', [
            'subject_id' => $subject->id,
            'student_id' => $student->id,
            'room_id' => $room->id,
        ]);
    }

    public function test_adviser_can_regenerate_room_key_and_toggle_enrollment(): void
    {
        $adviser = User::where('email', 'adviser@school.test')->firstOrFail();
        $room = \App\Models\Room::whereHas('subject', fn ($q) => $q->where('adviser_id', $adviser->id))->firstOrFail();
        $oldKey = $room->enrollment_key;
        $oldActive = (bool) $room->active;

        $this->actingAs($adviser)->post('/adviser/rooms/'.$room->id.'/regenerate-key')
            ->assertSessionHasNoErrors();
        $room->refresh();
        $this->assertNotSame($oldKey, $room->enrollment_key);

        $this->actingAs($adviser)->post('/adviser/rooms/'.$room->id.'/toggle')
            ->assertSessionHasNoErrors();
        $room->refresh();
        $this->assertSame(!$oldActive, (bool) $room->active);
    }

    public function test_student_notifications_and_profile_page_work(): void
    {
        $student = User::where('email', 'student@school.test')->firstOrFail();
        \App\Models\AppNotification::create([
            'user_id' => $student->id,
            'title' => 'Smoke Test Notification',
            'message' => 'Notification route verification.',
        ]);
        $this->actingAs($student)->post('/notifications/read-all')->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('app_notifications', [
            'user_id' => $student->id,
            'title' => 'Smoke Test Notification',
            'read_at' => null,
        ]);

        $this->actingAs($student)->get('/profile')->assertOk();
        $this->actingAs($student)->post('/profile', ['remove_photo' => 1])->assertSessionHasNoErrors();
    }

    public function test_adviser_can_finalize_a_grade_from_a_real_ai_result(): void
    {
        $adviser = User::where('email', 'adviser@school.test')->firstOrFail();
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $activity = Activity::where('adviser_id', $adviser->id)->where('status', 'published')->firstOrFail();

        $submission = \App\Models\Submission::create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'version' => 1,
            'text_content' => 'Grade finalization smoke-test answer.',
            'status' => 'ai_reviewed',
            'is_late' => false,
            'submitted_at' => now(),
        ]);
        \App\Models\AiEvaluation::create([
            'submission_id' => $submission->id,
            'provider' => 'groq',
            'model' => 'openai/gpt-oss-20b',
            'recommended_score' => 75,
            'summary' => 'Smoke-test AI recommendation.',
            'status' => 'completed',
            'is_demo' => false,
            'evaluated_at' => now(),
        ]);

        $this->actingAs($adviser)->post('/adviser/submissions/'.$submission->id.'/grade', [
            'final_score' => 80,
            'decision' => 'modified',
            'adviser_comment' => 'Verified during automated role test.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'final_score' => 80,
            'decision' => 'modified',
        ]);
        $this->assertDatabaseHas('submissions', ['id' => $submission->id, 'status' => 'graded']);
    }


    public function test_invalid_login_is_rejected_and_valid_login_redirects_to_role_dashboard(): void
    {
        $this->post('/login', [
            'email' => 'student@school.test',
            'password' => 'wrong-password',
            'portal' => 'student',
        ])->assertSessionHasErrors('email');

        $this->post('/login', [
            'email' => 'student@school.test',
            'password' => 'password',
            'portal' => 'student',
        ])->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'student@school.test')->firstOrFail());
    }

    public function test_logout_invalidates_authenticated_session(): void
    {
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $this->actingAs($student)->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_every_role_can_upload_a_profile_photo(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZV7sAAAAASUVORK5CYII=');

        foreach (['admin@school.test','adviser@school.test','student@school.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $file = UploadedFile::fake()->createWithContent('profile.png', $png);
            $this->actingAs($user)->post('/profile', ['profile_photo' => $file])->assertSessionHasNoErrors();
            $user->refresh();
            $this->assertNotNull($user->profile_photo_path);
            $absolute = public_path($user->profile_photo_path);
            $this->assertFileExists($absolute);
            @unlink($absolute);
            $user->update(['profile_photo_path' => null]);
        }
    }

    public function test_admin_subject_directory_filters_are_operational(): void
    {
        $admin = User::where('email', 'admin@school.test')->firstOrFail();
        $subject = Subject::firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/subjects?q='.urlencode($subject->name))
            ->assertOk()
            ->assertSee($subject->name);

        if ($subject->class_code) {
            $this->actingAs($admin)
                ->get('/admin/subjects?class_code='.urlencode($subject->class_code))
                ->assertOk()
                ->assertSee($subject->class_code);
        }
    }

    public function test_role_access_controls_block_wrong_portals(): void
    {
        $admin = User::where('email', 'admin@school.test')->firstOrFail();
        $adviser = User::where('email', 'adviser@school.test')->firstOrFail();
        $student = User::where('email', 'student@school.test')->firstOrFail();

        $this->actingAs($student)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($student)->get('/adviser/dashboard')->assertForbidden();
        $this->actingAs($adviser)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/student/dashboard')->assertForbidden();
    }

    public function test_student_cannot_open_admin_dashboard(): void
    {
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $this->actingAs($student)->get('/admin/dashboard')->assertForbidden();
    }
}
