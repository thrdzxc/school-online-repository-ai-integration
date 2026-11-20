<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomEditingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DemoSeeder::class);
    }

    public function test_admin_and_owning_adviser_can_edit_existing_classrooms_without_losing_enrollments(): void
    {
        $room = Room::with('subject')->firstOrFail();
        $key = $room->enrollment_key;
        $enrollments = $room->enrollments()->pluck('id')->all();
        $activities = \App\Models\Activity::where('room_id', $room->id)->pluck('id')->all();
        foreach (['adviser', 'admin'] as $role) {
            $user = $role === 'adviser' ? $room->subject->adviser : User::where('role', 'admin')->firstOrFail();
            $this->actingAs($user)->get(route($role.'.rooms.edit', $room))->assertOk()->assertSee('Save Classroom Changes');
            $data = ['name' => $role === 'admin' ? 'OS' : 'COMPLAB', 'section' => '3-C', 'physical_room' => 'OS304', 'capacity' => 40, 'schedule' => 'MW 8:00-9:30 AM'];
            $this->from(route($role.'.rooms.edit', $room))->put(route($role.'.rooms.update', $room), $data)
                ->assertRedirect(route($role.'.rooms.edit', $room))->assertSessionHasNoErrors()->assertSessionHas('success');
            $room->refresh();
            foreach ($data as $field => $value) $this->assertEquals($value, $room->$field);
            $this->assertSame($key, $room->enrollment_key);
            $this->assertSame($enrollments, $room->enrollments()->pluck('id')->all());
            $this->assertSame($activities, \App\Models\Activity::where('room_id', $room->id)->pluck('id')->all());
            $this->get(route($role === 'admin' ? 'admin.subjects' : 'adviser.rooms.index'))->assertOk()->assertSee('Edit Classroom');
        }
        $student = $room->students()->firstOrFail();
        $this->actingAs($student)->get(route('student.rooms'))->assertOk()->assertSee('OS304')->assertSee('3-C');
    }

    public function test_room_edits_validate_fields_and_capacity_before_saving(): void
    {
        $room = Room::firstOrFail();
        $room->update(['active' => false]);
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.rooms.edit', $room))->assertOk();
        $originalName = $room->name;
        $this->put(route('admin.rooms.update', $room), ['name' => '', 'section' => str_repeat('x', 81), 'capacity' => 501])
            ->assertSessionHasErrors(['name', 'section', 'capacity']);
        $this->assertSame($originalName, $room->fresh()->name);
        $this->assertGreaterThan(1, $room->enrollments()->count());
        $this->put(route('admin.rooms.update', $room), ['name' => 'EM', 'capacity' => 1])->assertSessionHasErrors('capacity');
        $this->assertSame($originalName, $room->fresh()->name);
        $this->put(route('admin.rooms.update', $room), ['name' => 'EM', 'section' => null, 'physical_room' => 'EM19', 'capacity' => null, 'schedule' => null])
            ->assertSessionHasNoErrors();
        $this->assertNull($room->fresh()->capacity);
        $this->assertFalse($room->fresh()->active);
    }

    public function test_students_and_other_advisers_cannot_edit_a_room(): void
    {
        $room = Room::firstOrFail();
        $otherAdviser = User::factory()->create(['role' => 'adviser', 'active' => true]);
        $this->actingAs($otherAdviser)->get(route('adviser.rooms.edit', $room))->assertNotFound();
        $this->put(route('adviser.rooms.update', $room), ['name' => 'Unauthorized'])->assertNotFound();
        $this->get(route('admin.rooms.edit', $room))->assertForbidden();
        $student = User::where('role', 'student')->firstOrFail();
        foreach (['adviser', 'admin'] as $role) {
            $this->actingAs($student)->get(route($role.'.rooms.edit', $room))->assertForbidden();
            $this->put(route($role.'.rooms.update', $room), ['name' => 'Unauthorized'])->assertForbidden();
        }
        $this->assertNotSame('Unauthorized', $room->fresh()->name);
    }

    public function test_adviser_changes_are_visible_to_admin_and_enrolled_student_and_live_updates_are_scoped(): void
    {
        $room = Room::with('subject')->firstOrFail();
        $student = $room->students()->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();
        $details = ['name' => 'Updated Classroom', 'section' => 'Section Z', 'physical_room' => 'EM19', 'schedule' => 'Friday 10 AM', 'capacity' => 40];
        $this->actingAs($room->subject->adviser)->put(route('adviser.rooms.update', $room), $details)->assertSessionHasNoErrors();
        foreach ([$admin, $student] as $viewer) {
            $this->actingAs($viewer)->get(route($viewer->isAdmin() ? 'admin.subjects' : 'student.rooms'))
                ->assertOk()->assertSee('Updated Classroom')->assertSee('Section Z')->assertSee('EM19')->assertSee('Friday 10 AM');
            $this->getJson(route('rooms.details', ['ids' => [$room->id]]))->assertOk()
                ->assertJsonPath('rooms.0.name', 'Updated Classroom')
                ->assertJsonPath('rooms.0.section', 'Section Z')
                ->assertJsonPath('rooms.0.physical_room', 'EM19')
                ->assertJsonPath('rooms.0.schedule', 'Friday 10 AM');
        }
        foreach (['student', 'adviser'] as $role) {
            $outsider = User::factory()->create(['role' => $role, 'active' => true]);
            $this->actingAs($outsider)->getJson(route('rooms.details', ['ids' => [$room->id]]))->assertOk()->assertJsonCount(0, 'rooms');
        }
    }
}
