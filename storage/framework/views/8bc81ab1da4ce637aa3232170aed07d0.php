<?php $__env->startSection('title','Activities & Rubrics'); ?>
<?php $__env->startSection('topbar','Room Activities & Rubrics'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head"><div><h1>Activities & Rubrics</h1><p>Every activity is assigned to a room/section so submissions stay organized.</p></div><a class="btn" href="<?php echo e(route('adviser.activities.create')); ?>">New Activity</a></div>
<div class="card table-wrap"><table><thead><tr><th>Activity</th><th>Room / Section</th><th>Subject</th><th>Status</th><th>Deadline</th><th>Submissions</th><th></th></tr></thead><tbody>
<?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td><strong><?php echo e($a->title); ?></strong></td><td><?php echo e($a->room?->name ?: 'General'); ?><br><span class="small muted"><?php echo e($a->room?->section); ?></span></td><td><?php echo e($a->subject->name); ?></td><td><span class="badge <?php echo e($a->status==='published'?'success':'gray'); ?>"><?php echo e($a->status); ?></span></td><td><?php echo e(optional($a->due_at)->format('M d, Y h:i A') ?? 'None'); ?></td><td><?php echo e($a->submissions_count); ?></td><td><div class="actions"><a class="btn sm" href="<?php echo e(route('adviser.activities.show',$a)); ?>">Open</a><a class="btn secondary sm" href="<?php echo e(route('adviser.activities.submissions',$a)); ?>">Submissions</a></div></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7" class="empty">No activities yet.</td></tr><?php endif; ?>
</tbody></table><?php echo e($activities->links('pagination.simple')); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/adviser/activities/index.blade.php ENDPATH**/ ?>