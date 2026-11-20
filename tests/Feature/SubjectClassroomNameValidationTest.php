<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectClassroomNameValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_subject_and_classroom_creation_validates_names_for_both_roles(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);
        $adviser = User::where('role', 'adviser')->firstOrFail();
        foreach (['adviser', 'admin'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            $data = ['name' => 'Computer Science', 'room_name' => 'Computer Laboratory', 'room' => 'OS 304', 'school_year' => '2026-2027', 'semester' => '1st Semester', 'adviser_id' => $adviser->id];
            foreach (['name' => ['@@@', 'Science 2', 'Science-Lab', '123', '   '], 'room_name' => ['@@@', 'Lab 2', 'Lab/Room', '   '], 'room' => ['OS-304', 'OS@304', 'Room/3', 'OS_304']] as $field => $values) {
                foreach ($values as $value) {
                    $before = Subject::count();
                    $this->post(route($role.'.subjects.store'), array_replace($data, [$field => $value]))->assertSessionHasErrors($field);
                    $this->assertSame($before, Subject::count());
                }
            }
            $this->post(route($role.'.subjects.store'), $data)->assertSessionHasNoErrors();
            $this->assertDatabaseHas('subjects', ['name' => 'Computer Science', 'room' => 'OS 304']);
            $this->assertDatabaseHas('rooms', ['name' => 'Computer Laboratory', 'physical_room' => 'OS 304']);
        }
    }

    public function test_editing_subjects_and_rooms_cannot_bypass_validation(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);
        $adviser = User::where('role', 'adviser')->firstOrFail();
        $room = Room::whereHas('subject', fn ($q) => $q->where('adviser_id', $adviser->id))->firstOrFail();
        foreach (['adviser', 'admin'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            $data = ['name' => 'Mathematics', 'school_year' => '2026-2027', 'semester' => '1st Semester', 'room' => 'EM19'];
            $this->put(route($role.'.subjects.update', $room->subject), array_replace($data, ['name' => 'Math@', 'room' => 'EM#19']))->assertSessionHasErrors(['name', 'room']);
            $this->put(route($role.'.subjects.update', $room->subject), $data)->assertSessionHasNoErrors();
            $this->put(route($role.'.rooms.update', $room), ['name' => 'Lab2', 'physical_room' => 'OS-304'])->assertSessionHasErrors(['name', 'physical_room']);
            $this->put(route($role.'.rooms.update', $room), ['name' => 'Laboratory', 'physical_room' => '304'])->assertSessionHasNoErrors();
            $this->assertSame('Laboratory', $room->fresh()->name);
            $this->assertSame('304', $room->fresh()->physical_room);
        }
        $this->actingAs($adviser);
        $this->post(route('adviser.rooms.store'), ['subject_id' => $room->subject_id, 'name' => 'Lab!', 'physical_room' => 'Room#2'])->assertSessionHasErrors(['name', 'physical_room']);
        $this->post(route('adviser.rooms.store'), ['subject_id' => $room->subject_id, 'name' => "Agham P\u{00E1}aralan", 'physical_room' => null])->assertSessionHasNoErrors();
        $this->get(route('adviser.rooms.index'))->assertOk()->assertSee('Use letters and spaces only.')->assertSee('Use letters, numbers, and spaces only.');
    }
}
