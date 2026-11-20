
<?php $__env->startSection('title','My Profile'); ?>
<?php $__env->startSection('topbar','My Profile'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div>
        <h1>My Profile</h1>
        <p>Students, advisers, and administrators can upload their own profile picture. The image appears in the sidebar and account header.</p>
    </div>
</div>

<div class="grid grid-2">
    <div class="card profile-card">
        <div class="profile-hero">
            <div class="profile-avatar-lg" id="profilePreview">
                <?php if($user->profilePhotoUrl()): ?>
                    <img src="<?php echo e($user->profilePhotoUrl()); ?>" alt="<?php echo e($user->name); ?> profile photo" data-profile-preview>
                <?php else: ?>
                    <span data-profile-fallback><?php echo e(strtoupper(substr($user->name,0,1))); ?></span>
                <?php endif; ?>
            </div>
            <div>
                <h3 style="margin-bottom:4px"><?php echo e($user->name); ?></h3>
                <p class="muted" style="margin:0"><?php echo e(ucfirst($user->role)); ?> · <?php echo e($user->email); ?></p>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="profile_photo">Profile Photo</label>
                <input id="profile_photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp,image/gif" data-profile-input>
                <button class="btn secondary sm" type="button" id="open-camera">Open camera</button>
                <p class="small" id="camera-status" role="status"></p>
                <span class="small muted">JPG, PNG, WEBP, or GIF • maximum 4 MB.</span>
            </div>
            <?php if($user->profilePhotoUrl()): ?>
                <label class="inline small" style="margin-bottom:14px"><input type="checkbox" name="remove_photo" value="1"> Remove current photo instead</label>
            <?php endif; ?>
            <div class="actions">
                <button class="btn" type="submit"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'camera','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'camera','size' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Save Profile Photo</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h3>Account Details</h3>
        <div class="score-line"><div><strong>Name</strong><br><span class="small muted"><?php echo e($user->name); ?></span></div></div>
        <div class="score-line"><div><strong>Email</strong><br><span class="small muted"><?php echo e($user->email); ?></span></div></div>
        <div class="score-line"><div><strong>Role</strong><br><span class="small muted"><?php echo e(ucfirst($user->role)); ?></span></div></div>
        <?php if($user->school_id): ?><div class="score-line"><div><strong>School ID</strong><br><span class="small muted"><?php echo e($user->school_id); ?></span></div></div><?php endif; ?>
        <?php if($user->course || $user->year_level || $user->section): ?>
            <div class="score-line"><div><strong>Academic Information</strong><br><span class="small muted"><?php echo e(trim(($user->course ? $user->course.' ' : '').($user->year_level ? 'Year '.$user->year_level.' ' : '').($user->section ? 'Section '.$user->section : ''))); ?></span></div></div>
        <?php endif; ?>
        <?php if($user->department): ?><div class="score-line"><div><strong>Department</strong><br><span class="small muted"><?php echo e($user->department); ?></span></div></div><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<dialog id="photo-camera" class="camera-dialog" aria-labelledby="camera-title" aria-describedby="camera-instructions">
    <header class="camera-dialog-header">
        <span class="camera-heading-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'camera','size' => '24']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'camera','size' => '24']); ?>
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
        <div><h2 id="camera-title">Take a Profile Photo</h2><p id="camera-instructions">Position yourself in the frame, then select Capture Photo.</p></div>
    </header>
    <div class="camera-dialog-body">
        <div class="camera-viewfinder">
            <video id="camera-video" autoplay playsinline muted aria-label="Live camera preview"></video>
            <img id="camera-preview" alt="Captured profile photo for review" hidden>
        </div>
        <p id="camera-live-status" class="camera-live-status" role="status" aria-live="polite">Preparing camera...</p>
        <p id="camera-error" role="alert"></p>
    </div>
    <footer class="camera-dialog-footer">
        <div class="camera-dialog-actions">
            <button type="button" class="btn secondary" id="cancel-camera">Cancel</button>
            <div class="camera-primary-actions">
                <button type="button" class="btn secondary" id="retake-photo" hidden>Retake Photo</button>
                <button type="button" class="btn camera-capture-button" id="capture-photo" disabled><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'camera','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'camera','size' => '20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Capture Photo</button>
                <button type="button" class="btn" id="use-photo" hidden>Use This Photo</button>
            </div>
        </div>
        <p class="small muted">After confirming, select Save Profile Photo to save your changes.</p>
    </footer>
</dialog>
<script src="<?php echo e(asset('js/camera.js')); ?>?v=<?php echo e(filemtime(public_path('js/camera.js'))); ?>" defer></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/profile/show.blade.php ENDPATH**/ ?>