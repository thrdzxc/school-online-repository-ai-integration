@extends('layouts.app')
@section('title',$activity->title)
@section('content')
<div class="page-head">
    <div><h1>{{ $activity->title }}</h1><p>{{ $activity->subject->displayLabel() }} · Adviser: {{ $activity->adviser->name }}</p></div>
    <div class="actions"><span class="badge {{ $activity->status==='published'?'success':'gray' }}">{{ $activity->status }}</span><a class="btn sm" href="{{ route('admin.submissions') }}">All Submissions</a></div>
</div>
<div class="grid grid-2">
    <div class="card">
        <h3>Instructions & Files</h3>
        @if(filled($activity->instructions))
            <div class="text-box">{{ $activity->instructions }}</div>
        @else
            <div class="empty">Instructions are provided in the uploaded instruction/reference file.</div>
        @endif
        @if($activity->instruction_file_path)
            <p><strong>Instruction file:</strong> {{ $activity->instruction_original_filename }} <a class="btn secondary sm" href="{{ route('admin.activities.instruction.download',$activity) }}">Download</a></p>
        @endif
        <p class="small muted">Deadline: {{ optional($activity->due_at)->format('F d, Y h:i A') ?? 'No deadline' }} · {{ $activity->submissions_count }} submission(s)</p>
    </div>
    <div class="card">
        <h3>Adviser Rubric</h3>
        @if($activity->rubric_text)<div class="text-box">{{ $activity->rubric_text }}</div>@endif
        @if($activity->rubric_file_path)
            <p><strong>Rubric file:</strong> {{ $activity->rubric_original_filename }} <a class="btn secondary sm" href="{{ route('admin.activities.rubric.download',$activity) }}">Download Rubric</a></p>
        @endif
        <p class="small muted">Maximum score: {{ $activity->max_score }}</p>
    </div>
</div>
@endsection
