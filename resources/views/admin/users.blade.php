@extends('layouts.app')
@section('title','User Management')
@section('topbar','User Management')
@section('inline-errors', 'true')
@section('content')
<div class="page-head"><div><h1>User Management</h1><p>Create and manage student, adviser, and administrator accounts.</p></div><span class="badge gray">{{ $users->total() }} matching accounts</span></div>
<div style="margin-bottom:18px">
    <details class="glass-dropdown" open><summary><strong>Create an Account</strong></summary>
        <form data-validate method="POST" action="{{ route('admin.users.store') }}" class="glass-dropdown-content">@csrf
            @include('partials.account-fields', ['formKey' => 'create', 'account' => null])
            <button class="btn" type="submit">Create Account</button>
        </form>
    </details>
</div>
<div class="card">
    <h3>Accounts</h3>
    @include('partials.admin-role-tabs', ['roleTabLabel' => 'Account roles', 'roleTabRoute' => 'admin.users', 'roleTabs' => ['' => 'All Accounts', 'student' => 'Students', 'adviser' => 'Advisers', 'admin' => 'Administrators']])
    <form method="GET" action="{{ route('admin.users') }}" class="grid grid-3" style="margin:16px 0;align-items:end">
        <div class="field"><label for="user-search">Name, email or school ID</label><input id="user-search" name="q" value="{{ request('q') }}"></div>
        <div class="field"><label for="user-role">Role</label><select id="user-role" name="role"><option value="">All roles</option>@foreach(['student'=>'Students','adviser'=>'Advisers','admin'=>'Administrators'] as $value=>$label)<option value="{{ $value }}" @selected(request('role')===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="actions"><button class="btn">Search Accounts</button><a class="btn secondary" href="{{ route('admin.users') }}">Reset</a></div>
    </form>
    @forelse($users as $u)
    @if($loop->first || $users[$loop->index - 1]->role !== $u->role)
        <div class="admin-role-heading"><h2>{{ ['student' => 'Students', 'adviser' => 'Advisers', 'admin' => 'Administrators'][$u->role] ?? ucfirst($u->role) }}</h2><span class="small muted">Account management</span></div>
    @endif
    <details class="manage-row" @if(in_array(old('_form'), ['edit-'.$u->id, 'password-'.$u->id], true)) open @endif>
        <summary class="account-summary">
            <span class="account-identity"><strong>{{ $u->name }}</strong><span class="small muted">{{ $u->email }}</span></span>
            <span class="account-role">{{ $u->role === 'admin' ? 'Administrator' : ucfirst($u->role) }}</span>
            <span class="small">School ID: {{ $u->school_id ?: 'Not assigned' }}</span>
            <span class="badge {{ $u->active ? 'success' : 'danger' }}">{{ $u->active ? 'Active' : 'Inactive' }}</span>
            <span class="small account-action">Manage account</span>
        </summary>
        <form data-validate method="POST" action="{{ route('admin.users.update',$u) }}" class="manage-form">@csrf @method('PUT')
            @include('partials.account-fields', ['formKey' => 'edit-'.$u->id, 'account' => $u])
            <button class="btn sm">Save Account</button>
        </form>
        <form data-validate method="POST" action="{{ route('admin.users.reset-password',$u) }}" class="manage-form">@csrf
            @php($formKey = 'password-'.$u->id)
            <input type="hidden" name="_form" value="{{ $formKey }}">
            <div class="grid grid-2">
            @foreach(['password' => 'New Password', 'password_confirmation' => 'Confirm Password'] as $field => $label)
                <div class="field"><label for="{{ $formKey }}-{{ $field }}">{{ $label }}</label><input id="{{ $formKey }}-{{ $field }}" type="password" name="{{ $field }}" minlength="8" maxlength="255" required autocomplete="new-password" @if($field === 'password') data-strong-password @endif>@include('partials.field-error', compact('field', 'formKey'))</div>
            @endforeach
            </div><p class="small muted">Use 8+ characters, an uppercase letter, and a special character.</p><button class="btn secondary sm">Reset Password</button>
        </form>
        @if($u->id !== auth()->id())<form method="POST" action="{{ route('admin.users.toggle',$u) }}" class="manage-form" onsubmit="return confirm('Change this account status?')">@csrf<button class="btn secondary sm">{{ $u->active ? 'Disable' : 'Enable' }} Account</button></form>@endif
    </details>
    @empty<div class="empty">No matching accounts.</div>@endforelse
    {{ $users->links('pagination.simple') }}
</div>
@endsection
