@extends('layouts.app')
@section('title','Submission Details')
@section('content')
<div class="page-head"><div><h1>{{ $submission->activity->title }}</h1><p>{{ $submission->activity->room?->name ?: 'General' }} · {{ $submission->activity->room?->section ?: 'No section' }} · {{ $submission->activity->subject->name }} · Version {{ $submission->version }}</p></div><span class="badge {{ $submission->status==='graded'?'success':'gray' }}">{{ str_replace('_',' ',$submission->status) }}</span></div>
<div class="grid grid-2">
    <div class="card"><h3>Your Submission</h3>@if($submission->text_content)<div class="text-box">{{ $submission->text_content }}</div>@endif @if($submission->file_path)<p><strong>File:</strong> {{ $submission->original_filename }}</p><a class="btn secondary sm" href="{{ route('student.submissions.download',$submission) }}">Download File</a>@endif<p class="small muted">Submitted {{ $submission->submitted_at->format('F d, Y h:i A') }} @if($submission->is_late) · Late @endif</p></div>
    <div class="card"><h3>Final Adviser Grade</h3>@if($submission->grade)<div class="score-big">{{ $submission->grade->final_score }} / {{ $submission->activity->max_score }}</div><p>{{ $submission->grade->adviser_comment ?: 'No adviser comment.' }}</p><span class="badge success">{{ $submission->grade->decision }}</span>@else<div class="empty">No final grade yet.</div>@endif</div>
</div>
<br>
@php($eval=$submission->latestAiEvaluation)
@if($submission->grade && $eval)
<div class="card">
    <div class="page-head"><div><h3>AI Rubric Recommendation</h3><p>{{ $eval->provider }} · {{ $eval->model }}</p></div><div class="score-big">{{ $eval->recommended_score ?? '-' }} / {{ $submission->activity->max_score }}</div></div>
    @if($eval->is_demo)<div class="alert warning"><strong>Demo result only.</strong> This was not produced by a real AI provider and must not be used as an academic grade.</div>@endif
    @forelse($eval->rubric_breakdown ?? [] as $row)<div class="score-line"><div><strong>{{ $row['name'] ?? 'Criterion' }}</strong><br><span class="small muted">{{ $row['feedback'] ?? '' }}</span></div><strong>{{ $row['score'] ?? 0 }}/{{ $row['max_points'] ?? 0 }}</strong></div>@empty<div class="empty">No rubric breakdown available.</div>@endforelse
    <h3 style="margin-top:20px">Summary</h3><p>{{ $eval->summary }}</p>
    <div class="grid grid-2"><div><strong>Strengths</strong><ul>@foreach($eval->strengths ?? [] as $x)<li>{{ $x }}</li>@endforeach</ul></div><div><strong>Improvements</strong><ul>@foreach($eval->improvements ?? [] as $x)<li>{{ $x }}</li>@endforeach</ul></div></div>
</div>
@endif
@endsection
