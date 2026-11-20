<?php

namespace App\Services;

use App\Models\Subject;
use App\Models\Room;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SubjectClassroomCreator
{
    public function create(array $data): Room
    {
        return DB::transaction(function () use ($data) {
            $subject = Subject::create(Arr::except($data, ['room_name', 'capacity']) + ['active' => true]);

            return $subject->rooms()->create([
                'name' => $data['room_name'],
                'section' => $data['section'] ?? null,
                'physical_room' => $data['room'] ?? null,
                'schedule' => $data['schedule'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'enrollment_key' => Room::newEnrollmentKey(),
                'active' => true,
            ]);
        });
    }
}
