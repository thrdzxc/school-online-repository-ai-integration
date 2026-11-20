@extends('layouts.app')
@section('title','Edit Subject')
@section('content')
<div class="page-head"><div><h1>Edit Subject</h1><p>{{ $subject->name }}</p></div><a class="btn secondary" href="{{ route('admin.subjects') }}">Back to Subjects</a></div>
<div class="card">
    <p><strong>Assigned adviser:</strong> {{ $subject->adviser->name }}</p>
    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
        @csrf @method('PUT')
        <div class="grid grid-2">
            @foreach(['name' => ['Subject Name',180,true], 'class_code' => ['Course / Class No.',30,false], 'school_year' => ['School Year',30,true], 'semester' => ['Semester',30,true], 'section' => ['Subject Section',80,false], 'schedule' => ['Subject Schedule',120,false], 'room' => ['Subject Room',80,false], 'instructor_name' => ['Instructor Display Name',120,false]] as $field => [$label,$length,$required])
                <div class="field"><label for="subject-{{ $field }}">{{ $label }}</label><input id="subject-{{ $field }}" name="{{ $field }}" value="{{ old($field, $subject->$field) }}" maxlength="{{ $length }}" @if($field === 'name') pattern="\p{L}[\p{L}\p{M} ]*" title="Use letters and spaces only." @elseif($field === 'room') pattern="[\p{L}0-9][\p{L}\p{M}0-9 ]*" title="Use letters, numbers, and spaces only." @endif @required($required)></div>
            @endforeach
            <div class="field"><label for="subject-units">Units</label><input id="subject-units" name="units" type="number" min="0" max="20" step="0.1" value="{{ old('units', $subject->units) }}"></div>
        </div>
        <div class="field"><label for="subject-description">Description</label><textarea id="subject-description" name="description" maxlength="2000">{{ old('description', $subject->description) }}</textarea></div>
        <p class="small muted">Individual classroom names, sections, and schedules can be changed using Edit Classroom.</p>
        <div class="actions"><button class="btn" type="submit">Save Subject Changes</button><a class="btn secondary" href="{{ route('admin.subjects') }}">Cancel</a></div>
    </form>
</div>
@endsection
