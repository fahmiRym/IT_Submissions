

<?php $__env->startSection('title', 'Notifikasi Sistem'); ?>
<?php $__env->startSection('page-title', '🔔 Notifikasi Sistem'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .notif-card {
        border-left: 4px solid transparent;
        transition: all 0.2s;
    }
    .notif-card:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
    }
    .notif-unread {
        background-color: #f0f9ff;
    }
    .notif-read {
        background-color: #fff;
    }
    
    /* Status Colors for Border */
    .border-info { border-left-color: #0dcaf0 !important; } /* Info general */
    .border-success { border-left-color: #198754 !important; } /* Approved */
    .border-danger { border-left-color: #dc3545 !important; } /* Rejected */
    .border-warning { border-left-color: #ffc107 !important; } /* Revision */

    .icon-box {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">🔔 Notifikasi Masuk</h5>
                <p class="text-muted small mb-0">Pantau aktivitas pengajuan dan sistem.</p>
            </div>
            <?php if($paginatedNotifications->count() > 0): ?>
            <span class="badge bg-light text-primary border rounded-pill px-3 py-2">
                Total: <?php echo e($paginatedNotifications->total()); ?>

            </span>
            <?php endif; ?>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $paginatedNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        // Determine type based on message content or title
                        $type = 'info';
                        $icon = 'bi-info-circle-fill';
                        $color = 'text-info';
                        $bgIcon = 'bg-info bg-opacity-10';
                        $borderClass = 'border-info';

                        if (Str::contains(strtolower($n->title), ['revisi', 'perbaikan'])) {
                            $type = 'warning'; $icon = 'bi-exclamation-triangle-fill'; $color = 'text-warning'; $bgIcon = 'bg-warning bg-opacity-10'; $borderClass = 'border-warning';
                        } elseif (Str::contains(strtolower($n->title), ['tolak', 'ditolak'])) {
                            $type = 'danger'; $icon = 'bi-x-circle-fill'; $color = 'text-danger'; $bgIcon = 'bg-danger bg-opacity-10'; $borderClass = 'border-danger';
                        } elseif (Str::contains(strtolower($n->title), ['setuju', 'disetujui', 'selesai'])) {
                            $type = 'success'; $icon = 'bi-check-circle-fill'; $color = 'text-success'; $bgIcon = 'bg-success bg-opacity-10'; $borderClass = 'border-success';
                        } elseif (Str::contains(strtolower($n->title), ['baru', 'pengajuan'])) {
                            $type = 'primary'; $icon = 'bi-file-earmark-plus-fill'; $color = 'text-primary'; $bgIcon = 'bg-primary bg-opacity-10'; $borderClass = 'border-primary';
                        }
                    ?>

                    <div class="list-group-item p-4 notif-card <?php echo e($n->is_read ? 'notif-read' : 'notif-unread'); ?> <?php echo e($borderClass); ?>">
                        <div class="d-flex gap-3">
                            
                            
                            <div class="flex-shrink-0">
                                <div class="icon-box <?php echo e($bgIcon); ?> <?php echo e($color); ?>">
                                    <i class="bi <?php echo e($icon); ?>"></i>
                                </div>
                            </div>

                            
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold mb-0 text-dark"><?php echo e($n->title); ?></h6>
                                    <small class="text-muted text-nowrap ms-2">
                                        <i class="bi bi-clock me-1"></i><?php echo e($n->created_at->diffForHumans()); ?>

                                    </small>
                                </div>
                                
                                <p class="text-secondary mb-2 small"><?php echo e($n->message); ?></p>

                                
                                <?php if($n->arsip): ?>
                                    <div class="bg-light rounded p-2 border d-inline-block mt-1 mb-2">
                                        <small class="d-block fw-bold text-dark">
                                            <i class="bi bi-file-text me-1"></i> Data Arsip:
                                        </small>
                                        <div class="d-flex flex-wrap gap-2 text-xs mt-1">
                                            <?php if($n->arsip->no_doc): ?>
                                                <span class="badge bg-white text-dark border font-monospace">Doc: <?php echo e($n->arsip->no_doc); ?></span>
                                            <?php endif; ?>
                                            <?php if($n->arsip->no_registrasi): ?>
                                                <span class="badge bg-white text-dark border font-monospace">Reg: <?php echo e($n->arsip->no_registrasi); ?></span>
                                            <?php endif; ?>
                                            <span class="badge bg-white text-dark border">
                                                Tahap: <?php echo e($n->arsip->ket_process); ?>

                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                
                                <div class="d-flex gap-2 mt-2">
                                    <?php if(!$n->is_read): ?>
                                        <form method="POST" action="<?php echo e(route('superadmin.notifications.read', $n->id)); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                            <button class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3">
                                                <i class="bi bi-check2-all me-1"></i> Tandai Dibaca
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic"><i class="bi bi-check2-all"></i> Dibaca</span>
                                    <?php endif; ?>

                                    <?php if($n->arsip_id): ?>
                                        <a href="<?php echo e(route('superadmin.arsip.index', ['search' => $n->arsip->no_registrasi ?? $n->arsip->no_doc])); ?>" 
                                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            Lihat Arsip <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="bi bi-bell-slash fs-1 text-muted opacity-25"></i>
                        </div>
                        <h6 class="fw-bold text-muted">Belum ada notifikasi</h6>
                        <p class="text-muted small">Semua aktivitas penting akan muncul di sini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            <?php echo e($paginatedNotifications->links('pagination::bootstrap-5')); ?>

        </div>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\e_arsip\resources\views\notifications\superadmin\index.blade.php ENDPATH**/ ?>