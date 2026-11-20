@extends('layouts.app')
@section('title','AI Evaluation Monitor')
@section('content')
<div class="page-head">
    <div><h1>AI Evaluation Monitor</h1><p>Inspect Groq connection status, models, rubric assessment results, and failures.</p></div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a class="btn secondary sm" href="{{ route('admin.ai-settings') }}">AI Settings</a>
        <form method="POST" action="{{ route('admin.ai-monitor.test') }}">@csrf<button class="btn sm">Test AI Connection</button></form>
    </div>
</div>
@if($aiStatus['configured'] ?? false)
<div class="alert success"><strong>AI is configured.</strong> Provider: {{ strtoupper($aiStatus['provider'] ?? 'groq') }} · Model: {{ $aiModel }}. Test the connection before grading new work.</div>
@else
<div class="alert error"><strong>AI is not configured.</strong> Add your Groq API key in <a href="{{ route('admin.ai-settings') }}">AI Settings</a> or set <code>GROQ_API_KEY</code> in <code>.env</code>.</div>
@endif
<div class="card" style="margin-bottom:18px">
    <h3>Checker Configuration</h3>
    <p><strong>Requested provider:</strong> {{ strtoupper($aiStatus['requested_provider'] ?? 'groq') }}</p>
    <p><strong>Groq key:</strong> {{ ($aiStatus['groq_configured'] ?? false) ? 'Configured' : 'Not configured' }}</p>
    <p><strong>Active provider:</strong> {{ strtoupper($aiProvider ?? 'none') }}</p>
    <p><strong>Active model:</strong> {{ $aiModel ?? '-' }}</p>
    <p class="small muted">The key is never displayed after saving. AI evaluation uses the adviser instructions and rubric as the grading standard.</p>
</div>
<div class="alert warning">AI results are recommendations. The adviser or an audited administrator reviews the actual student work and finalizes the grade.</div>
<div class="card table-wrap">
<table>
<thead><tr><th>Date</th><th>Student / Activity</th><th>Provider</th><th>Recommended</th><th>Status</th><th>Error</th></tr></thead>
<tbody>
@forelse($evaluations as $e)
<tr>
<td>{{ $e->evaluated_at?->format('M d, Y h:i A') }}</td>
<td><strong>{{ $e->submission?->student?->name ?? '-' }}</strong><br><span class="small muted">{{ $e->submission?->activity?->title ?? '-' }} · {{ $e->submission?->activity?->subject?->name ?? '' }}</span></td>
<td>{{ strtoupper($e->provider ?? '-') }}<br><span class="small muted">{{ $e->model }}</span></td>
<td>{{ $e->recommended_score ?? '-' }}</td>
<td><span class="badge {{ $e->status==='completed'?'success':'danger' }}">{{ $e->status }}</span> @if($e->is_demo)<span class="badge warn">demo</span>@endif</td>
<td><span class="small">{{ Str::limit($e->error_message,120) }}</span></td>
</tr>
@empty<tr><td colspan="6" class="empty">No AI assessments have been run yet.</td></tr>@endforelse
</tbody>
</table>
{{ $evaluations->links('pagination.simple') }}
</div>
@endsection
