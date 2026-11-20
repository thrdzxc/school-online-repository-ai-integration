<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Acceptance checks for gaps identified in the functional requirements audit. */
class FunctionalRequirementsGapsTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $room = $student->rooms()->firstOrFail();
        $activity = Activity::create(['subject_id' => $room->subject_id, 'room_id' => $room->id, 'adviser_id' => $room->subject->adviser_id, 'title' => 'Gap audit', 'instructions' => 'Answer', 'rubric_text' => 'Accuracy', 'status' => 'published', 'max_score' => 100, 'allow_text' => true]);
        $submission = Submission::create(['activity_id' => $activity->id, 'student_id' => $student->id, 'version' => 1, 'text_content' => 'Answer', 'status' => 'submitted', 'submitted_at' => now()]);
        $this->actingAs($room->subject->adviser);
        return [$activity, $submission];
    }

    public function test_return_for_revision_is_no_longer_allowed(): void
    {
        [, $submission] = $this->fixture();
        $this->post(route('adviser.submissions.grade', $submission), ['decision' => 'returned'])
            ->assertSessionHasErrors('decision');
        $this->assertSame('submitted', $submission->fresh()->status);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'submission_returned', 'target_id' => $submission->id]);
    }

    public function test_requirement_41_completion_counts_current_work_not_superseded_versions(): void
    {
        [$activity, $old] = $this->fixture();
        $old->update(['status' => 'superseded']);
        Submission::create(['activity_id' => $activity->id, 'student_id' => $old->student_id, 'version' => 2, 'text_content' => 'Revised answer', 'status' => 'graded', 'submitted_at' => now()]);
        $response = $this->get(route('adviser.reports'))->assertOk();
        $row = $response->viewData('activities')->firstWhere('id', $activity->id);
        $this->assertSame(1, (int) $row->submissions_count, 'One current graded submission should produce 100% completion; superseded versions must not dilute it.');
        $this->assertSame(1, (int) $row->graded_count);
    }

    public function test_every_existing_submission_status_blocks_another_upload(): void
    {
        [$activity, $submission] = $this->fixture();
        $this->actingAs($submission->student);
        \Illuminate\Support\Facades\Storage::fake('local');
        foreach (['submitted', 'ai_reviewed', 'ai_failed', 'returned', 'graded', 'superseded'] as $status) {
            $submission->update(['status' => $status]);
            $this->post(route('student.activities.submit', $activity), ['file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('replacement.txt', 'Replacement')])->assertSessionHasErrors('submission');
            $this->assertSame(1, $activity->submissions()->count());
            $this->assertSame($status, $submission->fresh()->status);
            $this->assertSame('Answer', $submission->fresh()->text_content);
            $this->get(route('student.activities.show', $activity))->assertOk()->assertDontSee('Submit Activity');
        }
        $this->assertSame([], \Illuminate\Support\Facades\Storage::disk('local')->allFiles());
    }

    public function test_subject_filters_separate_submissions_for_admin_and_adviser(): void
    {
        [$activity, $submission] = $this->fixture();
        $otherSubject = $activity->subject->replicate();
        $otherSubject->class_code = 'FILTER-OTHER';
        $otherSubject->name = 'Other subject';
        $otherSubject->save();
        $otherActivity = $activity->replicate();
        $otherActivity->subject_id = $otherSubject->id;
        $otherActivity->title = 'Other activity';
        $otherActivity->save();
        $otherSubmission = $submission->replicate();
        $otherSubmission->activity_id = $otherActivity->id;
        $otherSubmission->save();
        foreach (['adviser' => 'adviser.scores', 'admin' => 'admin.submissions'] as $role => $route) {
            $this->actingAs(User::where('email', $role.'@school.test')->firstOrFail());
            $response = $this->get(route($route, ['subject_id' => $activity->subject_id]))->assertOk();
            $this->assertSame([$submission->id], $response->viewData('submissions')->pluck('id')->all());
            $response->assertSee('Gap audit')->assertDontSee('Other activity');
        }
        $outsider = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $this->actingAs($outsider)->get(route('adviser.scores', ['subject_id' => $activity->subject_id]))->assertForbidden();
    }

    public function test_admin_can_edit_subject_details_without_reassigning_or_losing_classrooms(): void
    {
        [$activity] = $this->fixture();
        $subject = $activity->subject;
        $roomIds = $subject->rooms()->pluck('id')->all();
        $enrollmentIds = $subject->enrollments()->pluck('id')->all();
        $adviserId = $subject->adviser_id;
        $admin = User::where('role', 'admin')->firstOrFail();
        $data = ['name' => 'Updated Subject', 'school_year' => '2027-2028', 'semester' => '2nd Semester', 'description' => 'Updated course details', 'units' => 3, 'adviser_id' => $admin->id];
        $this->get(route('admin.subjects.edit', $subject))->assertForbidden();
        $this->put(route('admin.subjects.update', $subject), $data)->assertForbidden();
        $this->actingAs($admin)->get(route('admin.subjects.edit', $subject))->assertOk()->assertSee('Save Subject Changes');
        $this->put(route('admin.subjects.update', $subject), $data)->assertSessionHasNoErrors()->assertRedirect(route('admin.subjects.edit', $subject));
        $subject->refresh();
        $this->assertSame('Updated Subject', $subject->name);
        $this->assertSame('2027-2028', $subject->school_year);
        $this->assertSame($adviserId, $subject->adviser_id);
        $this->assertSame($roomIds, $subject->rooms()->pluck('id')->all());
        $this->assertSame($enrollmentIds, $subject->enrollments()->pluck('id')->all());
        $this->assertSame($subject->id, $activity->fresh()->subject_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_subject_updated', 'target_id' => $subject->id]);
        $this->put(route('admin.subjects.update', $subject), ['name' => '', 'units' => 25])->assertSessionHasErrors(['name', 'school_year', 'semester', 'units']);
        $this->assertSame('Updated Subject', $subject->fresh()->name);
        $this->get(route('admin.submissions'))->assertOk()->assertSee('Repository Files');
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $this->actingAs($student)->get(route('student.rooms'))->assertOk()->assertSee('Updated Subject');
    }
}
