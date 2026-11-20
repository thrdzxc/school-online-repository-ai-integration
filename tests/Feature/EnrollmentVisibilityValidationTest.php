<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AppNotification;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\User;
use App\Services\SubjectClassroomCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentVisibilityValidationTest extends TestCase
{
    use RefreshDatabase;

    private function room(User $adviser, string $name): Room
    {
        return app(SubjectClassroomCreator::class)->create([
            'name' => $name, 'room_name' => $name.' Room', 'school_year' => '2026-2027',
            'semester' => '1st Semester', 'adviser_id' => $adviser->id,
        ]);
    }

    private function activity(Room $room, string $title, array $extra = []): Activity
    {
        return Activity::create($extra + [
            'subject_id' => $room->subject_id, 'room_id' => $room->id,
            'adviser_id' => $room->subject->adviser_id, 'title' => $title,
            'instructions' => 'Answer the questions.', 'max_score' => 100,
            'status' => 'published', 'allow_text' => true, 'allow_file' => true,
        ]);
    }

    public function test_join_button_reviews_the_classroom_before_any_enrollment_is_saved(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $room = $this->room($adviser, 'Mathematics');
        $this->actingAs($student)->get(route('student.rooms'))->assertOk()
            ->assertSee('action="'.route('student.rooms.preview').'"', false);
        $this->post(route('student.rooms.preview'), ['enrollment_key' => strtolower($room->enrollment_key)])
            ->assertRedirect(route('student.rooms'))->assertSessionHasNoErrors()
            ->assertSessionHas('enrollment_preview.subject', 'Mathematics');
        $this->get(route('student.rooms'))->assertOk()->assertSee('Confirm your enrollment')
            ->assertSee('Confirm Enrollment')->assertSee('Cancel')->assertSee($room->name)->assertSee($adviser->name);
        $this->assertDatabaseCount('enrollments', 0);
        $this->assertDatabaseCount('app_notifications', 0);
        // Cancel navigates back without submitting the final enrollment form.
        $this->get(route('student.rooms'))->assertOk()->assertDontSee('id="enrollment-confirmation"', false);
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_invalid_preview_key_shows_inline_error_without_enrolling(): void
    {
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $this->actingAs($student)->from(route('student.rooms'))
            ->post(route('student.rooms.preview'), ['enrollment_key' => 'INVALID-KEY'])
            ->assertRedirect(route('student.rooms'))->assertSessionHasErrors('enrollment_key');
        $this->get(route('student.rooms'))->assertOk()->assertSee('enrollment-key-error')
            ->assertDontSee('id="enrollment-confirmation"', false);
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_confirmation_rechecks_the_key_and_displays_success_details_after_saving(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $room = $this->room($adviser, 'Mathematics');
        $key = $room->enrollment_key;
        $this->actingAs($student)->post(route('student.rooms.preview'), ['enrollment_key' => $key])->assertSessionHasNoErrors();
        $room->update(['enrollment_key' => Room::newEnrollmentKey()]);
        $this->from(route('student.rooms'))->post(route('student.rooms.join'), ['enrollment_key' => $key])->assertSessionHasErrors('enrollment_key');
        $this->assertDatabaseCount('enrollments', 0);
        $this->post(route('student.rooms.preview'), ['enrollment_key' => $room->enrollment_key])->assertSessionHasNoErrors();
        $this->get(route('student.rooms'))->assertOk();
        $this->post(route('student.rooms.join'), ['enrollment_key' => $room->enrollment_key])
            ->assertSessionHasNoErrors()->assertSessionHas('enrollment_details.subject', 'Mathematics');
        $this->get(route('student.rooms'))->assertOk()->assertSee('Enrollment confirmed')
            ->assertSee('View Subject Activities')->assertSee('id="enrollment-alert"', false)
            ->assertDontSee('id="enrollment-confirmation"', false);
        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseCount('app_notifications', 2);
    }

    public function test_section_transfer_is_explained_before_the_student_confirms(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $room = $this->room($adviser, 'Mathematics');
        $other = Room::create(['subject_id' => $room->subject_id, 'name' => 'Section Two', 'enrollment_key' => Room::newEnrollmentKey(), 'active' => true]);
        Enrollment::create(['student_id' => $student->id, 'subject_id' => $room->subject_id, 'room_id' => $room->id]);
        $this->actingAs($student)->post(route('student.rooms.preview'), ['enrollment_key' => $other->enrollment_key])
            ->assertSessionHas('enrollment_preview.previous_classroom', $room->name);
        $this->get(route('student.rooms'))->assertOk()->assertSee('Confirming will transfer your enrollment')
            ->assertSee('Section Two');
        $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'room_id' => $room->id]);
    }

    public function test_enrollment_alert_and_notifications_are_not_duplicated(): void
    {
        $adviser = User::factory()->create(['active' => true, 'role' => 'adviser']);
        $student = User::factory()->create(['active' => true, 'role' => 'student']);
        $room = $this->room($adviser, 'Mathematics');
        $this->actingAs($student)->post(route('student.rooms.join'), ['enrollment_key' => $room->enrollment_key])
            ->assertSessionHasNoErrors()->assertSessionHas('enrollment_alert');
        $this->get(route('student.rooms'))->assertOk()->assertSee('Enrollment confirmed')->assertSee('id="enrollment-alert"', false);
        $this->assertDatabaseHas('app_notifications', ['user_id' => $adviser->id, 'title' => 'Student joined your room']);
        $this->assertDatabaseHas('app_notifications', ['user_id' => $student->id, 'title' => 'Room enrollment successful']);
        $this->post(route('student.rooms.join'), ['enrollment_key' => $room->enrollment_key])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('app_notifications', 2);
        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_lists_direct_links_files_and_submissions_respect_enrollment(): void
    {
        $adviser = User::factory()->create(['active' => true, 'role' => 'adviser']);
        $student = User::factory()->create(['active' => true, 'role' => 'student']);
        $room = $this->room($adviser, 'Mathematics');
        $other = $this->room($adviser, 'Science');
        $secondSection = Room::create(['subject_id' => $room->subject_id, 'name' => 'Section Two', 'enrollment_key' => Room::newEnrollmentKey(), 'active' => true]);
        $visible = $this->activity($room, 'Visible Mathematics Task');
        $hidden = $this->activity($other, 'Hidden Science Task');
        $sectionTask = $this->activity($secondSection, 'Hidden Section Task');
        $draft = $this->activity($room, 'Hidden Draft Task', ['status' => 'draft']);
        $this->actingAs($student)->get(route('student.activities.index'))->assertOk()->assertDontSee($visible->title)->assertDontSee($hidden->title);
        $this->post(route('student.rooms.join'), ['enrollment_key' => $room->enrollment_key])->assertSessionHasNoErrors();
        foreach (['student.activities.index', 'student.dashboard'] as $route) {
            $this->get(route($route))->assertOk()->assertSee($visible->title)
                ->assertDontSee($hidden->title)->assertDontSee($sectionTask->title)->assertDontSee($draft->title);
        }
        foreach ([$hidden, $sectionTask] as $activity) {
            foreach (['student.activities.show', 'student.activities.instruction.download', 'student.activities.rubric.download'] as $route) {
                $this->get(route($route, $activity))->assertForbidden();
            }
            $this->post(route('student.activities.submit', $activity), ['text_content' => 'Unauthorized answer'])->assertForbidden();
        }
        $this->assertDatabaseCount('submissions', 0);
        $this->delete(route('student.rooms.leave', $room))->assertRedirect();
        $this->get(route('student.activities.show', $visible))->assertForbidden();
    }

    public function test_selected_subject_filter_and_section_transfer_hide_other_activities(): void
    {
        $adviser = User::factory()->create(['active' => true, 'role' => 'adviser']);
        $student = User::factory()->create(['active' => true, 'role' => 'student']);
        $first = $this->room($adviser, 'Mathematics');
        $second = $this->room($adviser, 'Science');
        $a = $this->activity($first, 'First Subject Task');
        $b = $this->activity($second, 'Second Subject Task');
        $this->actingAs($student);
        $this->get(route('student.activities.index', ['subject_id' => $second->subject_id]))->assertForbidden();
        foreach ([$first, $second] as $room) $this->post(route('student.rooms.join'), ['enrollment_key' => $room->enrollment_key])->assertSessionHasNoErrors();
        $this->get(route('student.activities.index', ['subject_id' => $first->subject_id]))->assertOk()->assertSee($a->title)->assertDontSee($b->title);
        $section = Room::create(['subject_id' => $first->subject_id, 'name' => 'New Section', 'enrollment_key' => Room::newEnrollmentKey(), 'active' => true]);
        $this->post(route('student.rooms.join'), ['enrollment_key' => $section->enrollment_key])->assertSessionHasNoErrors();
        $this->get(route('student.activities.show', $a))->assertForbidden();
        $this->get(route('student.activities.index'))->assertOk()->assertDontSee($a->title)->assertSee($b->title);
    }

    public function test_admin_enrollment_notifies_both_parties_and_respects_capacity(): void
    {
        $adviser = User::factory()->create(['active' => true, 'role' => 'adviser']);
        $admin = User::factory()->create(['active' => true, 'role' => 'admin']);
        $student = User::factory()->create(['active' => true, 'role' => 'student']);
        $other = User::factory()->create(['active' => true, 'role' => 'student']);
        $room = $this->room($adviser, 'Mathematics');
        $room->update(['capacity' => 1]);
        $this->actingAs($admin)->post(route('admin.subjects.enroll', $room->subject), ['student_id' => $student->id, 'room_id' => $room->id])
            ->assertSessionHasNoErrors()->assertSessionHas('enrollment_alert');
        $this->assertSame(1, AppNotification::where('user_id', $adviser->id)->count());
        $this->assertSame(1, AppNotification::where('user_id', $student->id)->count());
        $this->post(route('admin.subjects.enroll', $room->subject), ['student_id' => $other->id, 'room_id' => $room->id])->assertSessionHasErrors('room_id');
        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_account_validation_is_inline_and_rejects_incomplete_email_and_numeric_name(): void
    {
        $admin = User::factory()->create(['active' => true, 'role' => 'admin']);
        $this->actingAs($admin);
        foreach (['student', 'student@', 'student@gmail', '@gmail.com', 'student gmail.com'] as $email) {
            $this->from(route('admin.users'))->post(route('admin.users.store'), [
                '_form' => 'create', 'name' => 'Student123', 'email' => $email, 'role' => 'student', 'password' => 'SecurePass!1',
            ])->assertSessionHasErrors(['name', 'email']);
        }
        $this->get(route('admin.users'))->assertOk()->assertSee('create-name-error')->assertSee('create-email-error')
            ->assertSee('aria-invalid="true"', false)->assertDontSee('Please check the following:');
        $this->assertDatabaseCount('users', 1);
        $this->post(route('admin.users.store'), [
            'name' => 'María Dela Cruz', 'email' => '  MARIA@gmail.com ', 'role' => 'student', 'password' => 'SecurePass!1',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['name' => 'María Dela Cruz', 'email' => 'maria@gmail.com']);
    }

    public function test_edit_errors_stay_with_the_edited_account_and_preserve_input(): void
    {
        $admin = User::factory()->create(['active' => true, 'role' => 'admin']);
        $student = User::factory()->create(['active' => true, 'role' => 'student']);
        $this->actingAs($admin)->from(route('admin.users'))->put(route('admin.users.update', $student), [
            '_form' => 'edit-'.$student->id, 'name' => 'Invalid123', 'email' => 'broken@gmail', 'role' => 'student',
        ])->assertSessionHasErrors(['name', 'email']);
        $this->get(route('admin.users'))->assertOk()->assertSee('value="Invalid123"', false)
            ->assertSee('id="edit-'.$student->id.'-name-error"', false)->assertDontSee('Please check the following:');
        $this->assertNotSame('Invalid123', $student->fresh()->name);
    }

    public function test_api_registration_uses_the_same_name_and_email_rules(): void
    {
        $this->postJson('/api/register', ['name' => 'Student123', 'email' => 'student@gmail', 'password' => 'SecurePass!1'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name', 'email']);
        $this->assertDatabaseCount('users', 0);
        $admin = User::factory()->create(['active' => true, 'role' => 'admin']);
        $this->actingAs($admin, 'sanctum')->postJson('/api/students', ['name' => 'Student123', 'course' => 'BSIT'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name']);
        $this->assertDatabaseCount('students', 0);
    }

    public function test_login_errors_appear_beside_email_and_password(): void
    {
        $this->from(route('login'))->post(route('login.attempt'), ['email' => 'student@gmail', 'password' => ''])
            ->assertSessionHasErrors(['email', 'password']);
        $this->get(route('login'))->assertOk()->assertSee('id="login-email-error"', false)
            ->assertSee('id="login-password-error"', false)->assertDontSee('Unable to sign in.');
    }

    public function test_default_seeder_does_not_restore_old_subjects_or_reset_accounts(): void
    {
        $user = User::factory()->create(['active' => true, 'role' => 'admin']);
        $password = $user->password;
        $this->seed();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subjects', 0);
        $this->assertSame($password, $user->fresh()->password);
    }
}
