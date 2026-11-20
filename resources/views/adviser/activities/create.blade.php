@extends('layouts.app')
@section('title','Create Activity')
@section('topbar','Create Room Activity')
@section('content')
<div class="page-head">
    <div><h1>Create Activity & Rubric</h1><p>Choose the room/section first. Only students enrolled in that room will receive this activity.</p></div>
    <a class="btn secondary" href="{{ route('adviser.rooms.index') }}">Manage Rooms</a>
</div>
<form method="POST" action="{{ route('adviser.activities.store') }}" enctype="multipart/form-data">
@csrf
<div class="grid grid-2">
    <div class="card">
        <h3>Activity Details</h3>
        @if($rooms->isEmpty())
            <div class="alert warning"><strong>No active room found.</strong> <a href="{{ route('adviser.rooms.index') }}#create-subject">Create your subject and classroom</a> first, then return here to post an activity.</div>
        @endif
        <div class="field">
            <label>Room / Section</label>
            <select name="room_id" required>
                <option value="">Select room</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" @selected(old('room_id')==$room->id)>{{ $room->name }} · {{ $room->section ?: 'No section' }} · {{ $room->subject->name }}</option>
                @endforeach
            </select>
            <span class="small muted">The room determines the subject and which students can see the activity.</span>
        </div>
        <div class="field"><label>Activity Title</label><input name="title" required value="{{ old('title') }}" placeholder="Example: Application Development Activity 1"></div>
        <div class="field"><label>Instructions for Students <span class="small muted">(optional if a file is uploaded)</span></label><textarea name="instructions" style="min-height:250px" placeholder="Write the complete instructions here, or upload an instruction file below...">{{ old('instructions') }}</textarea></div>
        <div class="field"><label>Instruction / Reference File</label><input type="file" name="instruction_file" accept=".pdf,.docx,.txt,.md,.pptx"><span class="small muted">PDF, DOCX, TXT, MD or PPTX · max 15 MB.</span></div>
        <div class="alert info small"><strong>Instruction rule:</strong> provide instruction text, an uploaded instruction file, or both.</div>
        <div class="grid grid-2"><div class="field"><label>Deadline</label><input type="datetime-local" name="due_at" value="{{ old('due_at') }}"></div><div class="field"><label>Maximum Score</label><input type="number" step="0.01" min="1" name="max_score" value="{{ old('max_score',100) }}" required></div></div>
        <div class="field"><label>Status</label><select name="status"><option value="draft" @selected(old('status','draft')==='draft')>Draft</option><option value="published" @selected(old('status')==='published')>Published</option></select></div>
        @include('partials.submission-options')
    </div>

    <div class="card rubric-source-card">
        <h3>Rubric Used by AI</h3>
        <p class="small muted">Students can see the same rubric. The AI recommendation is calculated from this grading guide and the submitted work.</p>
        <div class="field"><label>Adviser Rubric / Grading Guide</label><textarea name="rubric_text" class="rubric-textarea" placeholder="Example:\nFunctionality - 30 points\nRequirements Completion - 25 points\nValidation - 15 points\nUser Interface - 15 points\nExplanation - 15 points">{{ old('rubric_text') }}</textarea></div>
        <div class="upload-panel"><label><strong>Upload Rubric File</strong></label><input type="file" name="rubric_file" accept=".pdf,.docx,.txt,.md,.pptx"><p class="small muted">Accepted: PDF, DOCX, TXT, MD, PPTX · max 15 MB.</p></div>
        <div class="alert info small"><strong>Rubric rule:</strong> at least rubric text or a rubric file is required. The recommendation is normalized to the activity maximum score.</div>
        <div class="alert warning small">The AI score is a recommendation. The adviser still reviews and finalizes the academic grade.</div>
        <button class="btn" type="submit" @disabled($rooms->isEmpty())>Save Activity</button>
    </div>
</div>
</form>
@endsection
