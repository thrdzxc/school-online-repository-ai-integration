@extends('layouts.app')
@section('title','Notifications')
@section('content')
<div class="page-head">
    <div><h1>Notifications</h1><p>Submission, revision, grading, and system updates.</p></div>
    <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn secondary sm">Mark all as read</button></form>
</div>
<div class="card">
@forelse($notifications as $n)
    <div class="score-line">
        <div class="notification-item-copy">
            <span class="notification-item-icon" aria-hidden="true">!</span>
            @if(!$n->read_at)<span class="notice-dot"></span>@endif
            <strong>{{ $n->title }}</strong><br>
            <span>{{ $n->message }}</span><br>
            <span class="small muted">{{ $n->created_at->diffForHumans() }}</span>
        </div>
        <form method="POST" action="{{ route('notifications.read',$n) }}">@csrf<button class="btn secondary sm">{{ $n->url ? 'Open' : 'Mark read' }}</button></form>
    </div>
@empty
    <div class="empty">No notifications.</div>
@endforelse
{{ $notifications->links('pagination.simple') }}
</div>
@endsection
