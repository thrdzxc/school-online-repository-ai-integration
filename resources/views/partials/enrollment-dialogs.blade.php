@if(session('enrollment_preview'))
    @php($preview = session('enrollment_preview'))
    <dialog open id="enrollment-confirmation" class="enrollment-dialog" data-enrollment-dialog aria-labelledby="enrollment-confirmation-title" aria-describedby="enrollment-confirmation-description">
        <div class="enrollment-dialog-header">
            <span class="enrollment-dialog-icon"><x-icon name="subjects" size="28" /></span>
            <span class="enrollment-eyebrow">Student enrollment</span>
            <h2 id="enrollment-confirmation-title">Confirm your enrollment</h2>
            <p id="enrollment-confirmation-description">Are you sure you would like to enroll in this subject and classroom? Please review the details before proceeding.</p>
        </div>
        <div class="enrollment-dialog-body">
            @include('partials.enrollment-details', ['details' => $preview])
            @if($preview['previous_classroom'])
                <p class="enrollment-dialog-note enrollment-dialog-note-warning"><x-icon name="rooms" size="18" /><span>Confirming will transfer your enrollment from <strong>{{ $preview['previous_classroom'] }}</strong> to this classroom. Activities from your previous section will no longer be available.</span></p>
            @elseif($preview['already_enrolled'])
                <p class="enrollment-dialog-note"><x-icon name="shield" size="18" /><span>You are already enrolled in this classroom. Continuing will keep your current enrollment.</span></p>
            @else
                <p class="enrollment-dialog-note"><x-icon name="shield" size="18" /><span>Your enrollment will be saved only after you select <strong>Confirm Enrollment</strong>.</span></p>
            @endif
        </div>
        <div class="enrollment-dialog-actions">
            <form method="GET" action="{{ route('student.rooms') }}"><button type="submit" class="btn secondary" autofocus>Cancel</button></form>
            <form method="POST" action="{{ route('student.rooms.join') }}" data-enrollment-submit data-pending-label="Confirming enrollment…">
                @csrf
                <input type="hidden" name="enrollment_key" value="{{ $preview['key'] }}">
                <button type="submit" class="btn enrollment-primary" data-enrollment-button>Confirm Enrollment <x-icon name="chevron" size="17" /></button>
            </form>
        </div>
    </dialog>
@elseif(session('enrollment_alert'))
    @php($details = session('enrollment_details', []))
    <dialog open id="enrollment-alert" class="enrollment-dialog enrollment-dialog-success" data-enrollment-dialog aria-labelledby="enrollment-alert-title" aria-describedby="enrollment-alert-description">
        <div class="enrollment-dialog-header">
            <span class="enrollment-dialog-icon"><x-icon name="shield" size="30" /></span>
            <span class="enrollment-eyebrow">Enrollment status</span>
            <h2 id="enrollment-alert-title">{{ $details['title'] ?? 'Enrollment confirmed' }}</h2>
            <p id="enrollment-alert-description">{{ session('enrollment_alert') }}</p>
        </div>
        @if($details)
            <div class="enrollment-dialog-body">@include('partials.enrollment-details', compact('details'))</div>
        @endif
        <div class="enrollment-dialog-actions">
            <form method="dialog"><button class="btn secondary" autofocus>Close</button></form>
            @if(!empty($details['activities_url']))
                <a class="btn enrollment-primary" href="{{ $details['activities_url'] }}">View Subject Activities <x-icon name="chevron" size="17" /></a>
            @endif
        </div>
    </dialog>
@endif
