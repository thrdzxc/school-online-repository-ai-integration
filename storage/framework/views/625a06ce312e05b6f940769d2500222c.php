<?php $__env->startSection('title','AI Scores & Grades'); ?>
<?php $__env->startSection('topbar','AI Scores & Adviser Grades'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>AI Scores & Grades</h1><p>View scores and feedback after your adviser publishes the final grade.</p></div>
    <a class="btn secondary" href="<?php echo e(route('student.submissions.index')); ?>">My Submissions</a>
</div>
<div class="score-note"><strong>How scoring works:</strong> the AI reads the adviser’s instructions, rubric, and your submission. AI suggestions are reviewed privately by the adviser before the final grade is released.</div>
<br>
<div class="card table-wrap">
<table>
    <thead><tr><th>Activity</th><th>Room / Section</th><th>Subject</th><th>AI Rubric Score</th><th>Final Adviser Grade</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $submissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php ($ai=$s->grade ? $s->latestAiEvaluation : null); ?>
        <tr>
            <td><strong><?php echo e($s->activity->title); ?></strong><br><span class="small muted">Submitted <?php echo e($s->submitted_at->format('M d, Y')); ?></span></td>
            <td><?php echo e($s->activity->room?->name ?: 'General'); ?><br><span class="small muted"><?php echo e($s->activity->room?->section); ?></span></td>
            <td><?php echo e($s->activity->subject->name); ?></td>
            <td><?php if($ai): ?><strong class="score-inline"><?php echo e($ai->recommended_score ?? '-'); ?> / <?php echo e($s->activity->max_score); ?></strong><?php if($ai->is_demo): ?><span class="badge warn">demo</span><?php endif; ?> <?php else: ?><span class="muted">Awaiting final grade</span><?php endif; ?></td>
            <td><?php if($s->grade): ?><strong><?php echo e($s->grade->final_score); ?> / <?php echo e($s->activity->max_score); ?></strong><?php else: ?><span class="muted">Not finalized</span><?php endif; ?></td>
            <td><span class="badge <?php echo e($s->status==='graded'?'success':'gray'); ?>"><?php echo e(str_replace('_',' ',$s->status)); ?></span></td>
            <td><a class="btn sm" href="<?php echo e(route('student.submissions.show',$s)); ?>">View Rubric</a></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7" class="empty">No submissions or AI scores yet.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php echo e($submissions->links('pagination.simple')); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/student/grades.blade.php ENDPATH**/ ?>