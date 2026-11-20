<input type="hidden" name="_form" value="{{ $formKey }}">
<div class="grid grid-2">
@foreach(['name' => ['Full Name',150], 'email' => ['Email Address',255], 'school_id' => ['School ID',60], 'course' => ['Course',100], 'year_level' => ['Year Level',30], 'section' => ['Section',30], 'department' => ['Department',100]] as $field => [$label, $length])
    @php($value = old('_form', 'create') === $formKey ? old($field, $account?->{$field}) : $account?->{$field})
    <div class="field">
        <label for="{{ $formKey }}-{{ $field }}">{{ $label }}</label>
        <input id="{{ $formKey }}-{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ $value }}" maxlength="{{ $length }}" @if(in_array($field, ['name','email'])) required @endif @if($field === 'name') data-person-name autocomplete="name" @endif aria-describedby="{{ $formKey }}-{{ $field }}-error" aria-invalid="{{ old('_form', 'create') === $formKey && $errors->has($field) ? 'true' : 'false' }}">
        @include('partials.field-error', compact('field', 'formKey'))
    </div>
@endforeach
<div class="field"><label for="{{ $formKey }}-role">Account Role</label><select id="{{ $formKey }}-role" name="role" required>
@foreach(['student'=>'Student','adviser'=>'Adviser','admin'=>'Administrator'] as $value=>$label)
<option value="{{ $value }}" @selected((old('_form', 'create') === $formKey ? old('role', $account?->role ?? 'student') : $account?->role) === $value)>{{ $label }}</option>
@endforeach
</select>@include('partials.field-error', ['field' => 'role', 'formKey' => $formKey])</div>
@if(!$account)
<div class="field"><label for="create-password">Initial Password</label><input id="create-password" type="password" name="password" data-strong-password minlength="8" maxlength="255" autocomplete="new-password" required><span class="small muted">8+ characters, an uppercase letter, and a special character.</span>@include('partials.field-error', ['field' => 'password', 'formKey' => $formKey])</div>
@endif
</div>
