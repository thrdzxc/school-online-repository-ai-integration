@extends('layouts.app')
@section('title','Student Dashboard')
@section('topbar','Student Dashboard')
@section('content')
<div class="page-head">
    <div><h1>Welcome, {{ auth()->user()->name }}</h1><p>Your rooms, activities, private adviser chat, notifications, submissions, and published grades are all in one place.</p></div>
    <div class="actions"><a class="btn secondary" href="{{ route('student.rooms') }}">My Rooms</a><a class="btn" href="{{ route('chat.index') }}">Adviser Chat @if($unreadChat) ({{ $unreadChat }}) @endif</a></div>
</div>
<div class="feature-banner"><div><span class="feature-kicker">FEATURES ACTIVE</span><h2>Rooms + Chat + AI Scores</h2><p>Join a section using a room key, message your adviser, submit activities, and view adviser-approved feedback.</p></div><a class="btn secondary" href="{{ route('student.grades') }}">View Grades</a></div>
<br>
<div class="grid grid-4">
    <div class="card"><span class="muted">My Rooms</span><div class="metric">{{ $roomCount }}</div><a class="small" href="{{ route('student.rooms') }}">View sections →</a></div>
    <div class="card"><span class="muted">Active Activities</span><div class="metric">{{ $activities->count() }}</div><a class="small" href="{{ route('student.activities.index') }}">Open activities →</a></div>
    <div class="card"><span class="muted">Unread Chat</span><div class="metric">{{ $unreadChat }}</div><a class="small" href="{{ route('chat.index') }}">Open chat →</a></div>
    <div class="card"><span class="muted">Unread Notifications</span><div class="metric">{{ $notifications->whereNull('read_at')->count() }}</div><a class="small" href="{{ route('notifications.index') }}">View notices →</a></div>
</div>
<br>
<div class="grid grid-2">
    <div class="card"><h3>Upcoming Room Activities</h3>@forelse($activities as $activity)<div class="score-line"><div><a href="{{ route('student.activities.show',$activity) }}"><strong>{{ $activity->title }}</strong></a><br><span class="small muted">{{ $activity->room?->name ?: 'General' }} · {{ $activity->subject->name }} · Due {{ optional($activity->due_at)->format('M d, Y h:i A') ?? 'No deadline' }}</span></div><span class="badge">{{ $activity->max_score }} pts</span></div>@empty<div class="empty">No active activities in your rooms.</div>@endforelse</div>
    <div class="card"><h3>Recent Submissions & AI Score</h3>@forelse($latestSubmissions as $submission)<div class="score-line"><div><a href="{{ route('student.submissions.show',$submission) }}"><strong>{{ $submission->activity->title }}</strong></a><br><span class="small muted">{{ $submission->activity->room?->name ?: 'General' }} · Version {{ $submission->version }}</span></div><div style="text-align:right">@if($submission->grade && $submission->latestAiEvaluation)<strong>{{ $submission->latestAiEvaluation->recommended_score }}/{{ $submission->activity->max_score }}</strong><br><span class="small muted">AI rubric</span>@else<span class="badge {{ $submission->status==='graded'?'success':'gray' }}">{{ str_replace('_',' ',$submission->status) }}</span>@endif</div></div>@empty<div class="empty">No submissions yet.</div>@endforelse</div>
</div>
@endsection
