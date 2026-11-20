<?php $__env->startSection('title','Chat'); ?>
<?php $__env->startSection('topbar','Online Chat'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1><?php echo e(auth()->user()->isStudent() ? 'Adviser Chat' : 'Student Chat'); ?></h1><p>Private conversations connected to your enrolled rooms and sections.</p></div>
</div>

<div class="chat-layout">
    <div class="card chat-list">
        <div class="chat-list-head"><strong>Conversations</strong><div class="small muted"><?php echo e($conversations->count()); ?> conversation(s)</div></div>
        <?php $__empty_1 = true; $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conversation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php ($other=auth()->user()->isStudent() ? $conversation->adviser : $conversation->student); ?>
            <a class="conversation-item" href="<?php echo e(route('chat.show',$conversation)); ?>">
                <div class="conversation-title"><strong><?php echo e($other->name); ?></strong><?php if(($unread[$conversation->id] ?? collect())->count()): ?><span class="badge info"><?php echo e(($unread[$conversation->id] ?? collect())->count()); ?> new</span><?php endif; ?></div>
                <div class="small muted"><?php echo e($conversation->room->name); ?></div>
                <div class="conversation-preview"><?php echo e($conversation->latestMessage?->body ?: 'Start the conversation.'); ?></div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty">No chat conversation yet.</div>
        <?php endif; ?>
    </div>

    <div class="card">
        <?php if(auth()->user()->isStudent()): ?>
            <h3>Start a Conversation</h3>
            <p class="muted small">Choose one of your enrolled rooms to talk directly with its adviser.</p>
            <?php $__empty_1 = true; $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="score-line"><div><strong><?php echo e($room->name); ?></strong><br><span class="small muted"><?php echo e($room->subject->adviser->name); ?> · <?php echo e($room->subject->name); ?></span></div><a class="btn sm" href="<?php echo e(route('chat.room',$room)); ?>">Open Chat</a></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="empty">Join a room first before starting an adviser chat.</div>
            <?php endif; ?>
        <?php else: ?>
            <h3>Student Conversations</h3>
            <p class="muted">Open a conversation from the list, or go to <strong>Rooms & Sections</strong> and choose a student from the room roster to start a chat.</p>
            <a class="btn" href="<?php echo e(route('adviser.rooms.index')); ?>">Go to Rooms & Sections</a>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/chat/index.blade.php ENDPATH**/ ?>