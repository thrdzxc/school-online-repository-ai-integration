@extends('layouts.app')
@section('title','Repository Item')
@section('content')
<div class="page-head"><div><h1>{{ $item->title }}</h1><p>{{ $item->user->name }} · {{ $item->subject?->name ?? 'General Repository' }}</p></div><a class="btn secondary sm" href="{{ route('admin.repository') }}">Back to Repository</a></div>
<div class="card">
    @if($item->description)<p>{{ $item->description }}</p>@endif
    @if($item->text_content)<h3>Stored Text</h3><div class="text-box">{{ $item->text_content }}</div>@endif
    @if($item->file_path)<p><strong>Uploaded file:</strong> {{ $item->original_filename }}</p><a class="btn" href="{{ route('admin.repository.download',$item) }}">Download File</a>@endif
    <p class="small muted">Stored {{ $item->created_at->format('F d, Y h:i A') }}</p>
</div>
@endsection
