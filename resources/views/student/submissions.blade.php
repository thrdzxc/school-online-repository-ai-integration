@extends('layouts.app')
@section('title','My Submissions')
@section('topbar','My Submissions & Scores')
@section('content')
<div class="page-head"><div><h1>My Submissions</h1><p>Track your submitted work, AI rubric score, and final adviser grade.</p></div><a class="btn secondary" href="{{ route('student.grades') }}">AI Scores & Grades</a></div>
<div class="card table-wrap"><table><thead><tr><th>Activity</th><th>Room / Section</th><th>Version</th><th>Submitted</th><th>Status</th><th>AI Score</th><th>Final Grade</th><th></th></tr></thead><tbody>
@forelse($submissions as $s)
<tr>
<td><strong>{{ $s->activity->title }}</strong><br><span class="small muted">{{ $s->activity->subject->name }}</span></td>
<td>{{ $s->activity->room?->name ?: 'General' }}<br><span class="small muted">{{ $s->activity->room?->section }}</span></td>
<td>{{ $s->version }}</td>
<td>{{ $s->submitted_at->format('M d, Y h:i A') }}</td>
<td><span class="badge {{ $s->status==='graded'?'success':'gray' }}">{{ str_replace('_',' ',$s->status) }}</span></td>
<td>@if($s->grade && $s->latestAiEvaluation)<strong>{{ $s->latestAiEvaluation->recommended_score }}/{{ $s->activity->max_score }}</strong>@else<span class="muted">Pending</span>@endif</td>
<td>@if($s->grade)<strong>{{ $s->grade->final_score }}/{{ $s->activity->max_score }}</strong>@else<span class="muted">Pending</span>@endif</td>
<td><a class="btn sm" href="{{ route('student.submissions.show',$s) }}">View Details</a></td>
</tr>
@empty<tr><td colspan="8" class="empty">No submissions yet.</td></tr>@endforelse
</tbody></table>{{ $submissions->links('pagination.simple') }}</div>
@endsection
