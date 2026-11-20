
<?php $__env->startSection('title','Subjects & Classrooms'); ?>
<?php $__env->startSection('topbar','Subjects & Classrooms'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Subjects & Classrooms</h1><p>Create and manage your own subjects and classrooms. Each adviser has a separate workspace.</p></div>
    <a class="btn secondary" href="<?php echo e(route('chat.index')); ?>">Student Messages</a>
</div>
<div class="card" id="create-subject" style="margin-bottom:18px">
    <h3>Create Subject & Classroom</h3>
    <p class="small muted">Set up your subject and first classroom together, then share the enrollment key with students.</p>
    <form method="POST" action="<?php echo e(route('adviser.subjects.store')); ?>"><?php echo csrf_field(); ?>
        <?php echo $__env->make('partials.subject-classroom-fields', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <button class="btn" type="submit">Create Subject & Classroom</button>
    </form>
</div>
<div class="page-head" style="margin-bottom:15px"><div><h2>My Subjects</h2><p>Edit details without changing existing enrollments.</p></div><span class="badge gray"><?php echo e($subjects->count()); ?> active subjects</span></div>
<div class="adviser-subject-list">
<?php $__empty_1 = true; $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <details class="glass-dropdown adviser-subject-panel" <?php if(old('subject_id') == $subject->id): ?> open <?php endif; ?>>
        <summary><span><strong><?php echo e($subject->name); ?></strong><span class="small muted" style="display:block"><?php echo e($subject->school_year); ?> · <?php echo e($subject->semester); ?></span></span><span class="badge gray">Edit details</span></summary>
        <div class="subject-editor-layout">
        <form class="subject-editor-form" method="POST" action="<?php echo e(route('adviser.subjects.update',$subject)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="subject-section-heading"><h3>Subject Information</h3><p>Update the subject details and academic schedule.</p></div>
            <div class="grid grid-2">
            <div class="field"><label>Course / Class No. (optional)</label><input name="class_code" value="<?php echo e($subject->class_code); ?>" maxlength="30"></div>
            <div class="field"><label>Subject Name</label><input name="name" pattern="\p{L}[\p{L}\p{M} ]*" title="Use letters and spaces only." value="<?php echo e($subject->name); ?>" maxlength="180" required></div>
            </div>
            <div class="field"><label>Description</label><textarea name="description" rows="3"><?php echo e($subject->description); ?></textarea></div>
            <div class="grid grid-2"><div class="field"><label>School Year</label><input name="school_year" value="<?php echo e($subject->school_year); ?>" required></div><div class="field"><label>Semester</label><input name="semester" value="<?php echo e($subject->semester); ?>" required></div></div>
            <div class="grid grid-2"><div class="field"><label>Section (optional)</label><input name="section" value="<?php echo e($subject->section); ?>"></div><div class="field"><label>Units</label><input name="units" type="number" min="0" max="20" step="0.5" value="<?php echo e($subject->units); ?>"></div></div>
            <div class="grid grid-2"><div class="field"><label>Schedule (optional)</label><input name="schedule" value="<?php echo e($subject->schedule); ?>"></div><div class="field"><label>Room (optional)</label><input name="room" pattern="[\p{L}0-9][\p{L}\p{M}0-9 ]*" title="Use letters, numbers, and spaces only." value="<?php echo e($subject->room); ?>"></div></div>
            <div class="subject-form-actions"><button class="btn sm" type="submit">Save Subject Changes</button></div>
        </form>
        <div class="subject-classroom-panel">
            <div class="subject-section-heading"><h3>Add Another Classroom</h3><p>Create a section with its own enrollment key.</p></div>
            <form method="POST" action="<?php echo e(route('adviser.rooms.store')); ?>"><?php echo csrf_field(); ?>
                <input type="hidden" name="subject_id" value="<?php echo e($subject->id); ?>">
                <div class="grid grid-2"><div class="field"><label>Classroom Name</label><input placeholder="COMPLAB" list="classroom-name-suggestions" name="name" pattern="\p{L}[\p{L}\p{M} ]*" title="Use letters and spaces only." value="<?php echo e(old('subject_id') == $subject->id ? old('name') : ''); ?>" maxlength="120" required></div><div class="field"><label>Section</label><input name="section" value="<?php echo e(old('subject_id') == $subject->id ? old('section') : ''); ?>" maxlength="80"></div></div>
                <div class="grid grid-2"><div class="field"><label>Physical Room</label><input placeholder="OS304" name="physical_room" pattern="[\p{L}0-9][\p{L}\p{M}0-9 ]*" title="Use letters, numbers, and spaces only." value="<?php echo e(old('subject_id') == $subject->id ? old('physical_room') : ''); ?>" maxlength="80"></div><div class="field"><label>Capacity</label><input type="number" name="capacity" value="<?php echo e(old('subject_id') == $subject->id ? old('capacity') : ''); ?>" min="1" max="500"></div></div>
                <div class="field"><label>Schedule</label><input name="schedule" value="<?php echo e(old('subject_id') == $subject->id ? old('schedule') : ''); ?>" maxlength="120"></div>
                <div class="subject-form-actions"><button class="btn secondary sm" type="submit">Add Classroom & Key</button></div>
            </form>
        </div>
        </div>
    </details>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card"><div class="empty">No subjects yet. Create your subject and classroom using the form above.</div></div>
<?php endif; ?>
</div>
<div class="page-head" style="margin-bottom:15px"><div><h2>Classrooms & Enrollment Keys</h2><p>Each classroom belongs to a subject. Students join using its unique key.</p></div></div>
<div class="grid grid-3">
<?php $__empty_1 = true; $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="card room-card">
        <div class="room-card-top"><span class="badge <?php echo e($room->active ? 'success' : 'gray'); ?>"><?php echo e($room->active ? 'Active' : 'Closed'); ?></span><span class="small muted"><?php echo e($room->students_count); ?> students</span></div>
        <h3 style="margin-top:12px"><?php echo e($room->name); ?></h3>
        <p class="small muted"><?php echo e($room->subject->name); ?> · <?php echo e($room->section ?: 'No section'); ?></p>
        <div class="room-key"><?php echo e($room->enrollment_key); ?></div>
        <div class="room-meta"><?php if($room->physical_room): ?><span class="badge gray"><?php echo e($room->physical_room); ?></span><?php endif; ?> <?php if($room->schedule): ?><span class="badge gray"><?php echo e($room->schedule); ?></span><?php endif; ?></div>
        <div class="actions" style="margin-top:15px"><a class="btn sm" href="<?php echo e(route('adviser.rooms.show',$room)); ?>">Manage Classroom</a><a class="btn secondary sm" href="<?php echo e(route('adviser.rooms.edit',$room)); ?>">Edit Classroom</a></div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card" style="grid-column:1/-1"><div class="empty">No classrooms yet. Create a subject and classroom above, or add a classroom under an existing subject.</div></div>
<?php endif; ?>
</div>
<?php echo e($rooms->links('pagination.simple')); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/adviser/rooms/index.blade.php ENDPATH**/ ?>