<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?php echo e($role ? ucfirst($role).' Login' : 'Login'); ?> - School Repository</title>
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
<body class="login-page">
<div class="login-sky-airplane" aria-hidden="true">
    <span class="distant-airplane">
        <svg viewBox="0 0 240 86" focusable="false">
            <defs>
                <linearGradient id="jet-body" x1="0" y1="0" x2="0" y2="1">
                    <stop stop-color="#fff"/><stop offset=".5" stop-color="#fbfdff"/><stop offset=".85" stop-color="#e0e8ef"/><stop offset="1" stop-color="#acbcc9"/>
                </linearGradient>
                <linearGradient id="jet-wing" x1="0" y1="0" x2="0" y2="1">
                    <stop stop-color="#fff"/><stop offset="1" stop-color="#becbd5"/>
                </linearGradient>
                <linearGradient id="jet-engine" x1="0" y1="0" x2="0" y2="1">
                    <stop stop-color="#eef4f8"/><stop offset=".4" stop-color="#fff"/><stop offset="1" stop-color="#9caebb"/>
                </linearGradient>
            </defs>
            <path fill="#d7e2eb" d="M125 42 L92 16 L85 16 L103 44Z"/>
            <path fill="url(#jet-wing)" d="M29 44 L15 6 Q22 3 29 7 L62 43Z"/>
            <path fill="none" stroke="#becbd4" stroke-width=".7" d="M28 9 L42 40"/>
            <path fill="url(#jet-body)" d="M10 45 Q31 41 62 40 L190 35 Q204 34 214 41 L229 49 Q237 54 226 58 Q215 63 193 63 L78 62 Q39 61 12 53 Q5 50 10 45Z"/>
            <path fill="none" stroke="#fff" stroke-width="1" opacity=".8" d="M47 44 L188 39 Q203 37 212 43"/>
            <path fill="#657e90" d="M200 38 L208 40 L214 45 L202 44Z M193 38 L198 38 L200 44 L193 44Z"/>
            <path fill="none" stroke="#8298a8" stroke-width="2.6" stroke-linecap="round" stroke-dasharray="1 5" d="M60 47 L181 42"/>
            <g fill="none" stroke="#c0ccd5" stroke-width=".8">
                <rect x="48" y="44" width="6" height="12" rx="2"/>
                <rect x="184" y="40" width="6" height="14" rx="2"/>
            </g>
            <path fill="url(#jet-wing)" d="M40 49 L5 39 L2 42 L23 54 L51 54Z"/>
            <path fill="#a9bac7" d="M141 55 L151 62 L137 65 L129 57Z"/>
            <path fill="url(#jet-engine)" d="M128 62 Q139 57 153 60 Q158 65 154 73 Q141 77 127 71Z"/>
            <ellipse fill="#e5edf3" cx="154" cy="66.5" rx="4" ry="7"/>
            <ellipse fill="#536c7e" cx="155" cy="66.5" rx="2.3" ry="5"/>
            <path fill="url(#jet-wing)" d="M134 51 L87 79 L72 80 L108 53Z"/>
            <path fill="#eef4f8" d="M72 80 L67 70 L70 69 L79 78Z"/>
            <path fill="none" stroke="#aebfcb" stroke-width=".7" d="M122 57 L87 76"/>
        </svg>
    </span>
</div>
<div class="login-wrap">
    <main class="login-card" aria-labelledby="loginTitle">
        <div class="login-logo-wrap">
            <img src="<?php echo e(asset('images/brand-logo.png')); ?>" alt="School Repository Logo" class="login-logo">
        </div>
        <h1 class="login-title" id="loginTitle">School Repository</h1>
        <p class="login-subtitle">Secure access for students, advisers, and administrators.</p>
        <p class="small muted">Each portal has a separate login. Open Student, Adviser, and Admin in separate tabs to use them together.</p>

        <nav class="portal-tabs" aria-label="Choose login portal">
            <a class="portal-tab <?php echo e($role==='student'?'selected':''); ?>" href="<?php echo e(route('workspace.login', ['workspace' => 'student'])); ?>"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'profile','size' => '15']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'profile','size' => '15']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>Student</a>
            <a class="portal-tab <?php echo e($role==='adviser'?'selected':''); ?>" href="<?php echo e(route('workspace.login', ['workspace' => 'adviser'])); ?>"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'subjects','size' => '15']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'subjects','size' => '15']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>Adviser</a>
            <a class="portal-tab <?php echo e($role==='admin'?'selected':''); ?>" href="<?php echo e(route('workspace.login', ['workspace' => 'admin'])); ?>"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'shield','size' => '15']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield','size' => '15']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>Admin</a>
        </nav>

        <?php if(session('success')): ?>
            <div class="alert success" role="status"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($role): ?><div class="alert info">Signing in to the <strong><?php echo e(ucfirst($role)); ?></strong> portal.</div><?php endif; ?>

        <form data-validate data-login-form data-portal="<?php echo e($role ?: 'automatic'); ?>" method="POST" action="<?php echo e(route('login.attempt')); ?>" autocomplete="off">
            <?php echo csrf_field(); ?>
            <?php if($role): ?><input type="hidden" name="portal" value="<?php echo e($role); ?>"><?php endif; ?>

            <div class="field login-field">
                <label for="email">Email address</label>
                <span class="field-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'mail','size' => '19']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'mail','size' => '19']); ?>
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
                <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="name@school.test" autocomplete="username" required autofocus aria-describedby="login-email-error" aria-invalid="<?php echo e($errors->has('email') ? 'true' : 'false'); ?>">
                <span id="login-email-error" data-field-error="email" class="field-error" aria-live="polite"><?php echo e($errors->first('email') ?: ($errors->first('portal') ?: $errors->first('default'))); ?></span>
            </div>

            <div class="field login-field">
                <label for="password">Password</label>
                <span class="field-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'lock','size' => '19']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lock','size' => '19']); ?>
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
                <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required aria-describedby="login-password-error" aria-invalid="<?php echo e($errors->has('password') ? 'true' : 'false'); ?>">
                <span id="login-password-error" data-field-error="password" class="field-error" aria-live="polite"><?php echo e($errors->first('password')); ?></span>
                <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password" title="Show or hide password"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'eye','size' => '19']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'eye','size' => '19']); ?>
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
            </div>

            <div class="login-options">
                <label><input id="remember-login" type="checkbox" name="remember" value="1"> Remember my login on this browser</label>
            </div>
            <p class="small muted" id="remember-login-help" role="status">Unchecked: enter your login manually. Checked: remember this portal's email and stay signed in. Save your password in your browser to fill it next time.</p>

            <button class="btn login-submit" type="submit">Sign in <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'chevron','size' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron','size' => '18']); ?>
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

        <details class="demo-box">
            <summary>Local demo accounts</summary>
            <div style="margin-top:9px;line-height:1.7">
                Admin: <code>admin@school.test</code> / <code>password</code><br>
                Adviser: <code>adviser@school.test</code> / <code>password</code><br>
                Student: <code>student@school.test</code> / <code>password</code>
            </div>
        </details>

        <p class="login-footer">
            <?php if($role): ?><a href="<?php echo e(route('login')); ?>">Use automatic role detection</a> · <?php endif; ?>
            University of La Salette • School Online Repository
        </p>
    </main>
</div>
<script src="<?php echo e(asset('js/app.js')); ?>?v=20261104-clear-glass3"></script>
<script src="<?php echo e(asset('js/form-validation.js')); ?>?v=<?php echo e(filemtime(public_path('js/form-validation.js'))); ?>"></script>
<script src="<?php echo e(asset('js/login.js')); ?>?v=<?php echo e(filemtime(public_path('js/login.js'))); ?>"></script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/auth/login.blade.php ENDPATH**/ ?>