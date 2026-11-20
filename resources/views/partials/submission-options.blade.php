<fieldset class="submission-options">
    <legend>Submission Options</legend>
    @foreach([
        'allow_text' => ['Allow text', 'Students can type or paste their answer.'],
        'allow_file' => ['Allow document upload', 'Students can attach PDF, DOCX, TXT, MD, or PPTX files.'],
        'allow_late' => ['Allow late submission', 'Accept work after the deadline. Returned work can still be revised when this is off.'],
    ] as $option => [$label, $description])
        <input type="hidden" name="{{ $option }}" value="0">
        <label class="submission-option" for="{{ $option }}">
            <input id="{{ $option }}" type="checkbox" name="{{ $option }}" value="1" @checked(old($option, isset($activity) ? $activity->$option : true)) aria-describedby="{{ $option }}-help">
            <span><strong>{{ $label }}</strong><small id="{{ $option }}-help">{{ $description }}</small></span>
        </label>
    @endforeach
    <p class="small muted">Enable text, document upload, or both.</p>
</fieldset>
