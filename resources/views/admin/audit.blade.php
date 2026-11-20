@extends('layouts.app')
@section('title','Audit Logs')
@section('topbar','Audit Logs')
@section('content')
<div class="page-head"><div><h1>Audit Logs</h1><p>Review account activity by role, including logins, submissions, evaluations, and grade decisions.</p></div><span class="badge gray">{{ $logs->total() }} matching events</span></div>
<div class="card">
    @include('partials.admin-role-tabs', ['roleTabLabel' => 'Audit log roles', 'roleTabRoute' => 'admin.audit', 'roleTabs' => ['' => 'All Activity', 'student' => 'Students', 'adviser' => 'Advisers', 'admin' => 'Administrators', 'system' => 'System / Unavailable Accounts']])
    @php($roleGroups = $logs->getCollection()->groupBy(fn ($log) => $log->user?->role ?? 'system'))
    @foreach(['admin' => 'Administrators', 'adviser' => 'Advisers', 'student' => 'Students', 'system' => 'System / Unavailable Accounts'] as $role => $label)
        @if($roleGroups->has($role))
        <section class="audit-role-section" aria-labelledby="audit-role-{{ $role }}">
            <div class="admin-role-heading"><h2 id="audit-role-{{ $role }}">{{ $label }}</h2><span class="small muted">Most recent first</span></div>
            <div class="table-wrap"><table class="audit-events"><thead><tr><th>Date &amp; Time</th><th>Account</th><th>Action</th><th>Record</th><th>Details</th><th>IP Address</th></tr></thead><tbody>
            @foreach($roleGroups[$role] as $l)
                <tr>
                    <td class="audit-date">{{ $l->created_at?->format('M d, Y') }}<br><span class="small muted">{{ $l->created_at?->format('h:i:s A') }}</span></td>
                    <td><strong>{{ $l->user?->name ?? 'System / Unavailable account' }}</strong>@if($l->user)<br><span class="small muted">{{ $l->user->email }}</span>@endif</td>
                    <td><strong class="audit-action">{{ str_replace('_',' ',$l->action) }}</strong></td>
                    <td>{{ $l->target_type ? ucfirst(str_replace('_', ' ', $l->target_type)) : '-' }} @if($l->target_id)<span class="small muted">#{{ $l->target_id }}</span>@endif</td>
                    <td>@if($l->details)<details class="audit-event-details"><summary>View details</summary><dl>@foreach($l->details as $key => $value)<dt>{{ ucfirst(str_replace('_', ' ', $key)) }}</dt><dd>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (is_bool($value) ? ($value ? 'Yes' : 'No') : ($value ?? '-')) }}</dd>@endforeach</dl></details>@else<span class="muted">-</span>@endif</td>
                    <td class="small">{{ $l->ip_address ?: '-' }}</td>
                </tr>
            @endforeach
            </tbody></table></div>
        </section>
        @endif
    @endforeach
    @if($logs->isEmpty())<div class="empty">No audit logs match this role.</div>@endif
    {{ $logs->links('pagination.simple') }}
</div>
@endsection
