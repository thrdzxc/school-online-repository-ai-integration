<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="color-scheme" content="light">
    <title><?php echo $__env->yieldContent('title','School Repository'); ?> - <?php echo e(config('app.name')); ?></title>
    <?php if (isset($component)) { $__componentOriginalcd57f27880e9969aeea6d732d37aff90 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcd57f27880e9969aeea6d732d37aff90 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.background-assets','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('background-assets'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcd57f27880e9969aeea6d732d37aff90)): ?>
<?php $attributes = $__attributesOriginalcd57f27880e9969aeea6d732d37aff90; ?>
<?php unset($__attributesOriginalcd57f27880e9969aeea6d732d37aff90); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcd57f27880e9969aeea6d732d37aff90)): ?>
<?php $component = $__componentOriginalcd57f27880e9969aeea6d732d37aff90; ?>
<?php unset($__componentOriginalcd57f27880e9969aeea6d732d37aff90); ?>
<?php endif; ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>?v=<?php echo e(filemtime(public_path('css/app.css'))); ?>">
</head>
<body class="app-page <?php echo $__env->yieldContent('body-class'); ?>">
<?php
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
?>

<div class="app-shell" id="appShell">
    <button class="sidebar-backdrop" type="button" aria-label="Close navigation" data-sidebar-close></button>

    <aside class="sidebar" id="sidebar" aria-label="Primary navigation">
        <div class="sidebar-brand">
            <div class="brand-mark logo-mark"><img src="<?php echo e(asset('images/brand-logo.png')); ?>" alt="School Repository Logo"></div>
            <div class="brand-copy">
                <strong class="brand">School Repository</strong>
            </div>
        </div>

        <button class="icon-button sidebar-collapse-button" type="button" data-sidebar-toggle title="Minimize sidebar" aria-label="Minimize sidebar" aria-controls="sidebar" aria-expanded="true">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'collapse','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'collapse','size' => '20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
        </button>

        <nav class="nav" aria-label="<?php echo e(ucfirst($currentUser->role)); ?> workspace">
            <div class="nav-label">Workspace</div>
            <?php $__currentLoopData = $workspaceNav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $activePatterns = explode('|', $item['active']);
                    $isActive = collect($activePatterns)->contains(fn($pattern) => request()->routeIs($pattern, 'workspace.'.$pattern));
                ?>
                <a class="nav-item <?php echo e($isActive ? 'active' : ''); ?>" href="<?php echo e(route($item['route'])); ?>" title="<?php echo e($item['label']); ?>" data-nav-link>
                    <span class="nav-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $item['icon'],'size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['icon']),'size' => '20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
                    <span class="nav-text"><?php echo e($item['label']); ?></span>
                    <?php if(!empty($item['count'])): ?><span class="nav-count"><?php echo e($item['count'] > 99 ? '99+' : $item['count']); ?></span><?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="nav-label nav-label-spaced">Personal</div>
            <a class="nav-item <?php echo e(request()->routeIs('profile.*', 'workspace.profile.*') ? 'active' : ''); ?>" href="<?php echo e(route('profile.show')); ?>" title="Profile" data-nav-link>
                <span class="nav-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'profile','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'profile','size' => '20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
                <span class="nav-text">Profile</span>
            </a>
            <a class="nav-item notification-nav <?php echo e(request()->routeIs('notifications.*', 'workspace.notifications.*') ? 'active' : ''); ?>" href="<?php echo e(route('notifications.index')); ?>" title="Notifications" data-nav-link>
                <span class="nav-icon bell-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'bell','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','size' => '20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
                <span class="nav-text">Notifications</span>
                <?php if($navUnread): ?><span class="nav-count nav-count-red"><?php echo e($navUnread > 99 ? '99+' : $navUnread); ?></span><?php endif; ?>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a class="sidebar-profile" href="<?php echo e(route('profile.show')); ?>" title="Open profile" data-nav-link>
                <div class="avatar avatar-photo">
                    <?php if($profilePhoto): ?>
                        <img src="<?php echo e($profilePhoto); ?>" alt="<?php echo e($currentUser->name); ?> profile photo">
                    <?php else: ?>
                        <?php echo e(strtoupper(substr($currentUser->name,0,1))); ?>

                    <?php endif; ?>
                </div>
                <div class="sidebar-profile-copy">
                    <strong><?php echo e($currentUser->name); ?></strong>
                    <span><?php echo e(ucfirst($currentUser->role)); ?></span>
                </div>
            </a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="sidebar-logout-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="icon-button sidebar-logout" title="Log out" aria-label="Log out"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'logout','size' => '19']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'logout','size' => '19']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></button>
            </form>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button class="icon-button topbar-menu" type="button" data-sidebar-toggle aria-label="Open or close navigation"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'menu','size' => '21']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'menu','size' => '21']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></button>
                <div>
                    <span class="topbar-kicker"><?php echo e(ucfirst($currentUser->role)); ?> Portal</span>
                    <strong><?php echo $__env->yieldContent('topbar','School Online Repository'); ?></strong>
                </div>
            </div>
            <div class="top-actions">
                <?php if(!$currentUser->isAdmin()): ?>
                    <a class="top-action" href="<?php echo e(route('chat.index')); ?>" title="Open chat"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'chat','size' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chat','size' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><span class="top-action-label">Chat</span><?php if($chatUnread): ?><b><?php echo e($chatUnread); ?></b><?php endif; ?></a>
                <?php endif; ?>
                <a class="top-action notification-top" href="<?php echo e(route('notifications.index')); ?>" title="Notifications"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'bell','size' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bell','size' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><span class="top-action-label">Notifications</span><?php if($navUnread): ?><b><?php echo e($navUnread); ?></b><?php endif; ?></a>
                <a class="top-profile" href="<?php echo e(route('profile.show')); ?>" title="Profile">
                    <span class="top-profile-avatar">
                        <?php if($profilePhoto): ?><img src="<?php echo e($profilePhoto); ?>" alt="Profile photo"><?php else: ?><?php echo e(strtoupper(substr($currentUser->name,0,1))); ?><?php endif; ?>
                    </span>
                    <span class="top-profile-copy"><strong><?php echo e($currentUser->name); ?></strong><small><?php echo e(ucfirst($currentUser->role)); ?></small></span>
                </a>
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="top-logout-form">
                    <?php echo csrf_field(); ?>
                    <button class="icon-button top-logout" type="submit" title="Log out" aria-label="Log out"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'logout','size' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'logout','size' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></button>
                </form>
            </div>
        </header>

        <div class="content">
            <?php if(session('success') && !session('enrollment_alert')): ?><div class="alert success"><?php echo e(session('success')); ?></div><?php endif; ?>
            <?php if($errors->any() && !View::hasSection('inline-errors')): ?>
                <div class="alert error"><strong>Please check the following:</strong><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>
</div>
<script src="<?php echo e(asset('js/app.js')); ?>?v=<?php echo e(filemtime(public_path('js/app.js'))); ?>"></script>
<script src="<?php echo e(asset('js/form-validation.js')); ?>?v=<?php echo e(filemtime(public_path('js/form-validation.js'))); ?>"></script>
<script src="<?php echo e(asset('js/room-details.js')); ?>?v=<?php echo e(filemtime(public_path('js/room-details.js'))); ?>" data-room-details-url="<?php echo e(route('rooms.details')); ?>" defer></script>
<?php echo $__env->make('partials.enrollment-dialogs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script src="<?php echo e(asset('js/enrollment-dialogs.js')); ?>?v=<?php echo e(filemtime(public_path('js/enrollment-dialogs.js'))); ?>"></script>
<datalist id="classroom-name-suggestions"><option value="COMPLAB"><option value="OS"><option value="EM"></datalist>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/layouts/app.blade.php ENDPATH**/ ?>