<?php $__env->startSection('title','Student Dashboard'); ?>
<?php $__env->startSection('topbar','Student Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Welcome, <?php echo e(auth()->user()->name); ?></h1><p>Your rooms, activities, private adviser chat, notifications, submissions, and published grades are all in one place.</p></div>
    <div class="actions"><a class="btn secondary" href="<?php echo e(route('student.rooms')); ?>">My Rooms</a><a class="btn" href="<?php echo e(route('chat.index')); ?>">Adviser Chat <?php if($unreadChat): ?> (<?php echo e($unreadChat); ?>) <?php endif; ?></a></div>
</div>
<div class="feature-banner"><div><span class="feature-kicker">FEATURES ACTIVE</span><h2>Rooms + Chat + AI Scores</h2><p>Join a section using a room key, message your adviser, submit activities, and view adviser-approved feedback.</p></div><a class="btn secondary" href="<?php echo e(route('student.grades')); ?>">View Grades</a></div>
<br>
<div class="grid grid-4">
    <div class="card"><span class="muted">My Rooms</span><div class="metric"><?php echo e($roomCount); ?></div><a class="small" href="<?php echo e(route('student.rooms')); ?>">View sections →</a></div>
    <div class="card"><span class="muted">Active Activities</span><div class="metric"><?php echo e($activities->count()); ?></div><a class="small" href="<?php echo e(route('student.activities.index')); ?>">Open activities →</a></div>
    <div class="card"><span class="muted">Unread Chat</span><div class="metric"><?php echo e($unreadChat); ?></div><a class="small" href="<?php echo e(route('chat.index')); ?>">Open chat →</a></div>
    <div class="card"><span class="muted">Unread Notifications</span><div class="metric"><?php echo e($notifications->whereNull('read_at')->count()); ?></div><a class="small" href="<?php echo e(route('notifications.index')); ?>">View notices →</a></div>
</div>
<br>
<div class="grid grid-2">
    <div class="card"><h3>Upcoming Room Activities</h3><?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="score-line"><div><a href="<?php echo e(route('student.activities.show',$activity)); ?>"><strong><?php echo e($activity->title); ?></strong></a><br><span class="small muted"><?php echo e($activity->room?->name ?: 'General'); ?> · <?php echo e($activity->subject->name); ?> · Due <?php echo e(optional($activity->due_at)->format('M d, Y h:i A') ?? 'No deadline'); ?></span></div><span class="badge"><?php echo e($activity->max_score); ?> pts</span></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="empty">No active activities in your rooms.</div><?php endif; ?></div>
    <div class="card"><h3>Recent Submissions & AI Score</h3><?php $__empty_1 = true; $__currentLoopData = $latestSubmissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="score-line"><div><a href="<?php echo e(route('student.submissions.show',$submission)); ?>"><strong><?php echo e($submission->activity->title); ?></strong></a><br><span class="small muted"><?php echo e($submission->activity->room?->name ?: 'General'); ?> · Version <?php echo e($submission->version); ?></span></div><div style="text-align:right"><?php if($submission->grade && $submission->latestAiEvaluation): ?><strong><?php echo e($submission->latestAiEvaluation->recommended_score); ?>/<?php echo e($submission->activity->max_score); ?></strong><br><span class="small muted">AI rubric</span><?php else: ?><span class="badge <?php echo e($submission->status==='graded'?'success':'gray'); ?>"><?php echo e(str_replace('_',' ',$submission->status)); ?></span><?php endif; ?></div></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="empty">No submissions yet.</div><?php endif; ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/student/dashboard.blade.php ENDPATH**/ ?>