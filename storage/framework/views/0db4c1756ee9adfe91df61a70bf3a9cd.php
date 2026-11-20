
<?php $__env->startSection('title','Student AI Scores'); ?>
<?php $__env->startSection('topbar','Student AI Scores'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Student AI Scores</h1><p>View AI rubric recommendations and adviser-finalized grades for your own activities.</p></div>
    <a class="btn secondary" href="<?php echo e(route('adviser.activities.index')); ?>">Activities</a>
</div>
<div class="card filter-card">
    <form method="GET" class="filter-row">
        <?php echo $__env->make('partials.submission-subject-filter', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="field"><label>Filter by Room</label><select name="room_id"><option value="">All rooms</option><?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($room->id); ?>" <?php if(request('room_id')==$room->id): echo 'selected'; endif; ?>><?php echo e($room->name); ?> · <?php echo e($room->section ?: 'No section'); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <button class="btn sm" type="submit">Apply Filter</button>
        <?php if(request('room_id') || request('subject_id')): ?><a class="btn secondary sm" href="<?php echo e(route('adviser.scores')); ?>">Clear</a><?php endif; ?>
    </form>
</div>
<br>
<div class="card table-wrap">
<table><thead><tr><th>Student</th><th>Activity</th><th>Room</th><th>AI Rubric Score</th><th>Final Grade</th><th>Status</th><th></th></tr></thead><tbody>
<?php $__empty_1 = true; $__currentLoopData = $submissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<?php if($loop->first || $submissions[$loop->index - 1]->activity->subject_id !== $s->activity->subject_id): ?>
<tr class="submission-subject-heading"><th colspan="7" scope="colgroup"><?php echo e($s->activity->subject->displayLabel()); ?></th></tr>
<?php endif; ?>

<tr>
    <td><strong><?php echo e($s->student->name); ?></strong><br><span class="small muted"><?php echo e($s->student->school_id); ?></span></td>
    <td><?php echo e($s->activity->title); ?><br><span class="small muted"><?php echo e($s->activity->subject->name); ?></span></td>
    <td><?php echo e($s->activity->room?->name ?: 'General'); ?><br><span class="small muted"><?php echo e($s->activity->room?->section); ?></span></td>
    <td><?php if($s->latestAiEvaluation): ?><strong class="score-inline"><?php echo e($s->latestAiEvaluation->recommended_score); ?> / <?php echo e($s->activity->max_score); ?></strong><?php else: ?><span class="muted">Not evaluated</span><?php endif; ?></td>
    <td><?php if($s->grade): ?><strong><?php echo e($s->grade->final_score); ?> / <?php echo e($s->activity->max_score); ?></strong><?php else: ?><span class="muted">Not finalized</span><?php endif; ?></td>
    <td><span class="badge <?php echo e($s->status==='graded'?'success':'gray'); ?>"><?php echo e(str_replace('_',' ',$s->status)); ?></span></td>
    <td><a class="btn sm" href="<?php echo e(route('adviser.submissions.show',$s)); ?>">View Rubric Score</a></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7" class="empty">No student submissions yet.</td></tr><?php endif; ?>
</tbody></table>
<?php echo e($submissions->links('pagination.simple')); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/adviser/scores.blade.php ENDPATH**/ ?>