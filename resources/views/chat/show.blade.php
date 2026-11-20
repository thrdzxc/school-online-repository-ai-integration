@extends('layouts.app')
@section('title','Chat Conversation')
@section('topbar','Online Chat')
@section('body-class','chat-page')
@section('content')
@php($other=auth()->id()===$conversation->student_id ? $conversation->adviser : $conversation->student)
<div class="page-head">
    <div><h1>{{ $other->name }}</h1><p>{{ $conversation->room->name }} · {{ $conversation->room->subject->name }}</p></div>
    <a class="btn secondary" href="{{ route('chat.index') }}">All Conversations</a>
</div>

<div class="card chat-card" data-chat data-messages-url="{{ route('chat.messages',$conversation) }}">
    <div class="chat-header"><div><strong>{{ $other->name }}</strong><div class="small muted">{{ auth()->id()===$conversation->student_id ? 'Adviser' : 'Student' }} · Private room chat</div></div><span class="badge chat-connection" data-chat-connection role="status">Connecting&hellip;</span></div>
    <div class="chat-messages" id="chat-messages" role="log" aria-label="Conversation messages" aria-live="polite" aria-relevant="additions" tabindex="0">
        @forelse($conversation->messages as $message)
            <div class="message-row {{ $message->sender_id===auth()->id() ? 'mine' : '' }}" data-message-id="{{ $message->id }}"><div class="message-bubble"><div class="message-body">{{ $message->body }}</div><div class="message-meta">{{ $message->sender->name }} · {{ $message->created_at->format('M d, h:i A') }}</div></div></div>
        @empty
            <div class="empty" id="chat-empty">No messages yet. Send the first message.</div>
        @endforelse
    </div>
    <form class="chat-compose" method="POST" action="{{ route('chat.send',$conversation) }}" data-chat-form>
        @csrf
        <div class="field">
            <label for="chat-body">Your message</label>
            <textarea id="chat-body" name="body" maxlength="3000" placeholder="Type your message..." aria-describedby="chat-hint chat-error" required>{{ old('body') }}</textarea>
        </div>
        <button class="btn" type="submit" data-chat-send><x-icon name="submission" size="17" /><span>Send message</span></button>
    </form>
    <div class="chat-feedback">
        <p class="small muted" id="chat-hint">Enter to send &middot; Shift + Enter for a new line &middot; Up to 3,000 characters</p>
        <p class="small chat-error" id="chat-error" role="alert" hidden></p>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/chat.js') }}?v={{ filemtime(public_path('js/chat.js')) }}" defer></script>
@endpush
