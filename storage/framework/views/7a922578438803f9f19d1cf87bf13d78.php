

<?php $__env->startSection('title', 'Edit Manager'); ?>
<?php $__env->startSection('page-title', '✏ Edit Manager'); ?>

<?php $__env->startSection('content'); ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('superadmin.managers.update', $manager->id)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="mb-3">
                <label class="form-label">Nama Manager</label>
                <input type="text" name="name"
                       value="<?php echo e($manager->name); ?>"
                       class="form-control"
                       required>
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="<?php echo e(route('superadmin.managers.index')); ?>" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\e_arsip\resources\views\managers\edit.blade.php ENDPATH**/ ?>