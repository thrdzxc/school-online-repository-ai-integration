<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ChatConversation;
use App\Models\Room;
use App\Models\Submission;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreshAdviserWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private User $freshAdviser;
    private User $existingAdviser;
    private Room $existingRoom;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.ai.auto_evaluate' => false]);
        $this->seed(\Database\Seeders\DemoSeeder::class);
        $this->existingAdviser = User::where('email', 'adviser@school.test')->firstOrFail();
        $this->existingRoom = Room::whereHas('subject', fn ($q) => $q->where('adviser_id', $this->existingAdviser->id))->firstOrFail();
        $this->actingAs(User::where('role', 'admin')->firstOrFail())
            ->post(route('admin.users.store'), [
                'name' => 'Fresh Adviser', 'email' => 'fresh.adviser@example.com',
                'password' => 'FreshPassword!', 'role' => 'adviser',
                // Extra fields must never attach another adviser's records.
                'adviser_id' => $this->existingAdviser->id,
                'subject_id' => $this->existingRoom->subject_id,
            ])->assertSessionHasNoErrors();
        $this->freshAdviser = User::where('email', 'fresh.adviser@example.com')->firstOrFail();
        $this->post('/logout');
        $this->post('/login', ['email' => $this->freshAdviser->email, 'password' => 'FreshPassword!', 'portal' => 'adviser'])
            ->assertRedirect(route('adviser.dashboard'));
    }

    public function test_new_account_starts_with_empty_private_lists(): void
    {
        $dashboard = $this->get(route('adviser.dashboard'))->assertOk()->assertSee('Set Up My First Subject');
        $this->assertCount(0, $dashboard->viewData('subjects'));
        $this->assertCount(0, $dashboard->viewData('recent'));
        $this->assertSame(0, $dashboard->viewData('roomCount'));
        $this->assertSame(0, $dashboard->viewData('pending'));
        foreach ([
            'adviser.rooms.index' => ['subjects', 'rooms'],
            'adviser.activities.index' => ['activities'],
            'adviser.activities.create' => ['rooms'],
            'adviser.scores' => ['submissions', 'subjects', 'rooms'],
            'adviser.reports' => ['activities'],
            'chat.index' => ['conversations'],
        ] as $route => $collections) {
            $response = $this->get(route($route))->assertOk();
            foreach ($collections as $collection) $this->assertCount(0, $response->viewData($collection), $route.' '.$collection);
        }
        $this->getJson(route('rooms.details', ['ids' => [$this->existingRoom->id]]))->assertOk()->assertExactJson(['rooms' => []]);
        $this->get(route('admin.users'))->assertForbidden();
    }

    public function test_new_adviser_can_create_and_manage_their_own_subject_classrooms_and_activity(): void
    {
        $this->post(route('adviser.subjects.store'), [
            'name' => 'Fresh Subject', 'room_name' => 'Fresh Classroom',
            'school_year' => '2026-2027', 'semester' => 'First Semester',
            'adviser_id' => $this->existingAdviser->id,
        ])->assertSessionHasNoErrors();
        $subject = Subject::where('name', 'Fresh Subject')->firstOrFail();
        $this->assertSame($this->freshAdviser->id, $subject->adviser_id);
        $room = $subject->rooms()->firstOrFail();
        $this->assertNotEmpty($room->enrollment_key);
        $this->assertNotSame($this->existingRoom->enrollment_key, $room->enrollment_key);
        $this->get(route('adviser.rooms.show', $room))->assertOk()->assertSee('Fresh Classroom');
        $this->put(route('adviser.subjects.update', $subject), ['name' => 'My Updated Subject', 'school_year' => '2026-2027', 'semester' => 'First Semester', 'adviser_id' => $this->existingAdviser->id])->assertSessionHasNoErrors();
        $this->assertSame($this->freshAdviser->id, $subject->fresh()->adviser_id);
        $this->post(route('adviser.rooms.store'), ['subject_id' => $subject->id, 'name' => 'Second Classroom'])->assertSessionHasNoErrors();
        $this->assertSame(2, $subject->rooms()->count());
        $this->post(route('adviser.activities.store'), [
            'room_id' => $room->id, 'title' => 'My First Activity', 'instructions' => 'Answer the question.',
            'rubric_text' => 'Accuracy: 100 points', 'max_score' => 100, 'status' => 'published', 'allow_text' => 1,
            'adviser_id' => $this->existingAdviser->id, 'subject_id' => $this->existingRoom->subject_id,
        ])->assertSessionHasNoErrors();
        $activity = Activity::where('title', 'My First Activity')->firstOrFail();
        $this->assertSame($this->freshAdviser->id, $activity->adviser_id);
        $this->assertSame($subject->id, $activity->subject_id);
        $this->get(route('adviser.dashboard'))->assertOk()->assertDontSee('Set Up My First Subject');
        $this->actingAs($this->existingAdviser)->get(route('adviser.activities.show', $activity))->assertForbidden();
        $this->get(route('adviser.rooms.index'))->assertOk()->assertDontSee('My Updated Subject')->assertDontSee('Second Classroom');
    }

    public function test_direct_requests_cannot_read_or_change_another_advisers_records(): void
    {
        $room = $this->existingRoom;
        $originalKey = $room->enrollment_key;
        $originalName = $room->subject->name;
        $activity = Activity::create(['subject_id' => $room->subject_id, 'room_id' => $room->id, 'adviser_id' => $this->existingAdviser->id, 'title' => 'Private activity', 'status' => 'published', 'max_score' => 100, 'allow_text' => true]);
        $student = $room->students()->firstOrFail();
        $submission = Submission::create(['activity_id' => $activity->id, 'student_id' => $student->id, 'version' => 1, 'text_content' => 'Private answer', 'status' => 'submitted', 'submitted_at' => now()]);
        $conversation = ChatConversation::firstOrCreate(['room_id' => $room->id, 'student_id' => $student->id, 'adviser_id' => $this->existingAdviser->id]);
        $this->put(route('adviser.subjects.update', $room->subject), ['name' => 'Hijacked'])->assertForbidden();
        $this->post(route('adviser.rooms.store'), ['subject_id' => $room->subject_id, 'name' => 'Hijacked'])->assertNotFound();
        $this->get(route('adviser.rooms.show', $room))->assertRedirect(route('adviser.rooms.index'))->assertSessionHasErrors('room');
        $this->get(route('adviser.rooms.edit', $room))->assertNotFound();
        $this->put(route('adviser.rooms.update', $room), ['name' => 'Hijacked'])->assertNotFound();
        foreach (['adviser.rooms.regenerate-key', 'adviser.rooms.toggle'] as $route) {
            $this->post(route($route, $room))->assertSessionHasErrors('room');
        }
        $this->delete(route('adviser.rooms.students.remove', [$room, $student]))->assertSessionHasErrors('room');
        foreach (['adviser.activities.show', 'adviser.activities.edit', 'adviser.activities.submissions', 'adviser.activities.instruction.download', 'adviser.activities.rubric.download'] as $route) {
            $this->get(route($route, $activity))->assertForbidden();
        }
        $this->post(route('adviser.activities.store'), [
            'room_id' => $room->id, 'title' => 'Hijacked activity', 'instructions' => 'Answer.',
            'rubric_text' => 'Accuracy', 'max_score' => 100, 'status' => 'published', 'allow_text' => 1,
        ])->assertNotFound();
        $this->get(route('adviser.scores', ['subject_id' => $room->subject_id]))->assertForbidden();
        $this->get(route('adviser.scores', ['room_id' => $room->id]))->assertNotFound();
        $this->put(route('adviser.activities.update', $activity), ['title' => 'Hijacked'])->assertForbidden();
        $this->post(route('adviser.activities.toggle', $activity))->assertForbidden();
        foreach (['adviser.submissions.show', 'adviser.submissions.download'] as $route) $this->get(route($route, $submission))->assertForbidden();
        $this->post(route('adviser.submissions.ai', $submission))->assertForbidden();
        $this->post(route('adviser.submissions.grade', $submission), ['decision' => 'approved', 'final_score' => 100])->assertForbidden();
        $this->get(route('chat.show', $conversation))->assertForbidden();
        $this->get(route('chat.messages', $conversation))->assertForbidden();
        $this->post(route('chat.send', $conversation), ['body' => 'Hijacked'])->assertForbidden();
        $this->get(route('chat.room.student', [$room, $student]))->assertForbidden();
        $this->assertSame($originalKey, $room->fresh()->enrollment_key);
        $this->assertSame($originalName, $room->subject->fresh()->name);
        $this->assertSame('published', $activity->fresh()->status);
        $this->assertDatabaseHas('enrollments', ['room_id' => $room->id, 'student_id' => $student->id]);
        $this->assertDatabaseMissing('grades', ['submission_id' => $submission->id]);
    }
}
