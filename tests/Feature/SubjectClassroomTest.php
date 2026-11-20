<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use App\Services\SubjectClassroomCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubjectClassroomTest extends TestCase
{
    use RefreshDatabase;

    private function details(): array
    {
        return [
            'name' => 'Software Engineering', 'room_name' => 'Computer Laboratory',
            'school_year' => '2026-2027', 'semester' => '1st Semester',
            'section' => '3-B', 'room' => 'Lab 1', 'capacity' => 35,
            'schedule' => 'MW 8:00-9:30 AM',
        ];
    }

    public function test_both_roles_create_subject_and_classroom_in_one_request(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        foreach ([$adviser, $admin] as $user) {
            $this->actingAs($user)->post(route($user->role.'.subjects.store'), $this->details() + [
                'adviser_id' => $adviser->id,
            ])->assertRedirect()->assertSessionHasNoErrors();
        }

        $this->assertDatabaseCount('subjects', 2);
        $this->assertDatabaseCount('rooms', 2);
        $this->assertFalse(Schema::hasColumn('subjects', 'code'));
        $this->assertSame(2, Room::pluck('enrollment_key')->unique()->count());
        foreach (Room::with('subject')->get() as $room) {
            $this->assertSame('Software Engineering', $room->subject->name);
            $this->assertSame($adviser->id, $room->subject->adviser_id);
            $this->assertSame('Computer Laboratory', $room->name);
            $this->assertSame('Lab 1', $room->physical_room);
            $this->assertSame('3-B', $room->section);
            $this->assertSame(35, $room->capacity);
            $this->assertSame('MW 8:00-9:30 AM', $room->schedule);
        }

        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $room = Room::firstOrFail();
        $this->actingAs($student)->post(route('student.rooms.join'), [
            'enrollment_key' => $room->enrollment_key,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('enrollments', ['room_id' => $room->id, 'student_id' => $student->id]);
    }

    public function test_invalid_classroom_details_do_not_create_partial_records(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        foreach (['adviser', 'admin'] as $role) {
            $user = $role === 'adviser' ? $adviser : User::factory()->create(['role' => 'admin', 'active' => true]);
            $this->actingAs($user)->post(route($role.'.subjects.store'), array_replace($this->details(), [
                'room_name' => '', 'capacity' => 501, 'adviser_id' => $adviser->id,
            ]))->assertSessionHasErrors(['room_name', 'capacity']);
        }
        $this->assertDatabaseCount('subjects', 0);
        $this->assertDatabaseCount('rooms', 0);
    }

    public function test_room_creation_failure_rolls_back_the_subject(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        Room::creating(function () { throw new \RuntimeException('Simulated room failure'); });
        try {
            app(SubjectClassroomCreator::class)->create($this->details() + ['adviser_id' => $adviser->id]);
            $this->fail('Room creation should have failed.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated room failure', $e->getMessage());
        } finally {
            Room::flushEventListeners();
        }
        $this->assertDatabaseCount('subjects', 0);
        $this->assertDatabaseCount('rooms', 0);
    }

    public function test_additional_classrooms_and_subject_edits_preserve_enrollments_and_keys(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $room = app(SubjectClassroomCreator::class)->create($this->details() + ['adviser_id' => $adviser->id]);
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $enrollment = Enrollment::create(['subject_id' => $room->subject_id, 'room_id' => $room->id, 'student_id' => $student->id]);
        $key = $room->enrollment_key;
        $this->actingAs($adviser)->put(route('adviser.subjects.update', $room->subject), [
            'name' => 'Software Engineering Updated', 'school_year' => '2026-2027', 'semester' => '1st Semester',
        ])->assertSessionHasNoErrors();
        $this->post(route('adviser.rooms.store'), [
            'subject_id' => $room->subject_id, 'name' => 'Science Laboratory',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('subjects', 1);
        $this->assertDatabaseCount('rooms', 2);
        $this->assertSame($key, $room->fresh()->enrollment_key);
        $this->assertSame($room->id, $enrollment->fresh()->room_id);
        $this->assertSame('Software Engineering Updated', $room->subject->fresh()->name);

        $other = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $this->actingAs($other)->post(route('adviser.rooms.store'), [
            'subject_id' => $room->subject_id, 'name' => 'Forbidden Classroom',
        ])->assertNotFound();
    }

    public function test_creation_pages_use_one_form_without_subject_code_inputs(): void
    {
        foreach (['adviser' => 'adviser.rooms.index', 'admin' => 'admin.subjects'] as $role => $route) {
            $user = User::factory()->create(['role' => $role, 'active' => true]);
            $this->actingAs($user)->get(route($route))->assertOk()
                ->assertSee('Create Subject & Classroom', false)
                ->assertSee('name="room_name"', false)->assertSee('name="name"', false)
                ->assertDontSee('name="code"', false)->assertDontSee('Subject Code')
                ->assertDontSee('Step 1')->assertDontSee('Step 2');
        }
    }

    public function test_migration_updates_only_legacy_default_room_names_and_preserves_enrollments(): void
    {
        $adviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $room = app(SubjectClassroomCreator::class)->create($this->details() + ['adviser_id' => $adviser->id]);
        $student = User::factory()->create(['role' => 'student', 'active' => true]);
        $enrollment = Enrollment::create(['subject_id' => $room->subject_id, 'room_id' => $room->id, 'student_id' => $student->id]);
        $custom = $room->replicate();
        $custom->name = 'Custom Classroom';
        $custom->enrollment_key = Room::newEnrollmentKey();
        $custom->save();
        $key = $room->enrollment_key;
        $migration = require database_path('migrations/2026_11_08_000014_remove_subject_codes.php');
        $migration->down();
        DB::table('subjects')->where('id', $room->subject_id)->update(['code' => 'LEGACY100', 'class_code' => 'F100']);
        $room->update(['name' => 'F100 · LEGACY100']);
        $migration->up();

        $this->assertSame('Software Engineering · 3-B', $room->fresh()->name);
        $this->assertSame('Custom Classroom', $custom->fresh()->name);
        $this->assertSame($key, $room->fresh()->enrollment_key);
        $this->assertSame($room->id, $enrollment->fresh()->room_id);
        $this->assertFalse(Schema::hasColumn('subjects', 'code'));
    }
}
