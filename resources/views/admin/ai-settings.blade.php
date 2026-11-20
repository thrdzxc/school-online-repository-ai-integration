@extends('layouts.app')
@section('title','AI Settings')
@section('topbar','AI Rubric Checker Settings')
@section('content')
<div class="page-head">
    <div><h1>AI Checker Settings</h1><p>Configure the Groq-compatible AI service used to assess student work against adviser instructions and rubrics.</p></div>
    <form method="POST" action="{{ route('admin.ai-monitor.test') }}">@csrf<button class="btn secondary">Test AI Connection</button></form>
</div>

<div class="grid grid-2">
    <div class="card">
        <h3>Groq API Configuration</h3>
        <form method="POST" action="{{ route('admin.ai-settings.save') }}">
            @csrf
            <div class="field">
                <label>Groq API Key</label>
                <input type="password" name="groq_api_key" autocomplete="new-password" placeholder="{{ ($status['configured'] ?? false) ? 'A key is already configured. Leave blank to keep it.' : 'Paste your Groq API key here' }}">
                <span class="small muted">The key is encrypted before it is stored in the local database. If GROQ_API_KEY is set in .env, the environment key takes priority.</span>
            </div>
            <div class="field">
                <label>AI Model</label>
                <input name="groq_model" value="{{ old('groq_model', $storedModel ?: 'openai/gpt-oss-20b') }}" required>
                <span class="small muted">Default configured model: openai/gpt-oss-20b. Use a model supported by your Groq account.</span>
            </div>
            <label class="inline"><input type="checkbox" name="clear_groq_key" value="1"> Remove the stored database API key</label>
            <br><br>
            <button class="btn">Save AI Settings</button>
        </form>
    </div>

    <div class="card">
        <h3>Current Status</h3>
        @if($status['configured'] ?? false)
            <div class="alert success"><strong>AI key detected.</strong> Use the connection test before grading.</div>
        @elseif($status['database_key_unreadable'] ?? false)
            <div class="alert error"><strong>Stored AI key cannot be decrypted.</strong> The Laravel APP_KEY may have changed. Enter the key again here.</div>
        @else
            <div class="alert warning"><strong>No AI key is configured yet.</strong> Room enrollment, chat, submissions, and notifications still work, but new AI assessments require a valid key.</div>
        @endif
        <p><strong>Provider:</strong> GROQ</p>
        <p><strong>Model:</strong> {{ $status['model'] ?? ($storedModel ?: 'openai/gpt-oss-20b') }}</p>
        <p><strong>Key source:</strong> {{ strtoupper($status['key_source'] ?? 'none') }}</p>
        <p><strong>Automatic checking:</strong> {{ config('services.ai.auto_evaluate') ? 'Enabled' : 'Disabled' }}</p>
        <hr>
        <p class="small muted">The checker reads the adviser instruction text/file, rubric text/file, and the student's text/file submission. It returns criterion scores, feedback, strengths, improvements, and a recommended score. The adviser remains responsible for the final academic grade.</p>
        <hr>
        <p><strong>Terminal alternative:</strong></p>
        <pre style="white-space:pre-wrap">php artisan groq:configure
php artisan groq:test
php artisan groq:status</pre>
    </div>
</div>
@endsection
