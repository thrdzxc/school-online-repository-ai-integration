<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use App\Services\SubjectClassroomCreator;
use App\Support\Audit;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    public function details(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:100'],
            'ids.*' => ['required', 'integer', 'min:1'],
        ]);
        $user = $request->user();
        $query = Room::whereIn('id', $data['ids']);
        if ($user->isStudent()) {
            $query->whereHas('enrollments', fn ($q) => $q->where('student_id', $user->id));
        } elseif ($user->isAdviser()) {
            $query->whereHas('subject', fn ($q) => $q->where('adviser_id', $user->id));
        } else {
            abort_unless($user->isAdmin(), 403);
        }
        return response()->json(['rooms' => $query->get(['id', 'name', 'section', 'physical_room', 'schedule', 'capacity', 'active'])])
            ->header('Cache-Control', 'private, no-store');
    }

    public function studentIndex()
    {
        $enrollments = Enrollment::with(['room.subject.adviser'])
            ->where('student_id', auth()->id())
            ->whereNotNull('room_id')
            ->latest()
            ->get();

        return view('student.rooms', compact('enrollments'));
    }

    public function previewEnrollment(Request $request)
    {
        [$room, $key] = $this->enrollmentRoom($request);
        $existing = Enrollment::with('room')->where('student_id', $request->user()->id)
            ->where('subject_id', $room->subject_id)->first();

        return redirect()->route('student.rooms')->withInput(['enrollment_key' => $key])
            ->with('enrollment_preview', [
                'key' => $key,
                'subject' => $room->subject->name,
                'classroom' => $room->name,
                'section' => $room->section,
                'adviser' => $room->subject->adviser->name,
                'previous_classroom' => $existing && (int) $existing->room_id !== $room->id ? $existing->room?->name : null,
                'already_enrolled' => $existing && (int) $existing->room_id === $room->id,
            ]);
    }

    private function enrollmentRoom(Request $request): array
    {
        $data = $request->validate([
            'enrollment_key' => ['required', 'string', 'max:40'],
        ]);

        $key = strtoupper(trim($data['enrollment_key']));
        $room = Room::with('subject.adviser')->whereRaw('UPPER(enrollment_key) = ?', [$key])->first();

        if (!$room || !$room->active || !$room->subject?->active) {
            throw ValidationException::withMessages([
                'enrollment_key' => 'The enrollment key is incorrect or enrollment is closed. Ask your adviser for the current key.',
            ]);
        }

        return [$room, $key];
    }

    public function join(Request $request)
    {
        [$room, $key] = $this->enrollmentRoom($request);

        $changed = app(\App\Services\EnrollmentService::class)->enroll($room, $request->user(), $request->user(), $key);
        $message = $changed
            ? 'Your enrollment has been completed successfully. You may now access the activities assigned to your classroom.'
            : 'You are already enrolled in '.$room->name.'.';
        return redirect()->route('student.rooms')->with('success', $message)
            ->with('enrollment_alert', $message)
            ->with('enrollment_details', [
                'title' => $changed ? 'Enrollment confirmed' : 'Already enrolled',
                'subject' => $room->subject->name,
                'classroom' => $room->name,
                'section' => $room->section,
                'adviser' => $room->subject->adviser->name,
                'activities_url' => route('student.activities.index', ['subject_id' => $room->subject_id]),
            ]);
    }

    public function leave(Room $room)
    {
        $enrollment = Enrollment::where('room_id', $room->id)
            ->where('student_id', auth()->id())
            ->firstOrFail();

        $room->loadMissing('subject');
        $enrollment->delete();

        if ($room->subject?->adviser_id) {
            Notifier::send(
                $room->subject->adviser_id,
                'Student left your room',
                auth()->user()->name.' left '.$room->name.'.',
                route('adviser.rooms.show', $room)
            );
        }

        Audit::log(auth()->user(), 'room_left', 'room', $room->id);
        return redirect()->route('student.rooms')->with('success', 'You left '.$room->name.'.');
    }

    public function adviserIndex()
    {
        $rooms = Room::with(['subject'])
            ->withCount('students')
            ->whereHas('subject', fn ($q) => $q->where('adviser_id', auth()->id()))
            ->latest()
            ->paginate(20);

        $subjects = Subject::where('adviser_id', auth()->id())
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('adviser.rooms.index', compact('rooms', 'subjects'));
    }

    /** Advisers can add their own subjects without depending on an administrator. */
    public function storeSubject(Request $request)
    {
        $data = $this->validateSubject($request);
        $data += $request->validate([
            'room_name' => ['required', 'string', 'max:120', new \App\Rules\AcademicName('Classroom name')],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);
        $room = app(SubjectClassroomCreator::class)->create($data + ['adviser_id' => auth()->id()]);
        $subject = $room->subject;
        Audit::log(auth()->user(), 'adviser_subject_created', 'subject', $subject->id);
        Audit::log(auth()->user(), 'room_created', 'room', $room->id, ['subject_id' => $subject->id]);
        return redirect()->route('adviser.rooms.show', $room)
            ->with('success', $subject->name.' and classroom '.$room->name.' created. Share the enrollment key below so students can join.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $this->assertOwnedSubject($subject);
        $data = $this->validateSubject($request);
        $subject->update($data);
        Audit::log(auth()->user(), 'adviser_subject_updated', 'subject', $subject->id);
        return back()->with('success', 'Subject details saved. Existing classrooms and enrollments were preserved.');
    }

    private function validateSubject(Request $request): array
    {
        $request->merge([
            'class_code' => $request->filled('class_code') ? strtoupper(trim((string) $request->input('class_code'))) : null,
        ]);
        return $request->validate([
            'name' => ['required', 'string', 'max:180', new \App\Rules\AcademicName('Subject name')],
            'description' => ['nullable', 'string', 'max:2000'],
            'class_code' => ['nullable', 'string', 'max:30'],
            'school_year' => ['required', 'string', 'max:30'],
            'semester' => ['required', 'string', 'max:30'],
            'section' => ['nullable', 'string', 'max:80'],
            'units' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'schedule' => ['nullable', 'string', 'max:120'],
            'room' => ['nullable', 'string', 'max:80', new \App\Rules\AcademicName('Physical room', true)],
        ]);
    }

    private function assertOwnedSubject(Subject $subject): void
    {
        abort_unless($subject->adviser_id === auth()->id(), 403);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:120', new \App\Rules\AcademicName('Classroom name')],
            'section' => ['nullable', 'string', 'max:80'],
            'physical_room' => ['nullable', 'string', 'max:80', new \App\Rules\AcademicName('Physical room', true)],
            'schedule' => ['nullable', 'string', 'max:120'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $subject = Subject::whereKey($data['subject_id'])->where('adviser_id', auth()->id())->where('active', true)->firstOrFail();

        $room = Room::create($data + [
            'enrollment_key' => Room::newEnrollmentKey(),
            'active' => true,
        ]);

        Audit::log(auth()->user(), 'room_created', 'room', $room->id, ['subject_id' => $subject->id]);

        return redirect()->route('adviser.rooms.show', $room)
            ->with('success', 'Classroom created for '.$subject->name.'. Share the enrollment key below so students can join.');
    }

    public function edit($room)
    {
        $room = $this->editableRoom($room);
        abort_unless($room, 404);
        return view('rooms.edit', compact('room'));
    }

    private function editableRoom($roomId): ?Room
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isAdviser(), 403);
        if (auth()->user()->isAdmin()) {
            return Room::with('subject')->whereKey($roomId)->first();
        }
        return $this->ownedRoom($roomId);
    }

    public function update(Request $request, $room)
    {
        $room = $this->editableRoom($room);
        abort_unless($room, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', new \App\Rules\AcademicName('Classroom name')],
            'section' => ['nullable', 'string', 'max:80'],
            'physical_room' => ['nullable', 'string', 'max:80', new \App\Rules\AcademicName('Physical room', true)],
            'schedule' => ['nullable', 'string', 'max:120'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);
        if (!empty($data['capacity']) && $data['capacity'] < $room->enrollments()->count()) {
            throw ValidationException::withMessages(['capacity' => 'Capacity cannot be lower than current enrollment.']);
        }
        $room->update($data);
        Audit::log(auth()->user(), 'room_updated', 'room', $room->id);
        return back()->with('success', 'Classroom information updated. The existing enrollment key is unchanged.');
    }

    public function show($room)
    {
        $room = $this->ownedRoom($room);
        if (!$room) {
            return redirect()->route('adviser.rooms.index')
                ->withErrors(['room' => 'That room could not be found. Refresh the room list and try again.']);
        }

        $room->load(['subject', 'students' => fn ($q) => $q->orderBy('name')]);
        return view('adviser.rooms.show', compact('room'));
    }

    public function regenerateKey($room)
    {
        $room = $this->ownedRoom($room);
        if (!$room) {
            return redirect()->route('adviser.rooms.index')
                ->withErrors(['room' => 'That room could not be found.']);
        }

        $room->update(['enrollment_key' => Room::newEnrollmentKey()]);
        Audit::log(auth()->user(), 'room_key_regenerated', 'room', $room->id);
        return back()->with('success', 'A new enrollment key was generated. The old key no longer works.');
    }

    public function toggle($room)
    {
        $room = $this->ownedRoom($room);
        if (!$room) {
            return redirect()->route('adviser.rooms.index')
                ->withErrors(['room' => 'That room could not be found.']);
        }

        $room->update(['active' => !$room->active]);
        Audit::log(auth()->user(), 'room_status_changed', 'room', $room->id, ['active' => $room->active]);
        return back()->with('success', 'Room enrollment status updated.');
    }

    public function removeStudent($room, User $student)
    {
        $room = $this->ownedRoom($room);
        if (!$room) {
            return redirect()->route('adviser.rooms.index')
                ->withErrors(['room' => 'That room could not be found.']);
        }
        abort_unless($student->role === 'student', 404);

        $enrollment = Enrollment::where('room_id', $room->id)
            ->where('student_id', $student->id)
            ->firstOrFail();
        $enrollment->delete();

        Notifier::send(
            $student->id,
            'Removed from room',
            'You were removed from '.$room->name.' by the adviser.',
            route('student.rooms')
        );
        Audit::log(auth()->user(), 'room_student_removed', 'room', $room->id, ['student_id' => $student->id]);

        return back()->with('success', $student->name.' was removed from the room.');
    }

    private function ownedRoom($roomId): ?Room
    {
        if (!is_numeric($roomId)) {
            return null;
        }

        return Room::with('subject')
            ->whereKey((int) $roomId)
            ->whereHas('subject', fn ($q) => $q->where('adviser_id', auth()->id()))
            ->first();
    }

    private function assertOwned(Room $room): void
    {
        $room->loadMissing('subject');
        abort_unless($room->subject && $room->subject->adviser_id === auth()->id(), 403);
    }
}
