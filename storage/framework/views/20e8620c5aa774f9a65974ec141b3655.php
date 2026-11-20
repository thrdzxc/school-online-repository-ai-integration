<?php $__env->startSection('title','Notifications'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Notifications</h1><p>Submission, revision, grading, and system updates.</p></div>
    <form method="POST" action="<?php echo e(route('notifications.read-all')); ?>"><?php echo csrf_field(); ?><button class="btn secondary sm">Mark all as read</button></form>
</div>
<div class="card">
<?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="score-line">
        <div class="notification-item-copy">
            <span class="notification-item-icon" aria-hidden="true">!</span>
            <?php if(!$n->read_at): ?><span class="notice-dot"></span><?php endif; ?>
            <strong><?php echo e($n->title); ?></strong><br>
            <span><?php echo e($n->message); ?></span><br>
            <span class="small muted"><?php echo e($n->created_at->diffForHumans()); ?></span>
        </div>
        <form method="POST" action="<?php echo e(route('notifications.read',$n)); ?>"><?php echo csrf_field(); ?><button class="btn secondary sm"><?php echo e($n->url ? 'Open' : 'Mark read'); ?></button></form>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="empty">No notifications.</div>
<?php endif; ?>
<?php echo e($notifications->links('pagination.simple')); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/notifications.blade.php ENDPATH**/ ?>