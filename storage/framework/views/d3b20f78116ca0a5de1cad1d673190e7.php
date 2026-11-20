<nav class="admin-role-tabs" aria-label="<?php echo e($roleTabLabel); ?>">
    <?php $__currentLoopData = $roleTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a class="admin-role-tab <?php echo e(request('role', '') === $value ? 'is-active' : ''); ?>" href="<?php echo e(route($roleTabRoute, array_filter(['role' => $value, 'q' => request('q')], fn ($value) => $value !== null && $value !== ''))); ?>" <?php if(request('role', '') === $value): ?> aria-current="page" <?php endif; ?>><?php echo e($label); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/partials/admin-role-tabs.blade.php ENDPATH**/ ?>