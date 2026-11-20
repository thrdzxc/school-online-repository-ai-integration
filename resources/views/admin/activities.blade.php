@extends('layouts.app')
@section('title','All Activities')
@section('content')
<div class="page-head"><div><h1>All Activities</h1><p>Administrator view of every adviser task, rubric, and submission count.</p></div></div>
<div class="card table-wrap"><table><thead><tr><th>Activity</th><th>Subject</th><th>Adviser</th><th>Status</th><th>Submissions</th><th></th></tr></thead><tbody>
@forelse($activities as $a)<tr><td><strong>{{ $a->title }}</strong><br><span class="small muted">{{ optional($a->due_at)->format('M d, Y h:i A') ?? 'No deadline' }}</span></td><td>{{ $a->subject->name }}</td><td>{{ $a->adviser->name }}</td><td><span class="badge {{ $a->status==='published'?'success':'gray' }}">{{ $a->status }}</span></td><td>{{ $a->submissions_count }}</td><td><a class="btn secondary sm" href="{{ route('admin.activities.show',$a) }}">Open</a></td></tr>@empty<tr><td colspan="6" class="empty">No activities.</td></tr>@endforelse
</tbody></table>{{ $activities->links('pagination.simple') }}</div>
@endsection
