@extends('layouts.app')
@section('title',$activity->title)
@section('content')
<div class="page-head">
    <div><h1>{{ $activity->title }}</h1><p>{{ $activity->room?->name ?: 'General activity' }} · {{ $activity->room?->section ?: 'No section' }} · {{ $activity->subject->displayLabel() }}</p></div>
    <div class="actions">
        <span class="badge {{ $activity->status==='published'?'success':'gray' }}">{{ $activity->status }}</span>
        @if($activity->submissions_count===0)<a class="btn secondary sm" href="{{ route('adviser.activities.edit',$activity) }}">Edit Activity</a>@endif
        <form method="POST" action="{{ route('adviser.activities.toggle',$activity) }}">@csrf<button class="btn secondary sm">{{ $activity->status==='published'?'Move to Draft':'Publish' }}</button></form>
        <a class="btn sm" href="{{ route('adviser.activities.submissions',$activity) }}">View Submissions ({{ $activity->submissions_count }})</a>
    </div>
</div>
<div class="grid grid-2">
    <div class="card">
        <h3>Instructions for Students</h3>
        @if(filled($activity->instructions))<div class="text-box">{{ $activity->instructions }}</div>@else<div class="empty">Instructions are provided in the uploaded instruction/reference file.</div>@endif
        @if($activity->instruction_file_path)<p><strong>Instruction/reference file:</strong> {{ $activity->instruction_original_filename }} <a class="btn secondary sm" href="{{ route('adviser.activities.instruction.download',$activity) }}">Download</a></p>@endif
        <p class="small muted">Deadline: {{ optional($activity->due_at)->format('F d, Y h:i A') ?? 'No deadline' }}</p>
    </div>
    <div class="card">
        <h3>Rubric for Students</h3>
        @if($activity->rubric_text)<div class="text-box">{{ $activity->rubric_text }}</div>@else<div class="empty">Rubric is provided through the uploaded file.</div>@endif
        @if($activity->rubric_file_path)<p><strong>Rubric file:</strong> {{ $activity->rubric_original_filename }} <a class="btn secondary sm" href="{{ route('adviser.activities.rubric.download',$activity) }}">Download Rubric</a></p>@endif
        <p class="small muted">Maximum score: {{ $activity->max_score }}. The assessment reads this rubric text/file when checking each student submission.</p>
    </div>
</div>
@endsection
