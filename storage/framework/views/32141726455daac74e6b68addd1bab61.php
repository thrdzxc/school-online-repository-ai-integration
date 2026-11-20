<div class="subject-creation-fields">
<section class="subject-creation-section">
<h4>Subject Details</h4>
<div class="field"><label for="new-name">Subject Name</label><input id="new-name" name="name" pattern="\p{L}[\p{L}\p{M} ]*" title="Use letters and spaces only." value="<?php echo e(old('name')); ?>" placeholder="Software Engineering" maxlength="180" required></div>
<?php if($showAdviser ?? false): ?>
    <div class="field"><label for="new-adviser">Adviser</label><select id="new-adviser" name="adviser_id" required><option value="">Select adviser</option><?php $__currentLoopData = $advisers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adviser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($adviser->id); ?>" <?php if(old('adviser_id')==$adviser->id): echo 'selected'; endif; ?>><?php echo e($adviser->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<?php endif; ?>
<div class="grid grid-2">
    <div class="field"><label for="new-year">School Year</label><input id="new-year" name="school_year" value="<?php echo e(old('school_year', date('Y').'-'.(date('Y')+1))); ?>" placeholder="2026-2027" maxlength="30" required></div>
    <div class="field"><label for="new-semester">Semester</label><select id="new-semester" name="semester" required><option value="">Choose semester</option><?php $__currentLoopData = ['1st Semester','2nd Semester','Summer']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($term); ?>" <?php if(old('semester')===$term): echo 'selected'; endif; ?>><?php echo e($term); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
</div>
</section>
<section class="classroom-creation-section">
<h4>Classroom Details</h4>
<p class="small muted">An enrollment key is generated automatically when you save.</p>
<div class="grid grid-2">
    <div class="field"><label for="new-room-name">Classroom Name</label><input id="new-room-name" name="room_name" pattern="\p{L}[\p{L}\p{M} ]*" title="Use letters and spaces only." value="<?php echo e(old('room_name')); ?>" placeholder="COMPLAB" list="classroom-name-suggestions" maxlength="120" required></div>
    <div class="field"><label for="new-section">Section (optional)</label><input id="new-section" name="section" value="<?php echo e(old('section')); ?>" placeholder="3-B" maxlength="80"></div>
</div>
<div class="grid grid-2">
    <div class="field"><label for="new-physical-room">Physical Room (optional)</label><input id="new-physical-room" name="room" pattern="[\p{L}0-9][\p{L}\p{M}0-9 ]*" title="Use letters, numbers, and spaces only." value="<?php echo e(old('room')); ?>" placeholder="OS304" maxlength="80"></div>
    <div class="field"><label for="new-capacity">Capacity (optional)</label><input id="new-capacity" type="number" name="capacity" value="<?php echo e(old('capacity')); ?>" min="1" max="500"></div>
</div>
<div class="field"><label for="new-schedule">Schedule (optional)</label><input id="new-schedule" name="schedule" value="<?php echo e(old('schedule')); ?>" placeholder="MW 8:00–9:30 AM" maxlength="120"></div>
</section>
</div>
<details class="glass-dropdown" style="margin-bottom:16px" <?php if(old('class_code') || old('units') || old('description') || old('instructor_name')): ?> open <?php endif; ?>>
    <summary><strong>Additional Subject Details</strong><span class="small muted">Optional</span></summary>
    <div class="glass-dropdown-content">
        <div class="grid grid-2">
            <div class="field"><label for="new-class-code">Course / Class No.</label><input id="new-class-code" name="class_code" value="<?php echo e(old('class_code')); ?>" placeholder="IT-302" maxlength="30"></div>
            <div class="field"><label for="new-units">Units</label><input id="new-units" name="units" type="number" min="0" max="20" step="0.1" value="<?php echo e(old('units')); ?>"></div>
        </div>
        <?php if($showAdviser ?? false): ?>
            <div class="field"><label for="new-instructor">Instructor Name</label><input id="new-instructor" name="instructor_name" value="<?php echo e(old('instructor_name')); ?>" maxlength="120"></div>
        <?php endif; ?>
        <div class="field"><label for="new-description">Description</label><textarea id="new-description" name="description" rows="3" maxlength="2000" placeholder="What will students learn?"><?php echo e(old('description')); ?></textarea></div>
    </div>
</details>
<?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/partials/subject-classroom-fields.blade.php ENDPATH**/ ?>