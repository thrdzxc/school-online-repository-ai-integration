
<?php $__env->startSection('title','Subjects & Classrooms'); ?>
<?php $__env->startSection('topbar','Subjects & Classrooms'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div>
        <h1>Subjects & Classrooms</h1>
        <p>Manage subjects by name, adviser, and semester, with a classroom and enrollment key for each section.</p>
    </div>
</div>

<div class="subject-layout admin-subject-layout">
    <section class="card subject-create-card">
        <h3>Create Subject & Classroom</h3>
        <p class="small muted">Set up a subject and its first classroom together.</p>
        <form method="POST" action="<?php echo e(route('admin.subjects.store')); ?>"><?php echo csrf_field(); ?>
            <?php echo $__env->make('partials.subject-classroom-fields', ['showAdviser' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <button class="btn" type="submit" <?php if($advisers->isEmpty()): echo 'disabled'; endif; ?>>Create Subject & Classroom</button>
            <?php if($advisers->isEmpty()): ?><p class="small muted">Add an active adviser before creating a subject and classroom.</p><?php endif; ?>
        </form>
    </section>

    <section class="card">
        <div class="page-head" style="margin-bottom:14px">
            <div><h3 style="margin:0">Subject Directory</h3><p>Find subjects by name, section, adviser, or semester.</p></div>
        </div>

        <form method="GET" action="<?php echo e(route('admin.subjects')); ?>" class="subject-toolbar">
            <div class="field toolbar-wide">
                <label>Search</label>
                <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search subject name, course no., section, or instructor">
            </div>
            <div class="field">
                <label>Course No.</label>
                <select name="class_code"><option value="">All</option><?php $__currentLoopData = $classCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($code); ?>" <?php if(request('class_code')===$code): echo 'selected'; endif; ?>><?php echo e($code); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
            </div>
            <div class="field">
                <label>School Year</label>
                <select name="school_year"><option value="">All</option><?php $__currentLoopData = $schoolYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($year); ?>" <?php if(request('school_year')===$year): echo 'selected'; endif; ?>><?php echo e($year); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
            </div>
            <div class="field">
                <label>Semester</label>
                <select name="semester"><option value="">All</option><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sem); ?>" <?php if(request('semester')===$sem): echo 'selected'; endif; ?>><?php echo e($sem); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
            </div>
            <div class="field">
                <label>Adviser</label>
                <select name="adviser_id"><option value="">All</option><?php $__currentLoopData = $advisers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a->id); ?>" <?php if((string)request('adviser_id')===(string)$a->id): echo 'selected'; endif; ?>><?php echo e($a->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
            </div>
            <div class="actions">
                <button class="btn sm"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'search','size' => '15']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','size' => '15']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Filter</button>
                <a class="btn secondary sm" href="<?php echo e(route('admin.subjects')); ?>">Reset</a>
            </div>
        </form>

        <div class="subject-list">
        <?php $__empty_1 = true; $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <details class="subject-row">
                <summary>
                    <div class="subject-summary">
                        <div class="subject-class-badge"><?php echo e($s->class_code ?: 'No Class'); ?></div>
                        <div>
                            <span class="subject-name"><?php echo e($s->name); ?></span>
                        </div>
                        <div class="subject-stats">
                            <?php echo e($s->section ?: 'No section'); ?> · <?php echo e($s->students_count); ?> student(s)<br>
                            <?php echo e($s->rooms->count()); ?> room(s) · <?php echo e($s->activities_count); ?> activit<?php echo e($s->activities_count===1 ? 'y' : 'ies'); ?>

                        </div>
                    </div>
                </summary>
                <div class="subject-details">
                    <div class="actions" style="margin-bottom:14px"><a class="btn secondary sm" href="<?php echo e(route('admin.subjects.edit', $s)); ?>">Edit Subject</a></div>
                    <div class="subject-meta-grid">
                        <div class="subject-meta-box"><span>Adviser</span><strong><?php echo e($s->adviser->name); ?></strong></div>
                        <div class="subject-meta-box"><span>School Year</span><strong><?php echo e($s->school_year); ?></strong></div>
                        <div class="subject-meta-box"><span>Semester</span><strong><?php echo e($s->semester); ?></strong></div>
                        <div class="subject-meta-box"><span>Schedule / Room</span><strong><?php echo e($s->schedule ?: 'Not set'); ?> <?php if($s->room): ?> · <?php echo e($s->room); ?> <?php endif; ?></strong></div>
                    </div>

                    <h4 style="margin:8px 0">Classrooms & Enrollment Keys</h4>
                    <?php $__empty_2 = true; $__currentLoopData = $s->rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                        <div class="score-line" data-live-room="<?php echo e($room->id); ?>">
                            <div><strong data-room-field="name"><?php echo e($room->name); ?></strong><br><span class="small muted"><span data-room-field="section"><?php echo e($room->section ?: 'No section'); ?></span><span data-room-optional="physical_room" <?php if(!$room->physical_room): ?> hidden <?php endif; ?>> &middot; <span data-room-field="physical_room"><?php echo e($room->physical_room); ?></span></span><span data-room-optional="schedule" <?php if(!$room->schedule): ?> hidden <?php endif; ?>> &middot; <span data-room-field="schedule"><?php echo e($room->schedule); ?></span></span></span></div>
                            <div class="actions"><span class="room-key"><?php echo e($room->enrollment_key); ?></span><a class="btn secondary sm" href="<?php echo e(route('admin.rooms.edit', $room)); ?>">Edit Classroom</a></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                        <div class="empty">No room configured for this subject.</div>
                    <?php endif; ?>

                    <form class="subject-enroll-row" method="POST" action="<?php echo e(route('admin.subjects.enroll',$s)); ?>" style="margin-top:14px">
                        <?php echo csrf_field(); ?>
                        <div class="field"><label>Student</label><select name="student_id" required><option value="">Select student</option><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($student->id); ?>"><?php echo e($student->name); ?> <?php if($student->school_id): ?> · <?php echo e($student->school_id); ?> <?php endif; ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                        <div class="field"><label>Enrollment Room</label><select name="room_id" required><option value="">Select room</option><?php $__currentLoopData = $s->rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($room->id); ?>" data-room-label="<?php echo e($room->id); ?>"><?php echo e($room->name); ?> <?php if($room->section): ?> · <?php echo e($room->section); ?> <?php endif; ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                        <button class="btn sm" <?php if($s->rooms->isEmpty()): echo 'disabled'; endif; ?>>Enroll Student</button>
                    </form>

                    <?php if($s->students->isNotEmpty()): ?>
                        <div class="table-wrap" style="margin-top:14px"><table><thead><tr><th>Student</th><th>School ID</th><th>Assigned Room</th><th>Action</th></tr></thead><tbody>
                        <?php $__currentLoopData = $s->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($enrollment=$s->enrollments->firstWhere('student_id',$student->id)); ?>
                            <tr>
                                <td><strong><?php echo e($student->name); ?></strong></td>
                                <td><?php echo e($student->school_id ?: '—'); ?></td>
                                <td <?php if($enrollment?->room): ?> data-live-room="<?php echo e($enrollment->room->id); ?>" <?php endif; ?>><span data-room-field="name"><?php echo e($enrollment?->room?->name ?? 'Not assigned'); ?></span></td>
                                <td><form method="POST" action="<?php echo e(route('admin.subjects.unenroll',[$s,$student])); ?>" onsubmit="return confirm('Remove this student from the subject?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn danger sm">Remove</button></form></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody></table></div>
                    <?php endif; ?>
                </div>
            </details>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty">No subjects match the current filters.</div>
        <?php endif; ?>
        </div>

        <?php echo e($subjects->links('pagination.simple')); ?>

    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\School_Repository_2.0_Improved\resources\views/admin/subjects.blade.php ENDPATH**/ ?>