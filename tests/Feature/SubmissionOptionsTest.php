<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.ai.auto_evaluate' => false]);
        Storage::fake('local');
        $this->seed(\Database\Seeders\DemoSeeder::class);
    }

    private function activity(array $options): Activity
    {
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $room = $student->rooms()->firstOrFail();
        $this->actingAs($student);
        return Activity::create(array_merge([
            'subject_id' => $room->subject_id, 'room_id' => $room->id, 'adviser_id' => $room->subject->adviser_id,
            'title' => 'Submission options test', 'instructions' => 'Complete the task.', 'rubric_text' => 'Accuracy: 100 points',
            'max_score' => 100, 'status' => 'published', 'allow_text' => true, 'allow_file' => true,
            'allow_late' => true, 'due_at' => now()->addDay(),
        ], $options));
    }

    public function test_text_only_and_file_only_options_control_both_form_and_server(): void
    {
        $textOnly = $this->activity(['allow_file' => false]);
        $this->get(route('student.activities.show', $textOnly))->assertOk()->assertSee('name="text_content"', false)->assertDontSee('name="file"', false);
        $this->post(route('student.activities.submit', $textOnly), ['file' => UploadedFile::fake()->createWithContent('answer.txt', 'Answer')])->assertSessionHasErrors('file');
        $this->assertSame(0, $textOnly->submissions()->count());
        $this->post(route('student.activities.submit', $textOnly), ['text_content' => 'My answer'])->assertSessionHasNoErrors();
        $this->assertSame(1, $textOnly->submissions()->count());

        $fileOnly = $this->activity(['allow_text' => false]);
        $this->get(route('student.activities.show', $fileOnly))->assertOk()->assertDontSee('name="text_content"', false)->assertSee('name="file"', false);
        $this->post(route('student.activities.submit', $fileOnly), ['text_content' => 'Disallowed text'])->assertSessionHasErrors('text_content');
        $this->assertSame(0, $fileOnly->submissions()->count());
        $this->post(route('student.activities.submit', $fileOnly), ['file' => UploadedFile::fake()->createWithContent('answer.txt', 'My uploaded answer')])->assertSessionHasNoErrors();
        Storage::disk('local')->assertExists($fileOnly->submissions()->firstOrFail()->file_path);
    }

    public function test_late_option_and_legacy_returned_status_do_not_allow_resubmission(): void
    {
        $activity = $this->activity(['allow_late' => false, 'due_at' => now()->subHour()]);
        $this->get(route('student.activities.show', $activity))->assertOk()->assertSee('late submissions are not allowed')->assertDontSee('Submit Activity');
        $this->post(route('student.activities.submit', $activity), ['text_content' => 'Late answer'])->assertSessionHasErrors('submission');
        $this->assertSame(0, $activity->submissions()->count());
        $activity->update(['allow_late' => true]);
        $this->post(route('student.activities.submit', $activity), ['text_content' => 'Allowed late answer'])->assertSessionHasNoErrors();
        $submission = $activity->submissions()->firstOrFail();
        $this->assertTrue($submission->is_late);
        $submission->update(['status' => 'returned']);
        $activity->update(['allow_late' => false]);
        $this->get(route('student.activities.show', $activity))->assertOk()->assertDontSee('Submit Activity');
        $this->post(route('student.activities.submit', $activity), ['text_content' => 'Requested revision'])->assertSessionHasErrors('submission');
        $this->assertSame(1, $activity->submissions()->count());
    }

    public function test_adviser_options_save_and_unchecked_choices_survive_validation_errors(): void
    {
        $activity = $this->activity([]);
        $this->actingAs($activity->adviser);
        $data = ['room_id' => $activity->room_id, 'title' => 'Updated options', 'instructions' => 'Instructions', 'rubric_text' => 'Accuracy: 100 points', 'max_score' => 100, 'status' => 'published', 'allow_text' => 0, 'allow_file' => 1, 'allow_late' => 0];
        $this->put(route('adviser.activities.update', $activity), $data)->assertSessionHasNoErrors();
        $this->assertFalse($activity->fresh()->allow_text);
        $this->assertTrue($activity->fresh()->allow_file);
        $this->assertFalse($activity->fresh()->allow_late);
        $this->from(route('adviser.activities.edit', $activity))->put(route('adviser.activities.update', $activity), array_merge($data, ['title' => '', 'allow_file' => 0]))->assertSessionHasErrors('title');
        $html = $this->get(route('adviser.activities.edit', $activity))->assertOk()->getContent();
        foreach (['allow_text', 'allow_file', 'allow_late'] as $option) {
            preg_match('/<input id="'.$option.'"[^>]+>/', $html, $match);
            $this->assertNotEmpty($match);
            $this->assertStringNotContainsString('checked', $match[0]);
        }
        $this->put(route('adviser.activities.update', $activity), array_merge($data, ['allow_file' => 0]))->assertSessionHasErrors('allow_text');
    }
}
