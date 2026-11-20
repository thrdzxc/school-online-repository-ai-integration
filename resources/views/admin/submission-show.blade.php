@extends('layouts.app')
@section('title','Submission Supervision')
@section('content')
<div class="page-head"><div><h1>Submission Supervision</h1><p>{{ $submission->student->name }} · {{ $submission->activity->title }} · Version {{ $submission->version }}</p></div><span class="badge gray">{{ str_replace('_',' ',$submission->status) }}</span></div>
<div class="grid grid-2">
    <div class="card"><h3>Student Output</h3>@if($submission->text_content)<div class="text-box">{{ $submission->text_content }}</div>@endif @if($submission->file_path)<p><strong>File:</strong> {{ $submission->original_filename }}</p><a class="btn secondary sm" href="{{ route('admin.submissions.download',$submission) }}">Download File</a>@endif<p class="small muted">Submitted {{ $submission->submitted_at->format('F d, Y h:i A') }}</p></div>
    <div class="card"><h3>Academic Context & Rubric</h3><p><strong>Subject:</strong> {{ $submission->activity->subject->displayLabel() }}</p><p><strong>Adviser:</strong> {{ $submission->activity->adviser->name }}</p><p><strong>Max score:</strong> {{ $submission->activity->max_score }}</p><h4>Instructions</h4>@if(filled($submission->activity->instructions))<div class="text-box">{{ $submission->activity->instructions }}</div>@else<div class="empty">Instructions are provided through the uploaded instruction/reference file.</div>@endif @if($submission->activity->instruction_file_path)<p><strong>Instruction file:</strong> {{ $submission->activity->instruction_original_filename }} <a class="btn secondary sm" href="{{ route('admin.activities.instruction.download',$submission->activity) }}">Download Instructions</a></p>@endif<h4>Rubric</h4>@if($submission->activity->rubric_text)<div class="text-box">{{ $submission->activity->rubric_text }}</div>@else<div class="empty">Rubric is provided through the uploaded rubric file.</div>@endif @if($submission->activity->rubric_file_path)<p><strong>Rubric file:</strong> {{ $submission->activity->rubric_original_filename }} <a class="btn secondary sm" href="{{ route('admin.activities.rubric.download',$submission->activity) }}">Download Rubric</a></p>@endif</div>
</div>
<br>
<div class="card">
    <div class="page-head"><div><h3>AI Rubric Assessment</h3><p>Admin can re-run the configured AI checker for troubleshooting or supervision.</p></div><form method="POST" action="{{ route('admin.submissions.ai',$submission) }}">@csrf<button class="btn">Run AI Rubric Assessment</button></form></div>
    @php($eval=$submission->aiEvaluations->first())
    @if($eval && $eval->status==='completed')
        @if($eval->is_demo)<div class="alert warning"><strong>DEMO MODE:</strong> no real AI academic scoring was performed.</div>@endif
        <div class="score-big">{{ $eval->recommended_score ?? '-' }} / {{ $submission->activity->max_score }}</div><p>{{ $eval->summary }}</p>
        @forelse($eval->rubric_breakdown ?? [] as $row)<div class="score-line"><div><strong>{{ $row['name'] ?? 'Criterion' }}</strong><br><span class="small muted">{{ $row['feedback'] ?? '' }}</span></div><strong>{{ $row['score'] ?? 0 }}/{{ $row['max_points'] ?? 0 }}</strong></div>@empty<div class="empty">No rubric breakdown available.</div>@endforelse
    @elseif($eval && $eval->status==='failed')<div class="alert error">{{ $eval->error_message }}</div>@else<div class="empty">No AI assessment yet.</div>@endif
</div>
@endsection
