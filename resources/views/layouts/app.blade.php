<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <title>@yield('title','School Repository') - {{ config('app.name') }}</title>
    <x-background-assets />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="app-page @yield('body-class')">
@php
    $currentUser = auth()->user();
    $navUnread = \App\Models\AppNotification::where('user_id', $currentUser->id)->whereNull('read_at')->count();
    $chatUnread = 0;
    if ($currentUser->isStudent()) {
        $chatUnread = \App\Models\ChatMessage::whereHas('conversation', fn($q) => $q->where('student_id', $currentUser->id))
            ->where('sender_id','!=',$currentUser->id)->whereNull('read_at')->count();
    } elseif ($currentUser->isAdviser()) {
        $chatUnread = \App\Models\ChatMessage::whereHas('conversation', fn($q) => $q->where('adviser_id', $currentUser->id))
            ->where('sender_id','!=',$currentUser->id)->whereNull('read_at')->count();
    }
    $profilePhoto = $currentUser->profilePhotoUrl();

    $workspaceNav = [];
    if ($currentUser->isStudent()) {
        $workspaceNav = [
            ['route'=>'student.dashboard','label'=>'Dashboard','icon'=>'home','active'=>'student.dashboard'],
            ['route'=>'student.rooms','label'=>'My Rooms','icon'=>'rooms','active'=>'student.rooms*'],
            ['route'=>'student.activities.index','label'=>'Activities','icon'=>'activity','active'=>'student.activities.*'],
            ['route'=>'student.submissions.index','label'=>'My Submissions','icon'=>'submission','active'=>'student.submissions.*'],
            ['route'=>'student.grades','label'=>'Grades & Feedback','icon'=>'star','active'=>'student.grades'],
            ['route'=>'chat.index','label'=>'Adviser Chat','icon'=>'chat','active'=>'chat.*','count'=>$chatUnread],
        ];
    } elseif ($currentUser->isAdviser()) {
        $workspaceNav = [
            ['route'=>'adviser.dashboard','label'=>'Dashboard','icon'=>'home','active'=>'adviser.dashboard'],
            ['route'=>'adviser.rooms.index','label'=>'Subjects & Classrooms','icon'=>'rooms','active'=>'adviser.rooms.*'],
            ['route'=>'adviser.activities.index','label'=>'Activities & Rubrics','icon'=>'activity','active'=>'adviser.activities.index|adviser.activities.show|adviser.activities.edit|adviser.activities.submissions'],
            ['route'=>'adviser.scores','label'=>'Evaluation & Grades','icon'=>'star','active'=>'adviser.scores'],
            ['route'=>'chat.index','label'=>'Student Chat','icon'=>'chat','active'=>'chat.*','count'=>$chatUnread],
            ['route'=>'adviser.reports','label'=>'Reports','icon'=>'report','active'=>'adviser.reports'],
        ];
    } else {
        $workspaceNav = [
            ['route'=>'admin.dashboard','label'=>'Dashboard','icon'=>'home','active'=>'admin.dashboard'],
            ['route'=>'admin.users','label'=>'User Management','icon'=>'users','active'=>'admin.users*'],
            ['route'=>'admin.subjects','label'=>'Subjects & Classrooms','icon'=>'subjects','active'=>'admin.subjects*'],
            ['route'=>'admin.activities','label'=>'All Activities','icon'=>'activity','active'=>'admin.activities*'],
            ['route'=>'admin.submissions','label'=>'All Submissions','icon'=>'submission','active'=>'admin.submissions*'],
            ['route'=>'admin.monitor','label'=>'System Monitor','icon'=>'monitor','active'=>'admin.monitor'],
            ['route'=>'admin.ai-settings','label'=>'AI Settings','icon'=>'settings','active'=>'admin.ai-settings'],
            ['route'=>'admin.ai-monitor','label'=>'AI Monitor','icon'=>'star','active'=>'admin.ai-monitor'],
            ['route'=>'admin.audit','label'=>'Audit Logs','icon'=>'audit','active'=>'admin.audit'],
        ];
    }
@endphp

<div class="app-shell" id="appShell">
    <button class="sidebar-backdrop" type="button" aria-label="Close navigation" data-sidebar-close></button>

    <aside class="sidebar" id="sidebar" aria-label="Primary navigation">
        <div class="sidebar-brand">
            <div class="brand-mark logo-mark"><img src="{{ asset('images/brand-logo.png') }}" alt="School Repository Logo"></div>
            <div class="brand-copy">
                <strong class="brand">School Repository</strong>
            </div>
        </div>

        <button class="icon-button sidebar-collapse-button" type="button" data-sidebar-toggle title="Minimize sidebar" aria-label="Minimize sidebar" aria-controls="sidebar" aria-expanded="true">
            <x-icon name="collapse" size="20" />
        </button>

        <nav class="nav" aria-label="{{ ucfirst($currentUser->role) }} workspace">
            <div class="nav-label">Workspace</div>
            @foreach($workspaceNav as $item)
                @php
                    $activePatterns = explode('|', $item['active']);
                    $isActive = collect($activePatterns)->contains(fn($pattern) => request()->routeIs($pattern, 'workspace.'.$pattern));
                @endphp
                <a class="nav-item {{ $isActive ? 'active' : '' }}" href="{{ route($item['route']) }}" title="{{ $item['label'] }}" data-nav-link>
                    <span class="nav-icon"><x-icon :name="$item['icon']" size="20" /></span>
                    <span class="nav-text">{{ $item['label'] }}</span>
                    @if(!empty($item['count']))<span class="nav-count">{{ $item['count'] > 99 ? '99+' : $item['count'] }}</span>@endif
                </a>
            @endforeach

            <div class="nav-label nav-label-spaced">Personal</div>
            <a class="nav-item {{ request()->routeIs('profile.*', 'workspace.profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}" title="Profile" data-nav-link>
                <span class="nav-icon"><x-icon name="profile" size="20" /></span>
                <span class="nav-text">Profile</span>
            </a>
            <a class="nav-item notification-nav {{ request()->routeIs('notifications.*', 'workspace.notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}" title="Notifications" data-nav-link>
                <span class="nav-icon bell-icon"><x-icon name="bell" size="20" /></span>
                <span class="nav-text">Notifications</span>
                @if($navUnread)<span class="nav-count nav-count-red">{{ $navUnread > 99 ? '99+' : $navUnread }}</span>@endif
            </a>
        </nav>

        <div class="sidebar-footer">
            <a class="sidebar-profile" href="{{ route('profile.show') }}" title="Open profile" data-nav-link>
                <div class="avatar avatar-photo">
                    @if($profilePhoto)
                        <img src="{{ $profilePhoto }}" alt="{{ $currentUser->name }} profile photo">
                    @else
                        {{ strtoupper(substr($currentUser->name,0,1)) }}
                    @endif
                </div>
                <div class="sidebar-profile-copy">
                    <strong>{{ $currentUser->name }}</strong>
                    <span>{{ ucfirst($currentUser->role) }}</span>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
                @csrf
                <button type="submit" class="icon-button sidebar-logout" title="Log out" aria-label="Log out"><x-icon name="logout" size="19" /></button>
            </form>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button class="icon-button topbar-menu" type="button" data-sidebar-toggle aria-label="Open or close navigation"><x-icon name="menu" size="21" /></button>
                <div>
                    <span class="topbar-kicker">{{ ucfirst($currentUser->role) }} Portal</span>
                    <strong>@yield('topbar','School Online Repository')</strong>
                </div>
            </div>
            <div class="top-actions">
                @if(!$currentUser->isAdmin())
                    <a class="top-action" href="{{ route('chat.index') }}" title="Open chat"><x-icon name="chat" size="18" /><span class="top-action-label">Chat</span>@if($chatUnread)<b>{{ $chatUnread }}</b>@endif</a>
                @endif
                <a class="top-action notification-top" href="{{ route('notifications.index') }}" title="Notifications"><x-icon name="bell" size="18" /><span class="top-action-label">Notifications</span>@if($navUnread)<b>{{ $navUnread }}</b>@endif</a>
                <a class="top-profile" href="{{ route('profile.show') }}" title="Profile">
                    <span class="top-profile-avatar">
                        @if($profilePhoto)<img src="{{ $profilePhoto }}" alt="Profile photo">@else{{ strtoupper(substr($currentUser->name,0,1)) }}@endif
                    </span>
                    <span class="top-profile-copy"><strong>{{ $currentUser->name }}</strong><small>{{ ucfirst($currentUser->role) }}</small></span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="top-logout-form">
                    @csrf
                    <button class="icon-button top-logout" type="submit" title="Log out" aria-label="Log out"><x-icon name="logout" size="18" /></button>
                </form>
            </div>
        </header>

        <div class="content">
            @if(session('success') && !session('enrollment_alert'))<div class="alert success">{{ session('success') }}</div>@endif
            @if($errors->any() && !View::hasSection('inline-errors'))
                <div class="alert error"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
<script src="{{ asset('js/form-validation.js') }}?v={{ filemtime(public_path('js/form-validation.js')) }}"></script>
<script src="{{ asset('js/room-details.js') }}?v={{ filemtime(public_path('js/room-details.js')) }}" data-room-details-url="{{ route('rooms.details') }}" defer></script>
@include('partials.enrollment-dialogs')
<script src="{{ asset('js/enrollment-dialogs.js') }}?v={{ filemtime(public_path('js/enrollment-dialogs.js')) }}"></script>
<datalist id="classroom-name-suggestions"><option value="COMPLAB"><option value="OS"><option value="EM"></datalist>
@stack('scripts')
</body>
</html>
