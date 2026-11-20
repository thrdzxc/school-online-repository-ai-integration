@extends('layouts.app')
@section('title','AI Scores & Grades')
@section('topbar','AI Scores & Adviser Grades')
@section('content')
<div class="page-head">
    <div><h1>AI Scores & Grades</h1><p>View scores and feedback after your adviser publishes the final grade.</p></div>
    <a class="btn secondary" href="{{ route('student.submissions.index') }}">My Submissions</a>
</div>
<div class="score-note"><strong>How scoring works:</strong> the AI reads the adviser’s instructions, rubric, and your submission. AI suggestions are reviewed privately by the adviser before the final grade is released.</div>
<br>
<div class="card table-wrap">
<table>
    <thead><tr><th>Activity</th><th>Room / Section</th><th>Subject</th><th>AI Rubric Score</th><th>Final Adviser Grade</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($submissions as $s)
        @php($ai=$s->grade ? $s->latestAiEvaluation : null)
        <tr>
            <td><strong>{{ $s->activity->title }}</strong><br><span class="small muted">Submitted {{ $s->submitted_at->format('M d, Y') }}</span></td>
            <td>{{ $s->activity->room?->name ?: 'General' }}<br><span class="small muted">{{ $s->activity->room?->section }}</span></td>
            <td>{{ $s->activity->subject->name }}</td>
            <td>@if($ai)<strong class="score-inline">{{ $ai->recommended_score ?? '-' }} / {{ $s->activity->max_score }}</strong>@if($ai->is_demo)<span class="badge warn">demo</span>@endif @else<span class="muted">Awaiting final grade</span>@endif</td>
            <td>@if($s->grade)<strong>{{ $s->grade->final_score }} / {{ $s->activity->max_score }}</strong>@else<span class="muted">Not finalized</span>@endif</td>
            <td><span class="badge {{ $s->status==='graded'?'success':'gray' }}">{{ str_replace('_',' ',$s->status) }}</span></td>
            <td><a class="btn sm" href="{{ route('student.submissions.show',$s) }}">View Rubric</a></td>
        </tr>
    @empty<tr><td colspan="7" class="empty">No submissions or AI scores yet.</td></tr>@endforelse
    </tbody>
</table>
{{ $submissions->links('pagination.simple') }}
</div>
@endsection
