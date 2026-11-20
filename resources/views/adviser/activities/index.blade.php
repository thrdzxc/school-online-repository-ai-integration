@extends('layouts.app')
@section('title','Activities & Rubrics')
@section('topbar','Room Activities & Rubrics')
@section('content')
<div class="page-head"><div><h1>Activities & Rubrics</h1><p>Every activity is assigned to a room/section so submissions stay organized.</p></div><a class="btn" href="{{ route('adviser.activities.create') }}">New Activity</a></div>
<div class="card table-wrap"><table><thead><tr><th>Activity</th><th>Room / Section</th><th>Subject</th><th>Status</th><th>Deadline</th><th>Submissions</th><th></th></tr></thead><tbody>
@forelse($activities as $a)
<tr><td><strong>{{ $a->title }}</strong></td><td>{{ $a->room?->name ?: 'General' }}<br><span class="small muted">{{ $a->room?->section }}</span></td><td>{{ $a->subject->name }}</td><td><span class="badge {{ $a->status==='published'?'success':'gray' }}">{{ $a->status }}</span></td><td>{{ optional($a->due_at)->format('M d, Y h:i A') ?? 'None' }}</td><td>{{ $a->submissions_count }}</td><td><div class="actions"><a class="btn sm" href="{{ route('adviser.activities.show',$a) }}">Open</a><a class="btn secondary sm" href="{{ route('adviser.activities.submissions',$a) }}">Submissions</a></div></td></tr>
@empty<tr><td colspan="7" class="empty">No activities yet.</td></tr>@endforelse
</tbody></table>{{ $activities->links('pagination.simple') }}</div>
@endsection
