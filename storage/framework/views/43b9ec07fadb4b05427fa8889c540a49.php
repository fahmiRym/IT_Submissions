

<?php $__env->startSection('title', 'Tambah Departemen'); ?>
<?php $__env->startSection('page-title', '➕ Tambah Departemen'); ?>

<?php $__env->startSection('content'); ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST" action="<?php echo e(route('superadmin.departments.store')); ?>">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label">Nama Departemen</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        💾 Simpan
                    </button>

                    <a href="<?php echo e(route('superadmin.departments.index')); ?>" class="btn btn-secondary">
                        Kembali
                    </a>
                </div>

            </form>

        </div>
    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\e_arsip\resources\views\departments\create.blade.php ENDPATH**/ ?>