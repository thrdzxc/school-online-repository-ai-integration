@extends('layouts.app')
@section('title','Chat')
@section('topbar','Online Chat')
@section('content')
<div class="page-head">
    <div><h1>{{ auth()->user()->isStudent() ? 'Adviser Chat' : 'Student Chat' }}</h1><p>Private conversations connected to your enrolled rooms and sections.</p></div>
</div>

<div class="chat-layout">
    <div class="card chat-list">
        <div class="chat-list-head"><strong>Conversations</strong><div class="small muted">{{ $conversations->count() }} conversation(s)</div></div>
        @forelse($conversations as $conversation)
            @php($other=auth()->user()->isStudent() ? $conversation->adviser : $conversation->student)
            <a class="conversation-item" href="{{ route('chat.show',$conversation) }}">
                <div class="conversation-title"><strong>{{ $other->name }}</strong>@if(($unread[$conversation->id] ?? collect())->count())<span class="badge info">{{ ($unread[$conversation->id] ?? collect())->count() }} new</span>@endif</div>
                <div class="small muted">{{ $conversation->room->name }}</div>
                <div class="conversation-preview">{{ $conversation->latestMessage?->body ?: 'Start the conversation.' }}</div>
            </a>
        @empty
            <div class="empty">No chat conversation yet.</div>
        @endforelse
    </div>

    <div class="card">
        @if(auth()->user()->isStudent())
            <h3>Start a Conversation</h3>
            <p class="muted small">Choose one of your enrolled rooms to talk directly with its adviser.</p>
            @forelse($rooms as $room)
                <div class="score-line"><div><strong>{{ $room->name }}</strong><br><span class="small muted">{{ $room->subject->adviser->name }} · {{ $room->subject->name }}</span></div><a class="btn sm" href="{{ route('chat.room',$room) }}">Open Chat</a></div>
            @empty
                <div class="empty">Join a room first before starting an adviser chat.</div>
            @endforelse
        @else
            <h3>Student Conversations</h3>
            <p class="muted">Open a conversation from the list, or go to <strong>Rooms & Sections</strong> and choose a student from the room roster to start a chat.</p>
            <a class="btn" href="{{ route('adviser.rooms.index') }}">Go to Rooms & Sections</a>
        @endif
    </div>
</div>
@endsection
