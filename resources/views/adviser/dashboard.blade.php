@extends('layouts.app')
@section('title','Adviser Dashboard')
@section('topbar','Adviser Dashboard')
@section('content')
<div class="page-head">
    <div><h1>Adviser Dashboard</h1><p>Manage room sections, publish rubric-based activities, chat with students, review AI scores, and finalize grades.</p></div>
    <div class="actions"><a class="btn secondary" href="{{ route('adviser.rooms.index') }}">Rooms & Keys</a>@if($roomCount > 0)<a class="btn" href="{{ route('adviser.activities.create') }}">Create Activity</a>@else<a class="btn" href="{{ route('adviser.rooms.index') }}#create-subject">Create Subject & Classroom</a>@endif</div>
</div>
@if($subjects->isEmpty())
<div class="card">
    <h2>Welcome, {{ auth()->user()->name }}</h2>
    <p>Your adviser workspace is ready. Start by creating your first subject and classroom.</p>
    <ol><li>Create a subject and classroom.</li><li>Share the classroom enrollment key with your students.</li><li>Create activities and review submissions from your classroom.</li></ol>
    <a class="btn" href="{{ route('adviser.rooms.index') }}#create-subject">Set Up My First Subject</a>
</div>
@else
<div class="feature-banner"><div><span class="feature-kicker">FEATURES ACTIVE</span><h2>Room Enrollment + Student Chat</h2><p>Every section has a unique enrollment key. Chat is private per student and room, with notifications for new messages.</p></div><a class="btn secondary" href="{{ route('chat.index') }}">Open Student Chat</a></div>
@endif
<br>
<div class="grid grid-4">
    <div class="card"><span class="muted">My Rooms</span><div class="metric">{{ $roomCount }}</div><a class="small" href="{{ route('adviser.rooms.index') }}">Manage rooms →</a></div>
    <div class="card"><span class="muted">My Subjects</span><div class="metric">{{ $subjects->count() }}</div></div>
    <div class="card"><span class="muted">Pending Reviews</span><div class="metric">{{ $pending }}</div><a class="small" href="{{ route('adviser.scores') }}">View scores →</a></div>
    <div class="card"><span class="muted">Unread Chat</span><div class="metric">{{ $unreadChat }}</div><a class="small" href="{{ route('chat.index') }}">Open chat →</a></div>
</div>
<br>
<div class="card"><div class="page-head"><div><h3>Recent Student Submissions</h3><p>AI rubric scores are shown as soon as an evaluation completes.</p></div><a class="btn secondary sm" href="{{ route('adviser.scores') }}">All AI Scores</a></div><div class="table-wrap"><table><thead><tr><th>Student</th><th>Activity</th><th>Room</th><th>Status</th><th>AI Rubric Score</th><th></th></tr></thead><tbody>@forelse($recent as $s)<tr><td>{{ $s->student->name }}</td><td>{{ $s->activity->title }}<br><span class="small muted">{{ $s->activity->subject->name }}</span></td><td>{{ $s->activity->room?->name ?: 'General' }}<br><span class="small muted">{{ $s->activity->room?->section }}</span></td><td><span class="badge gray">{{ str_replace('_',' ',$s->status) }}</span></td><td>@if($s->latestAiEvaluation)<strong>{{ $s->latestAiEvaluation->recommended_score ?? '-' }}/{{ $s->activity->max_score }}</strong>@else<span class="muted">Pending</span>@endif</td><td><a class="btn sm" href="{{ route('adviser.submissions.show',$s) }}">Review</a></td></tr>@empty<tr><td colspan="6" class="empty">No submissions yet.</td></tr>@endforelse</tbody></table></div></div>
@endsection
