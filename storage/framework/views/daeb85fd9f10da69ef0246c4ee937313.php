

<?php $__env->startSection('title','Notifikasi'); ?>
<?php $__env->startSection('page-title','🔔 Notifikasi'); ?>

<?php $__env->startSection('content'); ?>

<div class="card shadow-sm">
    <div class="card-body">

        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border-bottom py-3 <?php echo e($n->is_read ? '' : 'bg-light'); ?>">
                <div class="fw-semibold"><?php echo e($n->title); ?></div>
                <div class="text-muted small"><?php echo e($n->message); ?></div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-secondary">
                        <?php echo e($n->created_at->diffForHumans()); ?>

                    </small>

                    <?php if(!$n->is_read): ?>
                        <form method="POST" action="<?php echo e(route('notifications.read',$n->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <button class="btn btn-sm btn-outline-primary">
                                Tandai dibaca
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center text-muted">
                Tidak ada notifikasi
            </div>
        <?php endif; ?>

    </div>
</div>

<div class="mt-3">
    <?php echo e($notifications->links('pagination::bootstrap-5')); ?>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\e_arsip\resources\views\notifications\index.blade.php ENDPATH**/ ?>