<div class="subject-creation-fields">
<section class="subject-creation-section">
<h4>Subject Details</h4>
<div class="field"><label for="new-name">Subject Name</label><input id="new-name" name="name" pattern="\p{L}[\p{L}\p{M} ]*" title="Use letters and spaces only." value="{{ old('name') }}" placeholder="Software Engineering" maxlength="180" required></div>
@if($showAdviser ?? false)
    <div class="field"><label for="new-adviser">Adviser</label><select id="new-adviser" name="adviser_id" required><option value="">Select adviser</option>@foreach($advisers as $adviser)<option value="{{ $adviser->id }}" @selected(old('adviser_id')==$adviser->id)>{{ $adviser->name }}</option>@endforeach</select></div>
@endif
<div class="grid grid-2">
    <div class="field"><label for="new-year">School Year</label><input id="new-year" name="school_year" value="{{ old('school_year', date('Y').'-'.(date('Y')+1)) }}" placeholder="2026-2027" maxlength="30" required></div>
    <div class="field"><label for="new-semester">Semester</label><select id="new-semester" name="semester" required><option value="">Choose semester</option>@foreach(['1st Semester','2nd Semester','Summer'] as $term)<option value="{{ $term }}" @selected(old('semester')===$term)>{{ $term }}</option>@endforeach</select></div>
</div>
</section>
<section class="classroom-creation-section">
<h4>Classroom Details</h4>
<p class="small muted">An enrollment key is generated automatically when you save.</p>
<div class="grid grid-2">
    <div class="field"><label for="new-room-name">Classroom Name</label><input id="new-room-name" name="room_name" pattern="\p{L}[\p{L}\p{M} ]*" title="Use letters and spaces only." value="{{ old('room_name') }}" placeholder="COMPLAB" list="classroom-name-suggestions" maxlength="120" required></div>
    <div class="field"><label for="new-section">Section (optional)</label><input id="new-section" name="section" value="{{ old('section') }}" placeholder="3-B" maxlength="80"></div>
</div>
<div class="grid grid-2">
    <div class="field"><label for="new-physical-room">Physical Room (optional)</label><input id="new-physical-room" name="room" pattern="[\p{L}0-9][\p{L}\p{M}0-9 ]*" title="Use letters, numbers, and spaces only." value="{{ old('room') }}" placeholder="OS304" maxlength="80"></div>
    <div class="field"><label for="new-capacity">Capacity (optional)</label><input id="new-capacity" type="number" name="capacity" value="{{ old('capacity') }}" min="1" max="500"></div>
</div>
<div class="field"><label for="new-schedule">Schedule (optional)</label><input id="new-schedule" name="schedule" value="{{ old('schedule') }}" placeholder="MW 8:00–9:30 AM" maxlength="120"></div>
</section>
</div>
<details class="glass-dropdown" style="margin-bottom:16px" @if(old('class_code') || old('units') || old('description') || old('instructor_name')) open @endif>
    <summary><strong>Additional Subject Details</strong><span class="small muted">Optional</span></summary>
    <div class="glass-dropdown-content">
        <div class="grid grid-2">
            <div class="field"><label for="new-class-code">Course / Class No.</label><input id="new-class-code" name="class_code" value="{{ old('class_code') }}" placeholder="IT-302" maxlength="30"></div>
            <div class="field"><label for="new-units">Units</label><input id="new-units" name="units" type="number" min="0" max="20" step="0.1" value="{{ old('units') }}"></div>
        </div>
        @if($showAdviser ?? false)
            <div class="field"><label for="new-instructor">Instructor Name</label><input id="new-instructor" name="instructor_name" value="{{ old('instructor_name') }}" maxlength="120"></div>
        @endif
        <div class="field"><label for="new-description">Description</label><textarea id="new-description" name="description" rows="3" maxlength="2000" placeholder="What will students learn?">{{ old('description') }}</textarea></div>
    </div>
</details>
