<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AiEvaluation;
use App\Models\RepositoryItem;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FunctionalRequirementsAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.ai.auto_evaluate' => false]);
        Storage::fake('local');
        $this->seed(\Database\Seeders\DemoSeeder::class);
    }

    private function user(string $role): User
    {
        return User::where('email', $role.'@school.test')->firstOrFail();
    }

    private function activity(): Activity
    {
        $room = $this->user('student')->rooms()->firstOrFail();
        return Activity::create([
            'subject_id' => $room->subject_id, 'room_id' => $room->id, 'adviser_id' => $room->subject->adviser_id,
            'title' => 'Audit activity', 'instructions' => 'Write an answer.', 'rubric_text' => 'Accuracy: 100 points',
            'max_score' => 100, 'status' => 'published', 'allow_text' => true, 'allow_file' => true, 'allow_late' => true,
        ]);
    }

    public function test_profile_replacement_removes_old_file_and_removal_clears_every_role_photo(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZV7sAAAAASUVORK5CYII=');
        foreach (['student', 'adviser', 'admin'] as $role) {
            $user = $this->user($role);
            $paths = [];
            try {
                foreach (['first.png', 'replacement.png'] as $name) {
                    $this->actingAs($user)->post('/profile', ['profile_photo' => UploadedFile::fake()->createWithContent($name, $png)])->assertSessionHasNoErrors();
                    $paths[] = public_path($user->fresh()->profile_photo_path);
                    $this->assertFileExists(end($paths));
                }
                $this->assertNotSame($paths[0], $paths[1]);
                $this->assertFileDoesNotExist($paths[0]);
                $this->post('/profile', ['remove_photo' => 1])->assertSessionHasNoErrors();
                $this->assertNull($user->fresh()->profile_photo_path);
                $this->assertFileDoesNotExist($paths[1]);
                $this->post('/profile', ['profile_photo' => UploadedFile::fake()->createWithContent('bad.txt', 'not a photo')])->assertSessionHasErrors('profile_photo');
            } finally {
                foreach ($paths as $path) if (is_file($path)) unlink($path);
            }
        }
    }

    public function test_instruction_rubric_downloads_and_single_submission(): void
    {
        $activity = $this->activity();
        Storage::disk('local')->put('audit/instructions.txt', 'Instruction content');
        Storage::disk('local')->put('audit/rubric.txt', 'Rubric content');
        $activity->update(['instruction_file_path' => 'audit/instructions.txt', 'instruction_original_filename' => 'instructions.txt', 'rubric_file_path' => 'audit/rubric.txt', 'rubric_original_filename' => 'rubric.txt']);
        foreach (['student', 'adviser', 'admin'] as $role) {
            foreach (['instruction', 'rubric'] as $kind) {
                $this->actingAs($this->user($role))->get(route($role.'.activities.'.$kind.'.download', $activity))->assertOk()->assertDownload($kind === 'instruction' ? 'instructions.txt' : 'rubric.txt');
            }
        }
        $this->actingAs($this->user('student'))->post(route('student.activities.submit', $activity), ['file' => UploadedFile::fake()->createWithContent('answer.txt', 'Original answer')])->assertSessionHasNoErrors();
        $first = $activity->submissions()->firstOrFail();
        $this->post(route('student.activities.submit', $activity), ['text_content' => 'Improved answer'])->assertSessionHasErrors('submission');
        $this->assertSame('submitted', $first->fresh()->status);
        $this->assertEquals([1], $activity->submissions()->orderBy('version')->pluck('version')->all());
        $this->get(route('student.activities.show', $activity))->assertSee('Version 1')->assertDontSee('Version 2')->assertDontSee('Submit Activity');
        foreach (['student', 'adviser', 'admin'] as $role) {
            $this->actingAs($this->user($role))->get(route($role.'.submissions.download', $first))->assertOk()->assertDownload('answer.txt');
            $this->get(route($role.'.submissions.show', $first))->assertOk()->assertSee('answer.txt');
        }
        $outsider = User::factory()->create(['role' => 'student', 'active' => true]);
        $this->actingAs($outsider)->get(route('student.submissions.download', $first))->assertForbidden();
    }

    public function test_removed_student_repository_preserves_admin_access_to_existing_files(): void
    {
        Storage::disk('local')->put('repository/legacy/notes.txt', 'Private notes');
        $item = RepositoryItem::create([
            'user_id' => $this->user('student')->id,
            'title' => 'Audit document',
            'file_path' => 'repository/legacy/notes.txt',
            'original_filename' => 'notes.txt',
        ]);
        $this->actingAs($this->user('admin'))->get(route('admin.repository'))->assertOk()->assertSee('Audit document');
        $this->get(route('admin.repository.show', $item))->assertOk();
        $this->get(route('admin.repository.download', $item))->assertOk()->assertDownload('notes.txt');

        foreach ([$this->user('student'), User::factory()->create(['role' => 'student', 'active' => true])] as $student) {
            $this->actingAs($student)->get(route('student.dashboard'))->assertOk()->assertDontSee('/student/repository', false);
            foreach (['/student/repository', '/workspaces/student/student/repository'] as $path) {
                $this->get($path)->assertNotFound();
                $this->post($path, ['title' => 'Blocked item', 'text_content' => 'Blocked'])->assertNotFound();
                $this->get($path.'/'.$item->id.'/download')->assertNotFound();
                $this->delete($path.'/'.$item->id)->assertNotFound();
            }
        }
        $this->assertDatabaseMissing('repository_items', ['title' => 'Blocked item']);
        $this->assertDatabaseHas('repository_items', ['id' => $item->id]);
        Storage::disk('local')->assertExists($item->file_path);
    }

    public function test_leaving_roster_removal_and_admin_unenrollment_revoke_access(): void
    {
        $student = $this->user('student');
        $room = $student->rooms()->firstOrFail();
        $this->actingAs($student)->delete(route('student.rooms.leave', $room))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('enrollments', ['student_id' => $student->id, 'room_id' => $room->id]);
        $this->post(route('student.rooms.join'), ['enrollment_key' => $room->enrollment_key])->assertSessionHasNoErrors();
        $this->actingAs($room->subject->adviser)->get(route('adviser.rooms.show', $room))->assertOk()->assertSee($student->email);
        $this->delete(route('adviser.rooms.students.remove', [$room, $student]))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('enrollments', ['student_id' => $student->id, 'room_id' => $room->id]);
        $this->actingAs($this->user('admin'))->post(route('admin.subjects.enroll', $room->subject), ['student_id' => $student->id, 'room_id' => $room->id])->assertSessionHasNoErrors();
        $this->delete(route('admin.subjects.unenroll', [$room->subject, $student]))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('enrollments', ['student_id' => $student->id, 'subject_id' => $room->subject_id]);
        $this->actingAs($student)->get(route('chat.room', $room))->assertForbidden();
    }

    public function test_user_update_password_reset_and_disabled_login(): void
    {
        $student = $this->user('student');
        $this->actingAs($this->user('admin'))->put(route('admin.users.update', $student), ['name' => 'Updated Student', 'email' => $student->email, 'role' => 'student'])->assertSessionHasNoErrors();
        $this->assertSame('Updated Student', $student->fresh()->name);
        $this->post(route('admin.users.reset-password', $student), ['password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewPassword123!', $student->fresh()->password));
        $this->post(route('admin.users.toggle', $student))->assertSessionHasNoErrors();
        $this->assertFalse($student->fresh()->active);
        $this->post('/logout');
        $this->post('/login', ['email' => $student->email, 'password' => 'NewPassword123!'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($this->user('admin'))->post(route('admin.users.toggle', $student))->assertSessionHasNoErrors();
        $this->post('/logout');
        $this->post('/login', ['email' => $student->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $student->email, 'password' => 'NewPassword123!'])->assertRedirect(route('student.dashboard'));
    }

    public function test_publish_unpublish_and_final_grade_with_visible_ai_feedback(): void
    {
        $activity = $this->activity();
        $this->actingAs($this->user('adviser'))->post(route('adviser.activities.toggle', $activity))->assertSessionHasNoErrors();
        $this->actingAs($this->user('student'))->get(route('student.activities.show', $activity))->assertNotFound();
        $this->actingAs($this->user('adviser'))->post(route('adviser.activities.toggle', $activity))->assertSessionHasNoErrors();
        $this->actingAs($this->user('student'))->post(route('student.activities.submit', $activity), ['text_content' => 'My answer'])->assertSessionHasNoErrors();
        $first = $activity->submissions()->firstOrFail();
        $latest = $activity->submissions()->orderByDesc('version')->firstOrFail();
        AiEvaluation::create(['submission_id' => $latest->id, 'provider' => 'groq', 'model' => 'audit-fixture', 'status' => 'completed', 'is_demo' => false, 'recommended_score' => 82, 'summary' => 'Audit AI feedback', 'rubric_breakdown' => [['name' => 'Accuracy', 'score' => 82, 'max_points' => 100, 'feedback' => 'Audit criterion feedback']], 'evaluated_at' => now()]);
        $this->get(route('student.submissions.show', $latest))->assertDontSee('Audit AI feedback');
        $this->actingAs($this->user('adviser'))->get(route('adviser.submissions.show', $latest))->assertOk()->assertSee('Audit criterion feedback');
        $this->post(route('adviser.submissions.grade', $latest), ['decision' => 'approved', 'final_score' => 85, 'adviser_comment' => 'Final feedback'])->assertSessionHasNoErrors();
        $this->actingAs($this->user('student'))->get(route('student.submissions.show', $latest))->assertOk()->assertSee('Audit AI feedback')->assertSee('Final feedback');
        $this->get(route('student.grades'))->assertOk()->assertSee('85')->assertSee('82');
        $this->actingAs($this->user('adviser'))->get(route('adviser.scores'))->assertOk()->assertSee('Audit activity');
        $this->get(route('adviser.reports'))->assertOk()->assertSee('Audit activity');
        $this->actingAs($this->user('admin'))->get(route('admin.submissions'))->assertOk()->assertSee('85');
        $this->get(route('admin.ai-monitor'))->assertOk()->assertSee('audit-fixture');
        $this->get(route('admin.monitor'))->assertOk();
        $this->get(route('admin.audit'))->assertOk()->assertSee('grade finalized');
        $this->assertDatabaseHas('audit_logs', ['action' => 'grade_finalized']);
    }
}
