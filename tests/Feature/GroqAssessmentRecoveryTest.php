<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Submission;
use App\Models\User;
use App\Services\AiGradingService;
use App\Services\SubjectClassroomCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GroqAssessmentRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.groq.key' => 'test-key', 'services.groq.model' => 'openai/gpt-oss-20b']);
        Http::preventStrayRequests();
    }

    private function submission(): Submission
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $room = app(SubjectClassroomCreator::class)->create([
            'name' => 'Mathematics', 'room_name' => 'Section One', 'adviser_id' => $adviser->id,
            'school_year' => '2026-2027', 'semester' => 'First',
        ]);
        $activity = Activity::create([
            'subject_id' => $room->subject_id, 'room_id' => $room->id, 'adviser_id' => $adviser->id,
            'title' => 'Addition', 'instructions' => 'What is 2 + 2?', 'rubric_text' => 'Accuracy: 10 points.',
            'max_score' => 10, 'status' => 'published',
        ]);
        $this->actingAs($adviser);
        return Submission::create([
            'activity_id' => $activity->id, 'student_id' => $student->id, 'version' => 1,
            'text_content' => '4', 'status' => 'ai_failed', 'submitted_at' => now(),
        ]);
    }

    private function assessment(): array
    {
        return [
            'criteria' => [['name' => 'Accuracy', 'score' => 10, 'max_points' => 10, 'feedback' => 'Correct answer.']],
            'summary' => 'The answer is correct.', 'strengths' => ['Accurate calculation'],
            'improvements' => [], 'confidence' => 0.95,
        ];
    }

    private function response(?array $assessment = null, string $finishReason = 'stop'): array
    {
        return ['model' => 'openai/gpt-oss-20b', 'choices' => [[
            'finish_reason' => $finishReason,
            'message' => ['content' => json_encode($assessment ?? $this->assessment())],
        ]]];
    }

    private function jsonFailure(): array
    {
        return ['error' => ['code' => 'json_validate_failed',
            'message' => "Failed to generate JSON. Please adjust your prompt. See 'failed_generation' for more details.",
            // Must never be treated as a completed score, even if it parses.
            'failed_generation' => json_encode($this->assessment()),
        ]];
    }

    public function test_screenshot_error_recovers_and_saves_one_valid_recommendation(): void
    {
        $submission = $this->submission();
        Http::fakeSequence()->push($this->jsonFailure(), 400)->push($this->response());
        $this->post(route('adviser.submissions.ai', $submission))->assertSessionHasNoErrors();
        Http::assertSentCount(2);
        $requests = Http::recorded();
        $this->assertSame('json_schema', $requests[0][0]['response_format']['type']);
        $this->assertSame('json_object', $requests[1][0]['response_format']['type']);
        $this->assertSame($requests[0][0]['messages'], $requests[1][0]['messages']);
        $this->assertStringContainsString('Return exactly one JSON object', $requests[0][0]['messages'][0]['content']);
        $this->assertSame(8192, $requests[0][0]['max_completion_tokens']);
        $this->assertDatabaseCount('ai_evaluations', 1);
        $evaluation = $submission->fresh()->latestAiEvaluation;
        $this->assertSame('10.00', $evaluation->recommended_score);
        $this->assertSame(2, $evaluation->raw_response['assessment_attempts']);
        $this->assertSame('ai_reviewed', $submission->fresh()->status);
        $this->assertDatabaseCount('grades', 0);
        $this->get(route('adviser.submissions.show', $submission))->assertOk();
    }

    public function test_repeated_provider_json_failure_uses_validated_text_fallback(): void
    {
        Http::fakeSequence()->push($this->jsonFailure(), 400)->push($this->jsonFailure(), 400)->push($this->response());
        $evaluation = app(AiGradingService::class)->evaluate($this->submission());
        Http::assertSentCount(3);
        $this->assertSame('text', $evaluation->raw_response['assessment_format']);
        $this->assertSame('completed', $evaluation->status);
    }

    public function test_invalid_scores_are_never_saved_as_a_completed_assessment(): void
    {
        $submission = $this->submission();
        $bad = $this->assessment();
        $bad['criteria'][0]['score'] = 500;
        Http::fakeSequence()->push($this->response($bad))->push($this->response($bad))->push($this->response($bad));
        $this->post(route('adviser.submissions.ai', $submission))->assertSessionHasErrors('ai');
        Http::assertSentCount(3);
        $this->assertDatabaseCount('ai_evaluations', 1);
        $this->assertDatabaseHas('ai_evaluations', ['status' => 'failed', 'recommended_score' => null]);
        $this->assertDatabaseCount('grades', 0);
    }

    public function test_exhausted_json_failures_do_not_use_failed_generation_as_a_score(): void
    {
        $submission = $this->submission();
        Http::fakeSequence()->push($this->jsonFailure(), 400)->push($this->jsonFailure(), 400)->push($this->jsonFailure(), 400);
        $this->post(route('adviser.submissions.ai', $submission))->assertSessionHasErrors('ai');
        Http::assertSentCount(3);
        $this->assertNull($submission->fresh()->latestAiEvaluation);
        $this->assertDatabaseCount('ai_evaluations', 1);
    }

    public function test_authentication_rate_limits_and_unrelated_bad_requests_are_not_retried(): void
    {
        $sequence = Http::fakeSequence();
        foreach ([401, 429, 400] as $status) $sequence->push(['error' => ['message' => 'Request rejected']], $status);
        foreach ([401, 429, 400] as $index => $status) {
            try {
                app(AiGradingService::class)->testConnection();
                $this->fail('Expected an API failure.');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('HTTP '.$status, $e->getMessage());
            }
            Http::assertSentCount($index + 1);
        }
    }

    public function test_unsupported_schema_model_can_use_json_object_mode(): void
    {
        Http::fakeSequence()->push(['error' => ['message' => 'response_format json_schema is not supported with this model']], 400)
            ->push($this->response());
        $result = app(AiGradingService::class)->testConnection();
        $this->assertStringContainsString('structured assessment', $result['message']);
        Http::assertSentCount(2);
        $this->assertDatabaseCount('ai_evaluations', 0);
    }

    public function test_truncated_or_incomplete_responses_are_retried_before_saving(): void
    {
        $bad = $this->assessment();
        $bad['strengths'] = 'Not an array';
        Http::fakeSequence()->push($this->response(null, 'length'))->push($this->response($bad))->push($this->response());
        $evaluation = app(AiGradingService::class)->evaluate($this->submission());
        Http::assertSentCount(3);
        $this->assertSame('completed', $evaluation->status);
        $this->assertDatabaseCount('ai_evaluations', 1);
    }

    public function test_connection_test_checks_grading_json_and_does_not_save_evaluations(): void
    {
        Http::fakeSequence()->push($this->response());
        app(AiGradingService::class)->testConnection();
        Http::assertSent(fn ($request) => $request['response_format']['type'] === 'json_schema'
            && isset($request['response_format']['json_schema']['schema']['properties']['criteria']));
        $this->assertDatabaseCount('ai_evaluations', 0);
    }

    public function test_refusal_is_not_retried_or_stored_as_a_grade(): void
    {
        $submission = $this->submission();
        Http::fakeSequence()->push(['choices' => [['message' => ['refusal' => 'Cannot assess.', 'content' => null]]]]);
        $this->post(route('adviser.submissions.ai', $submission))->assertSessionHasErrors('ai');
        Http::assertSentCount(1);
        $this->assertNull($submission->fresh()->latestAiEvaluation);
        $this->assertDatabaseCount('grades', 0);
    }
}
