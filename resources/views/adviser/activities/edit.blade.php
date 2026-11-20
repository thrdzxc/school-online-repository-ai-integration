@extends('layouts.app')
@section('title','Edit Activity')
@section('topbar','Edit Room Activity')
@section('content')
<div class="page-head"><div><h1>Edit Activity & Rubric</h1><p>Room, instructions, and rubric are locked after the first student submission.</p></div></div>
<form method="POST" action="{{ route('adviser.activities.update',$activity) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="grid grid-2">
    <div class="card">
        <h3>Activity Details</h3>
        <div class="field"><label>Room / Section</label><select name="room_id" required>@foreach($rooms as $room)<option value="{{ $room->id }}" @selected(old('room_id',$activity->room_id)==$room->id)>{{ $room->name }} · {{ $room->section ?: 'No section' }} · {{ $room->subject->name }}</option>@endforeach</select></div>
        <div class="field"><label>Activity Title</label><input name="title" required value="{{ old('title',$activity->title) }}"></div>
        <div class="field"><label>Instructions for Students</label><textarea name="instructions" style="min-height:250px">{{ old('instructions',$activity->instructions) }}</textarea></div>
        <div class="field"><label>Instruction / Reference File</label>@if($activity->instruction_file_path)<div class="file-chip">Current: {{ $activity->instruction_original_filename }}</div><label class="inline small"><input type="checkbox" name="remove_instruction_file" value="1"> Remove current file</label>@endif<input type="file" name="instruction_file" accept=".pdf,.docx,.txt,.md,.pptx"></div>
        <div class="grid grid-2"><div class="field"><label>Deadline</label><input type="datetime-local" name="due_at" value="{{ old('due_at',$activity->due_at?->format('Y-m-d\\TH:i')) }}"></div><div class="field"><label>Maximum Score</label><input type="number" step="0.01" min="1" name="max_score" value="{{ old('max_score',$activity->max_score) }}" required></div></div>
        <div class="field"><label>Status</label><select name="status"><option value="draft" @selected(old('status',$activity->status)==='draft')>Draft</option><option value="published" @selected(old('status',$activity->status)==='published')>Published</option></select></div>
        @include('partials.submission-options')
    </div>

    <div class="card rubric-source-card">
        <h3>Rubric Used by AI</h3>
        <div class="field"><label>Adviser Rubric / Grading Guide</label><textarea name="rubric_text" class="rubric-textarea">{{ old('rubric_text',$activity->rubric_text) }}</textarea></div>
        <div class="field"><label>Rubric File</label>@if($activity->rubric_file_path)<div class="file-chip">Current: {{ $activity->rubric_original_filename }}</div><label class="inline small"><input type="checkbox" name="remove_rubric_file" value="1"> Remove current file</label>@endif<input type="file" name="rubric_file" accept=".pdf,.docx,.txt,.md,.pptx"></div>
        <div class="alert info small">At least rubric text or a rubric file must remain attached to the activity.</div>
        <button class="btn" type="submit">Save Changes</button>
    </div>
</div>
</form>
@endsection
