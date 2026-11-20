<nav class="admin-role-tabs" aria-label="{{ $roleTabLabel }}">
    @foreach($roleTabs as $value => $label)
        <a class="admin-role-tab {{ request('role', '') === $value ? 'is-active' : '' }}" href="{{ route($roleTabRoute, array_filter(['role' => $value, 'q' => request('q')], fn ($value) => $value !== null && $value !== '')) }}" @if(request('role', '') === $value) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
</nav>
