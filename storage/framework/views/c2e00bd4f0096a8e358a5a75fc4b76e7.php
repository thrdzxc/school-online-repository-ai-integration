<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Session Expired - School Repository</title>
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
<div class="login-wrap">
    <div class="login-card glass-card" style="text-align:center">
        <img src="<?php echo e(asset('images/brand-logo.png')); ?>" alt="School Repository" class="login-logo">
        <h1>Session Expired</h1>
        <p class="muted">Your browser had an old or expired security token. The project now uses database-backed sessions to prevent recurring session-file permission problems.</p>
        <p class="muted"><strong>Use only http://127.0.0.1:8000</strong> and reload the login page before signing in again.</p>
        <a class="btn" href="<?php echo e(route('login')); ?>">Return to Login</a>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/errors/419.blade.php ENDPATH**/ ?>