@extends('layouts.app')
@section('title','Student AI Scores')
@section('topbar','Student AI Scores')
@section('content')
<div class="page-head">
    <div><h1>Student AI Scores</h1><p>View AI rubric recommendations and adviser-finalized grades for your own activities.</p></div>
    <a class="btn secondary" href="{{ route('adviser.activities.index') }}">Activities</a>
</div>
<div class="card filter-card">
    <form method="GET" class="filter-row">
        @include('partials.submission-subject-filter')
        <div class="field"><label>Filter by Room</label><select name="room_id"><option value="">All rooms</option>@foreach($rooms as $room)<option value="{{ $room->id }}" @selected(request('room_id')==$room->id)>{{ $room->name }} · {{ $room->section ?: 'No section' }}</option>@endforeach</select></div>
        <button class="btn sm" type="submit">Apply Filter</button>
        @if(request('room_id') || request('subject_id'))<a class="btn secondary sm" href="{{ route('adviser.scores') }}">Clear</a>@endif
    </form>
</div>
<br>
<div class="card table-wrap">
<table><thead><tr><th>Student</th><th>Activity</th><th>Room</th><th>AI Rubric Score</th><th>Final Grade</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($submissions as $s)
@if($loop->first || $submissions[$loop->index - 1]->activity->subject_id !== $s->activity->subject_id)
<tr class="submission-subject-heading"><th colspan="7" scope="colgroup">{{ $s->activity->subject->displayLabel() }}</th></tr>
@endif

<tr>
    <td><strong>{{ $s->student->name }}</strong><br><span class="small muted">{{ $s->student->school_id }}</span></td>
    <td>{{ $s->activity->title }}<br><span class="small muted">{{ $s->activity->subject->name }}</span></td>
    <td>{{ $s->activity->room?->name ?: 'General' }}<br><span class="small muted">{{ $s->activity->room?->section }}</span></td>
    <td>@if($s->latestAiEvaluation)<strong class="score-inline">{{ $s->latestAiEvaluation->recommended_score }} / {{ $s->activity->max_score }}</strong>@else<span class="muted">Not evaluated</span>@endif</td>
    <td>@if($s->grade)<strong>{{ $s->grade->final_score }} / {{ $s->activity->max_score }}</strong>@else<span class="muted">Not finalized</span>@endif</td>
    <td><span class="badge {{ $s->status==='graded'?'success':'gray' }}">{{ str_replace('_',' ',$s->status) }}</span></td>
    <td><a class="btn sm" href="{{ route('adviser.submissions.show',$s) }}">View Rubric Score</a></td>
</tr>
@empty<tr><td colspan="7" class="empty">No student submissions yet.</td></tr>@endforelse
</tbody></table>
{{ $submissions->links('pagination.simple') }}
</div>
@endsection
