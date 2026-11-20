@extends('layouts.app')
@section('title','Subjects & Classrooms')
@section('topbar','Subjects & Classrooms')
@section('content')
<div class="page-head">
    <div>
        <h1>Subjects & Classrooms</h1>
        <p>Manage subjects by name, adviser, and semester, with a classroom and enrollment key for each section.</p>
    </div>
</div>

<div class="subject-layout admin-subject-layout">
    <section class="card subject-create-card">
        <h3>Create Subject & Classroom</h3>
        <p class="small muted">Set up a subject and its first classroom together.</p>
        <form method="POST" action="{{ route('admin.subjects.store') }}">@csrf
            @include('partials.subject-classroom-fields', ['showAdviser' => true])
            <button class="btn" type="submit" @disabled($advisers->isEmpty())>Create Subject & Classroom</button>
            @if($advisers->isEmpty())<p class="small muted">Add an active adviser before creating a subject and classroom.</p>@endif
        </form>
    </section>

    <section class="card">
        <div class="page-head" style="margin-bottom:14px">
            <div><h3 style="margin:0">Subject Directory</h3><p>Find subjects by name, section, adviser, or semester.</p></div>
        </div>

        <form method="GET" action="{{ route('admin.subjects') }}" class="subject-toolbar">
            <div class="field toolbar-wide">
                <label>Search</label>
                <input name="q" value="{{ request('q') }}" placeholder="Search subject name, course no., section, or instructor">
            </div>
            <div class="field">
                <label>Course No.</label>
                <select name="class_code"><option value="">All</option>@foreach($classCodes as $code)<option value="{{ $code }}" @selected(request('class_code')===$code)>{{ $code }}</option>@endforeach</select>
            </div>
            <div class="field">
                <label>School Year</label>
                <select name="school_year"><option value="">All</option>@foreach($schoolYears as $year)<option value="{{ $year }}" @selected(request('school_year')===$year)>{{ $year }}</option>@endforeach</select>
            </div>
            <div class="field">
                <label>Semester</label>
                <select name="semester"><option value="">All</option>@foreach($semesters as $sem)<option value="{{ $sem }}" @selected(request('semester')===$sem)>{{ $sem }}</option>@endforeach</select>
            </div>
            <div class="field">
                <label>Adviser</label>
                <select name="adviser_id"><option value="">All</option>@foreach($advisers as $a)<option value="{{ $a->id }}" @selected((string)request('adviser_id')===(string)$a->id)>{{ $a->name }}</option>@endforeach</select>
            </div>
            <div class="actions">
                <button class="btn sm"><x-icon name="search" size="15" /> Filter</button>
                <a class="btn secondary sm" href="{{ route('admin.subjects') }}">Reset</a>
            </div>
        </form>

        <div class="subject-list">
        @forelse($subjects as $s)
            <details class="subject-row">
                <summary>
                    <div class="subject-summary">
                        <div class="subject-class-badge">{{ $s->class_code ?: 'No Class' }}</div>
                        <div>
                            <span class="subject-name">{{ $s->name }}</span>
                        </div>
                        <div class="subject-stats">
                            {{ $s->section ?: 'No section' }} · {{ $s->students_count }} student(s)<br>
                            {{ $s->rooms->count() }} room(s) · {{ $s->activities_count }} activit{{ $s->activities_count===1 ? 'y' : 'ies' }}
                        </div>
                    </div>
                </summary>
                <div class="subject-details">
                    <div class="actions" style="margin-bottom:14px"><a class="btn secondary sm" href="{{ route('admin.subjects.edit', $s) }}">Edit Subject</a></div>
                    <div class="subject-meta-grid">
                        <div class="subject-meta-box"><span>Adviser</span><strong>{{ $s->adviser->name }}</strong></div>
                        <div class="subject-meta-box"><span>School Year</span><strong>{{ $s->school_year }}</strong></div>
                        <div class="subject-meta-box"><span>Semester</span><strong>{{ $s->semester }}</strong></div>
                        <div class="subject-meta-box"><span>Schedule / Room</span><strong>{{ $s->schedule ?: 'Not set' }} @if($s->room) · {{ $s->room }} @endif</strong></div>
                    </div>

                    <h4 style="margin:8px 0">Classrooms & Enrollment Keys</h4>
                    @forelse($s->rooms as $room)
                        <div class="score-line" data-live-room="{{ $room->id }}">
                            <div><strong data-room-field="name">{{ $room->name }}</strong><br><span class="small muted"><span data-room-field="section">{{ $room->section ?: 'No section' }}</span><span data-room-optional="physical_room" @if(!$room->physical_room) hidden @endif> &middot; <span data-room-field="physical_room">{{ $room->physical_room }}</span></span><span data-room-optional="schedule" @if(!$room->schedule) hidden @endif> &middot; <span data-room-field="schedule">{{ $room->schedule }}</span></span></span></div>
                            <div class="actions"><span class="room-key">{{ $room->enrollment_key }}</span><a class="btn secondary sm" href="{{ route('admin.rooms.edit', $room) }}">Edit Classroom</a></div>
                        </div>
                    @empty
                        <div class="empty">No room configured for this subject.</div>
                    @endforelse

                    <form class="subject-enroll-row" method="POST" action="{{ route('admin.subjects.enroll',$s) }}" style="margin-top:14px">
                        @csrf
                        <div class="field"><label>Student</label><select name="student_id" required><option value="">Select student</option>@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->name }} @if($student->school_id) · {{ $student->school_id }} @endif</option>@endforeach</select></div>
                        <div class="field"><label>Enrollment Room</label><select name="room_id" required><option value="">Select room</option>@foreach($s->rooms as $room)<option value="{{ $room->id }}" data-room-label="{{ $room->id }}">{{ $room->name }} @if($room->section) · {{ $room->section }} @endif</option>@endforeach</select></div>
                        <button class="btn sm" @disabled($s->rooms->isEmpty())>Enroll Student</button>
                    </form>

                    @if($s->students->isNotEmpty())
                        <div class="table-wrap" style="margin-top:14px"><table><thead><tr><th>Student</th><th>School ID</th><th>Assigned Room</th><th>Action</th></tr></thead><tbody>
                        @foreach($s->students as $student)
                            @php($enrollment=$s->enrollments->firstWhere('student_id',$student->id))
                            <tr>
                                <td><strong>{{ $student->name }}</strong></td>
                                <td>{{ $student->school_id ?: '—' }}</td>
                                <td @if($enrollment?->room) data-live-room="{{ $enrollment->room->id }}" @endif><span data-room-field="name">{{ $enrollment?->room?->name ?? 'Not assigned' }}</span></td>
                                <td><form method="POST" action="{{ route('admin.subjects.unenroll',[$s,$student]) }}" onsubmit="return confirm('Remove this student from the subject?')">@csrf @method('DELETE')<button class="btn danger sm">Remove</button></form></td>
                            </tr>
                        @endforeach
                        </tbody></table></div>
                    @endif
                </div>
            </details>
        @empty
            <div class="empty">No subjects match the current filters.</div>
        @endforelse
        </div>

        {{ $subjects->links('pagination.simple') }}
    </section>
</div>
@endsection
