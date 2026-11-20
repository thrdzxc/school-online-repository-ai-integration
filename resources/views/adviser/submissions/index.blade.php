@extends('layouts.app')
@section('title','Student Submissions')
@section('topbar','Submission Scores')
@section('content')
<div class="page-head"><div><h1>{{ $activity->title }} - Submissions</h1><p>{{ $activity->room?->name ?: 'General' }} · {{ $activity->room?->section ?: 'No section' }} · {{ $activity->subject->name }} · AI scores follow your rubric and remain subject to adviser review.</p></div><a class="btn secondary" href="{{ route('adviser.activities.show',$activity) }}">Back to Activity</a></div>
<div class="card table-wrap"><table><thead><tr><th>Student</th><th>Version</th><th>Submitted</th><th>Status</th><th>AI Rubric Score</th><th>Final Grade</th><th></th></tr></thead><tbody>
@forelse($submissions as $s)
<tr><td><strong>{{ $s->student->name }}</strong><br><span class="small muted">{{ $s->student->school_id }}</span></td><td>{{ $s->version }}</td><td>{{ $s->submitted_at->format('M d, Y h:i A') }} @if($s->is_late)<span class="badge warn">late</span>@endif</td><td><span class="badge gray">{{ str_replace('_',' ',$s->status) }}</span></td><td>@if($s->latestAiEvaluation)<strong>{{ $s->latestAiEvaluation->recommended_score ?? '-' }}/{{ $activity->max_score }}</strong> @if($s->latestAiEvaluation->is_demo)<span class="badge warn">demo</span>@endif @else <span class="muted">Pending</span> @endif</td><td>{{ $s->grade?->final_score !== null ? $s->grade->final_score.'/'.$activity->max_score : '-' }}</td><td><a class="btn sm" href="{{ route('adviser.submissions.show',$s) }}">Review & Score</a></td></tr>
@empty<tr><td colspan="7" class="empty">No submissions.</td></tr>@endforelse
</tbody></table>{{ $submissions->links('pagination.simple') }}</div>
@endsection
