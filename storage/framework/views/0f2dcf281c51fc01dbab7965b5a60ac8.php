
<?php $__env->startSection('title','Audit Logs'); ?>
<?php $__env->startSection('topbar','Audit Logs'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head"><div><h1>Audit Logs</h1><p>Review account activity by role, including logins, submissions, evaluations, and grade decisions.</p></div><span class="badge gray"><?php echo e($logs->total()); ?> matching events</span></div>
<div class="card">
    <?php echo $__env->make('partials.admin-role-tabs', ['roleTabLabel' => 'Audit log roles', 'roleTabRoute' => 'admin.audit', 'roleTabs' => ['' => 'All Activity', 'student' => 'Students', 'adviser' => 'Advisers', 'admin' => 'Administrators', 'system' => 'System / Unavailable Accounts']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php ($roleGroups = $logs->getCollection()->groupBy(fn ($log) => $log->user?->role ?? 'system')); ?>
    <?php $__currentLoopData = ['admin' => 'Administrators', 'adviser' => 'Advisers', 'student' => 'Students', 'system' => 'System / Unavailable Accounts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($roleGroups->has($role)): ?>
        <section class="audit-role-section" aria-labelledby="audit-role-<?php echo e($role); ?>">
            <div class="admin-role-heading"><h2 id="audit-role-<?php echo e($role); ?>"><?php echo e($label); ?></h2><span class="small muted">Most recent first</span></div>
            <div class="table-wrap"><table class="audit-events"><thead><tr><th>Date &amp; Time</th><th>Account</th><th>Action</th><th>Record</th><th>Details</th><th>IP Address</th></tr></thead><tbody>
            <?php $__currentLoopData = $roleGroups[$role]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="audit-date"><?php echo e($l->created_at?->format('M d, Y')); ?><br><span class="small muted"><?php echo e($l->created_at?->format('h:i:s A')); ?></span></td>
                    <td><strong><?php echo e($l->user?->name ?? 'System / Unavailable account'); ?></strong><?php if($l->user): ?><br><span class="small muted"><?php echo e($l->user->email); ?></span><?php endif; ?></td>
                    <td><strong class="audit-action"><?php echo e(str_replace('_',' ',$l->action)); ?></strong></td>
                    <td><?php echo e($l->target_type ? ucfirst(str_replace('_', ' ', $l->target_type)) : '-'); ?> <?php if($l->target_id): ?><span class="small muted">#<?php echo e($l->target_id); ?></span><?php endif; ?></td>
                    <td><?php if($l->details): ?><details class="audit-event-details"><summary>View details</summary><dl><?php $__currentLoopData = $l->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><dt><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?></dt><dd><?php echo e(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (is_bool($value) ? ($value ? 'Yes' : 'No') : ($value ?? '-'))); ?></dd><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></dl></details><?php else: ?><span class="muted">-</span><?php endif; ?></td>
                    <td class="small"><?php echo e($l->ip_address ?: '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody></table></div>
        </section>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($logs->isEmpty()): ?><div class="empty">No audit logs match this role.</div><?php endif; ?>
    <?php echo e($logs->links('pagination.simple')); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/admin/audit.blade.php ENDPATH**/ ?>