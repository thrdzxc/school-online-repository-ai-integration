@extends('layouts.app')
@section('title','Review Submission')
@section('content')
<div class="page-head">
    <div><h1>Review: {{ $submission->student->name }}</h1><p>{{ $submission->activity->title }} · {{ $submission->activity->room?->name ?: 'General' }} · {{ $submission->activity->room?->section ?: 'No section' }} · Version {{ $submission->version }} · {{ $submission->submitted_at->format('M d, Y h:i A') }}</p></div>
    <span class="badge gray">{{ str_replace('_',' ',$submission->status) }}</span>
</div>

<div class="grid grid-2">
    <div class="card">
        <h3>Student Output</h3>
        @if($submission->text_content)<div class="text-box">{{ $submission->text_content }}</div>@endif
        @if($submission->file_path)<p><strong>Uploaded:</strong> {{ $submission->original_filename }}</p><a class="btn secondary sm" href="{{ route('adviser.submissions.download',$submission) }}">Download File</a>@endif
        @if(!$submission->text_content && !$submission->file_path)<div class="empty">No submission content.</div>@endif
    </div>

    <div class="card">
        <h3>Adviser Instructions & Rubric</h3>
        <h4>Instructions</h4>
        @if(filled($submission->activity->instructions))
            <div class="text-box">{{ $submission->activity->instructions }}</div>
        @else
            <div class="empty">Instructions were provided through the uploaded file.</div>
        @endif
        @if($submission->activity->instruction_file_path)
            <p><strong>Instruction/reference file:</strong> {{ $submission->activity->instruction_original_filename }} <a class="btn secondary sm" href="{{ route('adviser.activities.instruction.download',$submission->activity) }}">Download</a></p>
        @endif

        <h4>Rubric</h4>
        @if(filled($submission->activity->rubric_text))<div class="text-box">{{ $submission->activity->rubric_text }}</div>@else<div class="empty">Rubric was provided through the uploaded file.</div>@endif
        @if($submission->activity->rubric_file_path)
            <p><strong>Rubric file:</strong> {{ $submission->activity->rubric_original_filename }} <a class="btn secondary sm" href="{{ route('adviser.activities.rubric.download',$submission->activity) }}">Download Rubric</a></p>
        @endif
        <p class="small muted">Maximum score: {{ $submission->activity->max_score }}</p>
    </div>
</div>

<br>
<div class="card">
    <div class="page-head">
        <div>
            <h3>AI Rubric Assessment</h3>
            <p>The AI service reads the adviser instructions, rubric, and student submission, then returns criterion scores and feedback.</p>
        </div>
        @if($aiStatus['configured'] ?? false)
            <form method="POST" action="{{ route('adviser.submissions.ai',$submission) }}">@csrf<button class="btn">Run AI Rubric Assessment</button></form>
        @else
            <button class="btn" disabled title="The administrator must configure the AI service first">AI Not Configured</button>
        @endif
    </div>

    @if(!($aiStatus['configured'] ?? false))
        <div class="alert warning"><strong>AI setup is required.</strong> The administrator can configure it from <strong>Admin &gt; AI Settings</strong> or from the project terminal with <code>php artisan groq:configure</code>, then verify it with <code>php artisan groq:test</code>. Refresh this page after the connection succeeds; the <strong>Run AI Rubric Assessment</strong> button will become available.</div>
    @else
        <div class="alert success"><strong>AI service is configured.</strong> Model: {{ $aiStatus['model'] ?? 'openai/gpt-oss-20b' }}.</div>
    @endif

    @php($eval=$submission->latestAiEvaluation)
    @php($latestAttempt=$submission->aiEvaluations->first())
    @if($latestAttempt && $latestAttempt->status==='failed')<div class="alert warning"><strong>Latest AI attempt failed.</strong> {{ $latestAttempt->error_message }} You can retry, or grade manually.</div>@endif
    @if($eval && $eval->status==='completed')
        <div class="score-big">{{ $eval->recommended_score }} / {{ $submission->activity->max_score }}</div>
        <p>{{ $eval->summary }}</p>
        @forelse($eval->rubric_breakdown ?? [] as $row)
            <div class="score-line"><div><strong>{{ $row['name'] ?? 'Criterion' }}</strong><br><span class="small muted">{{ $row['feedback'] ?? '' }}</span></div><strong>{{ $row['score'] ?? 0 }}/{{ $row['max_points'] ?? 0 }}</strong></div>
        @empty
            <div class="empty">No rubric breakdown was returned.</div>
        @endforelse
        <div class="grid grid-2" style="margin-top:18px">
            <div><strong>Strengths</strong><ul>@forelse($eval->strengths ?? [] as $x)<li>{{ $x }}</li>@empty<li class="muted">None listed.</li>@endforelse</ul></div>
            <div><strong>Improvements</strong><ul>@forelse($eval->improvements ?? [] as $x)<li>{{ $x }}</li>@empty<li class="muted">None listed.</li>@endforelse</ul></div>
        </div>
        <p class="small muted">Provider: {{ strtoupper($eval->provider ?? 'AI') }} · Model: {{ $eval->model }} @if($eval->confidence !== null) · Confidence: {{ round(((float)$eval->confidence)*100) }}% @endif</p>
    @else
        <div class="empty">No AI assessment has been completed yet.</div>
    @endif
</div>

<br>
<div class="card">
    <h3>Adviser Decision</h3>
    <div class="alert warning">AI scores are optional recommendations. Review the work and rubric yourself before publishing a grade. Manual grading remains available when Groq is offline.</div>
    <form method="POST" action="{{ route('adviser.submissions.grade',$submission) }}">@csrf
        <div class="grid grid-2">
            <div class="field"><label>Final Score (max {{ $submission->activity->max_score }})</label><input type="number" name="final_score" id="final_score" step="0.01" min="0" max="{{ $submission->activity->max_score }}" value="{{ old('final_score',$submission->grade?->final_score ?? $eval?->recommended_score) }}" required></div>
            <div class="field"><label>Decision</label><select name="decision" id="grade_decision"><option value="approved">Approve score</option><option value="modified">Modify score</option></select></div>
        </div>
        <div class="field"><label>Adviser Comment</label><textarea name="adviser_comment">{{ old('adviser_comment',$submission->grade?->adviser_comment) }}</textarea></div>
        <button class="btn success">Save Decision</button>
    </form>
    @if($submission->grade)<p class="small muted">Finalized by {{ $submission->grade->finalizedBy?->name ?? 'Adviser' }} on {{ $submission->grade->finalized_at?->format('M d, Y h:i A') }}</p>@endif
</div>
@endsection
