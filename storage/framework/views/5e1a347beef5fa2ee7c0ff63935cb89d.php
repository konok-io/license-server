
<?php $__env->startSection('title', 'Site Settings'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('status')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo e(session('status')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Everything here controls the public homepage. Changes are live immediately after saving.
    </div>
    <a href="<?php echo e(url('/')); ?>" target="_blank" class="btn btn-outline-accent btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i>Preview Homepage
    </a>
</div>

<form action="<?php echo e(route('admin.settings.update')); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $fields): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-collection me-2"></i><?php echo e($groupName); ?>

            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold"><?php echo e($field['label']); ?></label>
                            <?php if($field['type'] === 'textarea'): ?>
                                <textarea name="<?php echo e($field['key']); ?>" class="form-control" rows="2"><?php echo e(old($field['key'], $values[$field['key']] ?? '')); ?></textarea>
                            <?php else: ?>
                                <input type="text" name="<?php echo e($field['key']); ?>" class="form-control"
                                       value="<?php echo e(old($field['key'], $values[$field['key']] ?? '')); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="<?php echo e(url('/')); ?>" target="_blank" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-accent">
            <i class="bi bi-save me-1"></i>Save Settings
        </button>
    </div>
</form>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\license-server\resources\views/admin/settings/edit.blade.php ENDPATH**/ ?>