
<?php $__env->startSection('title','Adviser Dashboard'); ?>
<?php $__env->startSection('topbar','Adviser Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Adviser Dashboard</h1><p>Manage room sections, publish rubric-based activities, chat with students, review AI scores, and finalize grades.</p></div>
    <div class="actions"><a class="btn secondary" href="<?php echo e(route('adviser.rooms.index')); ?>">Rooms & Keys</a><?php if($roomCount > 0): ?><a class="btn" href="<?php echo e(route('adviser.activities.create')); ?>">Create Activity</a><?php else: ?><a class="btn" href="<?php echo e(route('adviser.rooms.index')); ?>#create-subject">Create Subject & Classroom</a><?php endif; ?></div>
</div>
<?php if($subjects->isEmpty()): ?>
<div class="card">
    <h2>Welcome, <?php echo e(auth()->user()->name); ?></h2>
    <p>Your adviser workspace is ready. Start by creating your first subject and classroom.</p>
    <ol><li>Create a subject and classroom.</li><li>Share the classroom enrollment key with your students.</li><li>Create activities and review submissions from your classroom.</li></ol>
    <a class="btn" href="<?php echo e(route('adviser.rooms.index')); ?>#create-subject">Set Up My First Subject</a>
</div>
<?php else: ?>
<div class="feature-banner"><div><span class="feature-kicker">FEATURES ACTIVE</span><h2>Room Enrollment + Student Chat</h2><p>Every section has a unique enrollment key. Chat is private per student and room, with notifications for new messages.</p></div><a class="btn secondary" href="<?php echo e(route('chat.index')); ?>">Open Student Chat</a></div>
<?php endif; ?>
<br>
<div class="grid grid-4">
    <div class="card"><span class="muted">My Rooms</span><div class="metric"><?php echo e($roomCount); ?></div><a class="small" href="<?php echo e(route('adviser.rooms.index')); ?>">Manage rooms →</a></div>
    <div class="card"><span class="muted">My Subjects</span><div class="metric"><?php echo e($subjects->count()); ?></div></div>
    <div class="card"><span class="muted">Pending Reviews</span><div class="metric"><?php echo e($pending); ?></div><a class="small" href="<?php echo e(route('adviser.scores')); ?>">View scores →</a></div>
    <div class="card"><span class="muted">Unread Chat</span><div class="metric"><?php echo e($unreadChat); ?></div><a class="small" href="<?php echo e(route('chat.index')); ?>">Open chat →</a></div>
</div>
<br>
<div class="card"><div class="page-head"><div><h3>Recent Student Submissions</h3><p>AI rubric scores are shown as soon as an evaluation completes.</p></div><a class="btn secondary sm" href="<?php echo e(route('adviser.scores')); ?>">All AI Scores</a></div><div class="table-wrap"><table><thead><tr><th>Student</th><th>Activity</th><th>Room</th><th>Status</th><th>AI Rubric Score</th><th></th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($s->student->name); ?></td><td><?php echo e($s->activity->title); ?><br><span class="small muted"><?php echo e($s->activity->subject->name); ?></span></td><td><?php echo e($s->activity->room?->name ?: 'General'); ?><br><span class="small muted"><?php echo e($s->activity->room?->section); ?></span></td><td><span class="badge gray"><?php echo e(str_replace('_',' ',$s->status)); ?></span></td><td><?php if($s->latestAiEvaluation): ?><strong><?php echo e($s->latestAiEvaluation->recommended_score ?? '-'); ?>/<?php echo e($s->activity->max_score); ?></strong><?php else: ?><span class="muted">Pending</span><?php endif; ?></td><td><a class="btn sm" href="<?php echo e(route('adviser.submissions.show',$s)); ?>">Review</a></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="empty">No submissions yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/adviser/dashboard.blade.php ENDPATH**/ ?>