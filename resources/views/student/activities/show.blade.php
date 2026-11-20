@extends('layouts.app')
@section('title',$activity->title)
@section('content')
<div class="page-head"><div><h1>{{ $activity->title }}</h1><p>{{ $activity->room?->name ?: 'General' }} · {{ $activity->room?->section ?: 'No section' }} · {{ $activity->subject->displayLabel() }}</p></div><span class="badge">{{ $activity->max_score }} points</span></div>
<div class="grid grid-2">
    <div>
        <div class="card">
            <h3>Adviser Instructions</h3>
            @if(filled($activity->instructions))<div class="text-box">{{ $activity->instructions }}</div>@else<div class="empty">The activity instructions are provided in the uploaded reference file below.</div>@endif
            @if($activity->instruction_file_path)<p><strong>Reference file:</strong> {{ $activity->instruction_original_filename }} <a class="btn secondary sm" href="{{ route('student.activities.instruction.download',$activity) }}">Download</a></p>@endif
            <p class="small muted">Deadline: {{ optional($activity->due_at)->format('F d, Y h:i A') ?? 'No deadline' }} @if($activity->allow_late) · Late submission allowed @endif</p>
        </div>
        <br>
        <div class="card">
            <h3>Adviser Rubric</h3>
            <p class="small muted">Your submission will be checked using this rubric.</p>
            @if($activity->rubric_text)<div class="text-box">{{ $activity->rubric_text }}</div>@endif
            @if($activity->rubric_file_path)<p><strong>Rubric file:</strong> {{ $activity->rubric_original_filename }}</p><a class="btn secondary sm" href="{{ route('student.activities.rubric.download',$activity) }}">Download Rubric</a>@endif
        </div>
    </div>
    <div>
        <div class="card">
            <h3>Submit Your Work</h3>
            @php
                $latestSubmission = $submissions->first();
                $submissionClosed = $activity->due_at && now()->greaterThan($activity->due_at) && !$activity->allow_late;
            @endphp
            @if($latestSubmission)
                <div class="alert info">Your work has been submitted. Only one submission is allowed per activity; it cannot be replaced or resubmitted.</div>
            @elseif($submissionClosed)
                <div class="alert warning">The deadline has passed and late submissions are not allowed.</div>
            @else
            <form method="POST" action="{{ route('student.activities.submit',$activity) }}" enctype="multipart/form-data">@csrf
                @if($activity->allow_text)<div class="field"><label>Text Submission</label><textarea name="text_content" placeholder="Paste or write your answer here...">{{ old('text_content') }}</textarea></div>@endif
                @if($activity->allow_file)<div class="field"><label>Document</label><input type="file" name="file" accept=".pdf,.docx,.txt,.md,.pptx"><span class="small muted">PDF, DOCX, TXT, MD or PPTX up to 15 MB.</span></div>@endif
                <p class="small muted">Review your work carefully. You can submit this activity only once.</p><button class="btn">Submit Activity</button>
            </form>
            @endif
            <div class="alert warning" style="margin-top:16px">After submission, your adviser reviews your work and may use the AI checker. Scores appear after final approval.</div>
        </div>
        <br>
        <div class="card"><h3>Submission History</h3>@forelse($submissions as $s)<div class="score-line"><div><a href="{{ route('student.submissions.show',$s) }}"><strong>Version {{ $s->version }}</strong></a><br><span class="small muted">{{ $s->submitted_at->format('M d, Y h:i A') }}</span></div><div><span class="badge {{ $s->status==='graded'?'success':'gray' }}">{{ str_replace('_',' ',$s->status) }}</span>@if($s->grade && $s->latestAiEvaluation)<br><span class="small muted">AI:</span> <strong>{{ $s->latestAiEvaluation->recommended_score }}/{{ $activity->max_score }}</strong>@endif @if($s->grade)<br><span class="small muted">Final:</span> <strong>{{ $s->grade->final_score }}/{{ $activity->max_score }}</strong>@endif</div></div>@empty<div class="empty">No submission yet.</div>@endforelse</div>
    </div>
</div>
@endsection
