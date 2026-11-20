@extends('layouts.app')
@section('title','My Rooms')
@section('topbar','My Rooms & Sections')
@section('inline-errors', 'true')
@section('content')
<div class="page-head">
    <div><h1>My Rooms</h1><p>Join the correct class section using the unique enrollment key provided by your adviser.</p></div>
    <a class="btn secondary" href="{{ route('chat.index') }}">Open Adviser Chat</a>
</div>

<div class="grid grid-2">
    <div class="card join-panel">
        <h3>Join a Subject & Classroom</h3>
        <p class="muted small">Enter the enrollment key shared by your adviser to join its subject and classroom. Keys are not case-sensitive.</p>
        <form method="POST" action="{{ route('student.rooms.preview') }}" data-enrollment-submit data-pending-label="Checking enrollment key…">
            @csrf
            <div class="field"><label for="enrollment-key">Enrollment Key</label><input id="enrollment-key" class="key-input" name="enrollment_key" value="{{ old('enrollment_key') }}" placeholder="Example: ROOM-A7B9C2D4" maxlength="40" required autocomplete="off" spellcheck="false" @error('enrollment_key') aria-invalid="true" aria-describedby="enrollment-key-error" @enderror>
                @error('enrollment_key')<p id="enrollment-key-error" class="small" role="alert">{{ $message }}</p>@enderror
            </div>
            <button class="btn" type="submit" data-enrollment-button>Join Subject & Classroom</button>
            <p class="small muted" style="margin-bottom:0">You will review the subject and classroom before confirming your enrollment.</p>
        </form>
    </div>

    <div class="card hero-card">
        <h3>One Room = One Organized Section</h3>
        <p class="muted">Your room connects the correct adviser, section, activities, submissions, rubric scores, notifications, and private chat.</p>
        <div class="room-meta"><span class="badge info">Unique enrollment key</span><span class="badge info">Private adviser chat</span><span class="badge info">AI rubric scores</span></div>
    </div>
</div>

<br>
<div class="grid grid-3">
@forelse($enrollments as $enrollment)
    @php($room=$enrollment->room)
    @if($room)
    <div class="card room-card" data-live-room="{{ $room->id }}">
        <div class="room-card-top"><span class="badge {{ $room->active ? 'success' : 'gray' }}">{{ $room->active ? 'Active' : 'Closed' }}</span></div>
        <h3 style="margin:12px 0 5px" data-room-field="name">{{ $room->name }}</h3>
        <p class="muted small">{{ $room->subject->name }}</p>
        <div class="room-meta"><span class="badge gray" data-room-field="section">{{ $room->section ?: 'No section' }}</span><span class="badge gray" data-room-field="physical_room" @if(!$room->physical_room) hidden @endif>{{ $room->physical_room }}</span></div>
        <p class="small" data-room-optional="schedule" @if(!$room->schedule) hidden @endif><strong>Schedule:</strong> <span data-room-field="schedule">{{ $room->schedule }}</span></p>
        <p class="small"><strong>Adviser:</strong> {{ $room->subject->adviser->name }}</p>
        <details class="glass-dropdown" style="margin-top:13px">
            <summary><strong>Course Actions</strong><span class="small muted">View activities or contact adviser</span></summary>
            <div class="glass-dropdown-content">
                <div class="actions"><a class="btn sm" href="{{ route('student.activities.index', ['subject_id' => $room->subject_id]) }}">View Activities</a><a class="btn secondary sm" href="{{ route('chat.room',$room) }}">Message Adviser</a></div>
                <form method="POST" action="{{ route('student.rooms.leave',$room) }}" style="margin-top:12px" onsubmit="return confirm('Leave this classroom? You will lose access to its activities and chat until you enroll again.');">@csrf @method('DELETE')<button class="btn danger sm" type="submit">Leave Classroom</button></form>
            </div>
        </details>
    </div>
    @endif
@empty
    <div class="card" style="grid-column:1/-1"><div class="empty">You are not enrolled in a room yet. Ask your adviser for the enrollment key and enter it above.</div></div>
@endforelse
</div>
@endsection
