@extends('layouts.app')
@section('title','Subjects & Classrooms')
@section('topbar','Subjects & Classrooms')
@section('content')
<div class="page-head">
    <div><h1>Subjects & Classrooms</h1><p>Create and manage your own subjects and classrooms. Each adviser has a separate workspace.</p></div>
    <a class="btn secondary" href="{{ route('chat.index') }}">Student Messages</a>
</div>
<div class="card" id="create-subject" style="margin-bottom:18px">
    <h3>Create Subject & Classroom</h3>
    <p class="small muted">Set up your subject and first classroom together, then share the enrollment key with students.</p>
    <form method="POST" action="{{ route('adviser.subjects.store') }}">@csrf
        @include('partials.subject-classroom-fields')
        <button class="btn" type="submit">Create Subject & Classroom</button>
    </form>
</div>
<div class="page-head" style="margin-bottom:15px"><div><h2>My Subjects</h2><p>Edit details without changing existing enrollments.</p></div><span class="badge gray">{{ $subjects->count() }} active subjects</span></div>
<div class="adviser-subject-list">
@forelse($subjects as $subject)
    <details class="glass-dropdown adviser-subject-panel" @if(old('subject_id') == $subject->id) open @endif>
        <summary><span><strong>{{ $subject->name }}</strong><span class="small muted" style="display:block">{{ $subject->school_year }} · {{ $subject->semester }}</span></span><span class="badge gray">Edit details</span></summary>
        <div class="subject-editor-layout">
        <form class="subject-editor-form" method="POST" action="{{ route('adviser.subjects.update',$subject) }}">@csrf @method('PUT')
            <div class="subject-section-heading"><h3>Subject Information</h3><p>Update the subject details and academic schedule.</p></div>
            <div class="grid grid-2">
            <div class="field"><label>Course / Class No. (optional)</label><input name="class_code" value="{{ $subject->class_code }}" maxlength="30"></div>
            <div class="field"><label>Subject Name</label><input name="name" pattern="\p{L}[\p{L}\p{M} ]*" title="Use letters and spaces only." value="{{ $subject->name }}" maxlength="180" required></div>
            </div>
            <div class="field"><label>Description</label><textarea name="description" rows="3">{{ $subject->description }}</textarea></div>
            <div class="grid grid-2"><div class="field"><label>School Year</label><input name="school_year" value="{{ $subject->school_year }}" required></div><div class="field"><label>Semester</label><input name="semester" value="{{ $subject->semester }}" required></div></div>
            <div class="grid grid-2"><div class="field"><label>Section (optional)</label><input name="section" value="{{ $subject->section }}"></div><div class="field"><label>Units</label><input name="units" type="number" min="0" max="20" step="0.5" value="{{ $subject->units }}"></div></div>
            <div class="grid grid-2"><div class="field"><label>Schedule (optional)</label><input name="schedule" value="{{ $subject->schedule }}"></div><div class="field"><label>Room (optional)</label><input name="room" pattern="[\p{L}0-9][\p{L}\p{M}0-9 ]*" title="Use letters, numbers, and spaces only." value="{{ $subject->room }}"></div></div>
            <div class="subject-form-actions"><button class="btn sm" type="submit">Save Subject Changes</button></div>
        </form>
        <div class="subject-classroom-panel">
            <div class="subject-section-heading"><h3>Add Another Classroom</h3><p>Create a section with its own enrollment key.</p></div>
            <form method="POST" action="{{ route('adviser.rooms.store') }}">@csrf
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <div class="grid grid-2"><div class="field"><label>Classroom Name</label><input placeholder="COMPLAB" list="classroom-name-suggestions" name="name" pattern="\p{L}[\p{L}\p{M} ]*" title="Use letters and spaces only." value="{{ old('subject_id') == $subject->id ? old('name') : '' }}" maxlength="120" required></div><div class="field"><label>Section</label><input name="section" value="{{ old('subject_id') == $subject->id ? old('section') : '' }}" maxlength="80"></div></div>
                <div class="grid grid-2"><div class="field"><label>Physical Room</label><input placeholder="OS304" name="physical_room" pattern="[\p{L}0-9][\p{L}\p{M}0-9 ]*" title="Use letters, numbers, and spaces only." value="{{ old('subject_id') == $subject->id ? old('physical_room') : '' }}" maxlength="80"></div><div class="field"><label>Capacity</label><input type="number" name="capacity" value="{{ old('subject_id') == $subject->id ? old('capacity') : '' }}" min="1" max="500"></div></div>
                <div class="field"><label>Schedule</label><input name="schedule" value="{{ old('subject_id') == $subject->id ? old('schedule') : '' }}" maxlength="120"></div>
                <div class="subject-form-actions"><button class="btn secondary sm" type="submit">Add Classroom & Key</button></div>
            </form>
        </div>
        </div>
    </details>
@empty
    <div class="card"><div class="empty">No subjects yet. Create your subject and classroom using the form above.</div></div>
@endforelse
</div>
<div class="page-head" style="margin-bottom:15px"><div><h2>Classrooms & Enrollment Keys</h2><p>Each classroom belongs to a subject. Students join using its unique key.</p></div></div>
<div class="grid grid-3">
@forelse($rooms as $room)
    <div class="card room-card">
        <div class="room-card-top"><span class="badge {{ $room->active ? 'success' : 'gray' }}">{{ $room->active ? 'Active' : 'Closed' }}</span><span class="small muted">{{ $room->students_count }} students</span></div>
        <h3 style="margin-top:12px">{{ $room->name }}</h3>
        <p class="small muted">{{ $room->subject->name }} · {{ $room->section ?: 'No section' }}</p>
        <div class="room-key">{{ $room->enrollment_key }}</div>
        <div class="room-meta">@if($room->physical_room)<span class="badge gray">{{ $room->physical_room }}</span>@endif @if($room->schedule)<span class="badge gray">{{ $room->schedule }}</span>@endif</div>
        <div class="actions" style="margin-top:15px"><a class="btn sm" href="{{ route('adviser.rooms.show',$room) }}">Manage Classroom</a><a class="btn secondary sm" href="{{ route('adviser.rooms.edit',$room) }}">Edit Classroom</a></div>
    </div>
@empty
    <div class="card" style="grid-column:1/-1"><div class="empty">No classrooms yet. Create a subject and classroom above, or add a classroom under an existing subject.</div></div>
@endforelse
</div>
{{ $rooms->links('pagination.simple') }}
@endsection
