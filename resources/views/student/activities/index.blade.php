@extends('layouts.app')
@section('title','Activities')
@section('topbar','Room Activities')
@section('content')
<div class="page-head"><div><h1>Activities</h1><p>You only see published activities assigned to rooms/sections where you are enrolled.</p></div><a class="btn secondary" href="{{ route('student.rooms') }}">My Rooms</a></div>
<form method="GET" action="{{ route('student.activities.index') }}" class="card" style="margin-bottom:18px">
    <div class="field"><label for="activity-subject">Subject</label><select id="activity-subject" name="subject_id">
        <option value="">All enrolled subjects</option>
        @foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((int) $subjectId === $subject->id)>{{ $subject->name }}</option>@endforeach
    </select></div><button class="btn" type="submit">Show Activities</button>
</form>
<div class="card table-wrap"><table><thead><tr><th>Activity</th><th>Room / Section</th><th>Subject</th><th>Deadline</th><th>Max Score</th><th></th></tr></thead><tbody>
@forelse($activities as $activity)
<tr><td><strong>{{ $activity->title }}</strong><br><span class="small muted">{{ filled($activity->instructions) ? Str::limit($activity->instructions,90) : ($activity->instruction_file_path ? 'Instructions provided in uploaded file.' : 'No instructions provided.') }}</span></td><td>{{ $activity->room?->name ?: 'General' }}<br><span class="small muted">{{ $activity->room?->section }}</span></td><td>{{ $activity->subject->name }}</td><td>{{ optional($activity->due_at)->format('M d, Y h:i A') ?? 'No deadline' }}</td><td>{{ $activity->max_score }}</td><td><a class="btn sm" href="{{ route('student.activities.show',$activity) }}">Open Activity</a></td></tr>
@empty<tr><td colspan="6" class="empty">No activities are available in your enrolled rooms.</td></tr>@endforelse
</tbody></table>{{ $activities->links('pagination.simple') }}</div>
@endsection
