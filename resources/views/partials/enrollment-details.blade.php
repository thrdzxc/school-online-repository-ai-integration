<dl class="enrollment-summary">
    <div class="enrollment-summary-subject"><dt>Subject</dt><dd>{{ $details['subject'] }}</dd></div>
    <div><dt>Classroom</dt><dd>{{ $details['classroom'] }}</dd></div>
    @if(!empty($details['section']))<div><dt>Section</dt><dd>{{ $details['section'] }}</dd></div>@endif
    <div><dt>Adviser</dt><dd>{{ $details['adviser'] }}</dd></div>
</dl>
