<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Room;
use App\Models\User;
use App\Services\SubjectClassroomCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentKeyTest extends TestCase
{
    use RefreshDatabase;

    private function createRoom(User $adviser): Room
    {
        return app(SubjectClassroomCreator::class)->create([
            'name' => 'Software Engineering', 'room_name' => 'Computer Laboratory',
            'school_year' => '2026-2027', 'semester' => '1st Semester',
            'adviser_id' => $adviser->id,
        ]);
    }

    public function test_creation_opens_the_generated_key_for_the_adviser_to_share(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $response = $this->actingAs($adviser)->post(route('adviser.subjects.store'), [
            'name' => 'Software Engineering', 'room_name' => 'Computer Laboratory',
            'school_year' => '2026-2027', 'semester' => '1st Semester',
        ])->assertSessionHasNoErrors();
        $room = Room::firstOrFail();
        $response->assertRedirect(route('adviser.rooms.show', $room));
        $this->get(route('adviser.rooms.show', $room))->assertOk()
            ->assertSee('Subject Enrollment Key')->assertSee($room->enrollment_key)
            ->assertSee($room->subject->name)->assertSee('Copy Enrollment Key');

        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $this->actingAs($student)->get(route('adviser.rooms.show', $room))->assertForbidden();
        $this->get(route('student.rooms'))->assertOk()->assertDontSee($room->enrollment_key);
    }

    public function test_missing_and_incorrect_keys_cannot_enroll_even_with_room_or_subject_ids(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $room = $this->createRoom($adviser);
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        foreach ([null, '', 'ROOM-NOTVALID', $room->subject->name] as $key) {
            $this->actingAs($student)->from(route('student.rooms'))->post(route('student.rooms.join'), [
                'enrollment_key' => $key, 'room_id' => $room->id, 'subject_id' => $room->subject_id,
            ])->assertRedirect(route('student.rooms'))->assertSessionHasErrors('enrollment_key');
            $this->assertDatabaseCount('enrollments', 0);
        }
    }

    public function test_valid_key_enrolls_only_its_matching_subject_and_classroom(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $first = $this->createRoom($adviser);
        $second = $this->createRoom($adviser);
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $this->assertNotSame($first->enrollment_key, $second->enrollment_key);
        $this->actingAs($student)->post(route('student.rooms.join'), [
            'enrollment_key' => '  '.strtolower($first->enrollment_key).'  ',
            'room_id' => $second->id, 'subject_id' => $second->subject_id,
        ])->assertRedirect(route('student.rooms'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id, 'room_id' => $first->id, 'subject_id' => $first->subject_id,
        ]);
        $this->assertDatabaseMissing('enrollments', ['student_id' => $student->id, 'subject_id' => $second->subject_id]);
        $this->post(route('student.rooms.join'), ['enrollment_key' => $first->enrollment_key])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_replaced_key_is_rejected_and_the_current_key_works(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $room = $this->createRoom($adviser);
        $oldKey = $room->enrollment_key;
        $this->actingAs($adviser)->post(route('adviser.rooms.regenerate-key', $room))->assertSessionHasNoErrors();
        $room->refresh();
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $this->actingAs($student)->post(route('student.rooms.join'), ['enrollment_key' => $oldKey])
            ->assertSessionHasErrors('enrollment_key');
        $this->assertDatabaseCount('enrollments', 0);
        $this->post(route('student.rooms.join'), ['enrollment_key' => $room->enrollment_key])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'room_id' => $room->id]);
    }

    public function test_correct_key_cannot_join_closed_inactive_or_full_classes(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $room = $this->createRoom($adviser);
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $this->actingAs($student);
        $room->update(['active' => false]);
        $this->post(route('student.rooms.join'), ['enrollment_key' => $room->enrollment_key])->assertSessionHasErrors('enrollment_key');
        $room->update(['active' => true]);
        $room->subject->update(['active' => false]);
        $this->post(route('student.rooms.join'), ['enrollment_key' => $room->enrollment_key])->assertSessionHasErrors('enrollment_key');
        $this->assertDatabaseCount('enrollments', 0);
        $room->subject->update(['active' => true]);
        $room->update(['capacity' => 1]);
        $other = User::factory()->create(['role' => 'student', 'active' => true]);
        Enrollment::create(['student_id' => $other->id, 'subject_id' => $room->subject_id, 'room_id' => $room->id]);
        $this->post(route('student.rooms.join'), ['enrollment_key' => $room->enrollment_key])->assertSessionHasErrors('enrollment_key');
        $this->assertDatabaseMissing('enrollments', ['student_id' => $student->id]);
    }
}
