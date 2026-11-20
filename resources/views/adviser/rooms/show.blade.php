@extends('layouts.app')
@section('title','Manage Room')
@section('topbar','Room Management')
@section('content')
<div class="page-head">
    <div><h1>{{ $room->name }}</h1><p>{{ $room->subject->name }} · {{ $room->section ?: 'No section' }}</p></div>
    <div class="actions"><a class="btn secondary" href="{{ route('adviser.rooms.index') }}">Back to Rooms</a><a class="btn" href="{{ route('adviser.activities.create') }}">Create Activity</a></div>
</div>

<div class="grid grid-3">
    <div class="card room-card">
        <h3>Subject Enrollment Key</h3>
        <p class="small"><strong>{{ $room->subject->name }}</strong> · {{ $room->name }}</p>
        <div class="field"><label class="small muted" for="room-key">Share this key with your students</label><input class="key-input" id="room-key" value="{{ $room->enrollment_key }}" readonly style="margin-top:8px"></div>
        <p class="small muted">Students open My Rooms, enter this key, and click Join Subject & Classroom. Only the matching key allows them to join this subject's classroom.</p>
        <div class="actions">
            <button type="button" class="btn secondary sm" data-copy-enrollment-key="room-key">Copy Enrollment Key</button>
            <form method="POST" action="{{ route('adviser.rooms.regenerate-key',$room) }}">@csrf<button class="btn warn sm" onclick="return confirm('Generate a new key? The old key will stop working.')">New Key</button></form>
        </div>
        <p class="small muted" data-copy-key-status role="status" aria-live="polite"></p>
    </div>
    <div class="card"><span class="muted small">Students Enrolled</span><div class="metric">{{ $room->students->count() }}</div><p class="small muted">Capacity: {{ $room->capacity ?: 'No limit' }}</p></div>
    <div class="card"><span class="muted small">Room Status</span><div class="metric" style="font-size:24px">{{ $room->active ? 'Active' : 'Closed' }}</div><form method="POST" action="{{ route('adviser.rooms.toggle',$room) }}">@csrf<button class="btn {{ $room->active?'danger':'success' }} sm">{{ $room->active?'Close Enrollment':'Reopen Room' }}</button></form></div>
</div>
<br>
<details class="glass-dropdown" @if($errors->any()) open @endif>
    <summary><strong>Classroom Settings</strong><span class="small muted">Update name, section, schedule and capacity</span></summary>
    <form method="POST" action="{{ route('adviser.rooms.update',$room) }}" class="glass-dropdown-content">@csrf @method('PUT')
        @include('partials.room-edit-fields')
        <button class="btn" type="submit">Save Classroom Details</button>
    </form>
</details>
<br>
<div class="card">
    <div class="page-head"><div><h3>Student Roster</h3><p>Start a private chat or remove a student from this room.</p></div><a class="btn secondary sm" href="{{ route('chat.index') }}">All Chats</a></div>
    <div class="table-wrap"><table><thead><tr><th>Student</th><th>School ID</th><th>Course / Year</th><th>Section</th><th>Actions</th></tr></thead><tbody>
    @forelse($room->students as $student)
        <tr><td><strong>{{ $student->name }}</strong><br><span class="small muted">{{ $student->email }}</span></td><td>{{ $student->school_id ?: '-' }}</td><td>{{ trim(($student->course ?: '').' '.($student->year_level ?: '')) ?: '-' }}</td><td>{{ $student->section ?: '-' }}</td><td><div class="actions"><a class="btn sm" href="{{ route('chat.room.student',[$room,$student]) }}">Open Chat</a><form method="POST" action="{{ route('adviser.rooms.students.remove',[$room,$student]) }}" onsubmit="return confirm('Remove {{ addslashes($student->name) }} from this room?');">@csrf @method('DELETE')<button class="btn danger sm">Remove</button></form></div></td></tr>
    @empty
        <tr><td colspan="5" class="empty">No students have joined this room yet. Share the enrollment key above.</td></tr>
    @endforelse
    </tbody></table></div>
</div>
@endsection
