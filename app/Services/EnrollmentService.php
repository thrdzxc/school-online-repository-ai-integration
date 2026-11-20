<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Room;
use App\Models\User;
use App\Support\Audit;
use App\Support\Notifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function enroll(Room $room, User $student, User $actor, ?string $key = null): bool
    {
        return DB::transaction(function () use ($room, $student, $actor, $key) {
            // Serialize changes for a student and capacity checks for a classroom.
            User::whereKey($student->id)->lockForUpdate()->firstOrFail();
            $room = Room::with('subject')->whereKey($room->id)->lockForUpdate()->firstOrFail();
            $field = $key === null ? 'room_id' : 'enrollment_key';
            if ($key !== null && !hash_equals(strtoupper($room->enrollment_key), $key)) {
                throw ValidationException::withMessages([$field => 'This enrollment key has changed. Ask your adviser for the current key.']);
            }
            if (!$student->active || !$room->active || !$room->subject->active) {
                throw ValidationException::withMessages([$field => 'The student and classroom must be active for enrollment.']);
            }
            $existing = Enrollment::where('student_id', $student->id)->where('subject_id', $room->subject_id)->first();
            if ($existing && (int) $existing->room_id === $room->id) return false;
            if ($room->capacity && $room->enrollments()->count() >= $room->capacity) {
                throw ValidationException::withMessages([$field => 'This room has reached its enrollment capacity.']);
            }
            $enrollment = Enrollment::updateOrCreate(
                ['subject_id' => $room->subject_id, 'student_id' => $student->id],
                ['room_id' => $room->id]
            );
            Notifier::send($room->subject->adviser_id, 'Student joined your room',
                $student->name.' enrolled in '.$room->subject->name.' / '.$room->name.'.',
                route('adviser.rooms.show', $room));
            Notifier::send($student->id, 'Room enrollment successful',
                'You are now enrolled in '.$room->subject->name.' / '.$room->name.'.',
                route('student.activities.index', ['subject_id' => $room->subject_id]));
            Audit::log($actor, 'student_enrolled', 'room', $room->id,
                ['student_id' => $student->id, 'subject_id' => $room->subject_id, 'enrollment_id' => $enrollment->id]);
            return true;
        });
    }
}
