

<?php $__env->startSection('title', 'Tambah Manager'); ?>
<?php $__env->startSection('page-title', '➕ Tambah Manager'); ?>

<?php $__env->startSection('content'); ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('superadmin.managers.store')); ?>">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label">Nama Manager</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="<?php echo e(route('superadmin.managers.index')); ?>" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\e_arsip\resources\views\managers\create.blade.php ENDPATH**/ ?>