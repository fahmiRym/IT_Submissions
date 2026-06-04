<?php $__env->startSection('title', 'Log Audit Aktivitas'); ?>
<?php $__env->startSection('page-title', 'Detailed Audit Logs'); ?>

<?php $__env->startSection('content'); ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="<?php echo e(route('superadmin.activity-logs.index')); ?>" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-secondary">CARI DOKUMEN</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="form-control bg-light border-0"
                            placeholder="No. Registrasi atau No. Transaksi...">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">FILTER USER (EDITOR)</label>
                    <select name="user_id" class="form-select bg-light border-0 shadow-none">
                        <option value="">-- Semua Editor --</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($u->id); ?>" <?php echo e(request('user_id') == $u->id ? 'selected' : ''); ?>><?php echo e($u->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 fw-bold shadow-sm rounded-3"><i
                            class="bi bi-funnel-fill me-2"></i>FILTER</button>
                    <a href="<?php echo e(route('superadmin.activity-logs.index')); ?>"
                        class="btn btn-light border shadow-sm px-3 rounded-3"><i
                            class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-primary-dark"><i class="bi bi-shield-lock-fill text-success me-2"></i>Riwayat Log
                Aktivitas</h6>
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-bold" style="font-size: 0.7rem;">Total:
                <?php echo e($logs->total()); ?> Aktivitas</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr style="font-size: 0.75rem;">
                        <th class="ps-4 text-muted fw-bold">WAKTU & USER</th>
                        <th class="text-muted fw-bold">DOKUMEN</th>
                        <th class="text-muted fw-bold">AKSI</th>
                        <th class="text-muted fw-bold">DETAIL PERUBAHAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-bottom">
                            <td class="ps-4 py-3" style="min-width: 200px;">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 fw-bold me-2"
                                        style="font-size: 0.7rem;">
                                        <?php echo e($log->created_at->format('H:i')); ?> WIB
                                    </div>
                                    <span class="text-muted small fw-semibold"><?php echo e($log->created_at->format('d/m/Y')); ?></span>
                                </div>
                                <div class="d-flex align-items-center mt-2">
                                    <div class="bg-success rounded-circle me-2 d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                        style="width: 24px; height: 24px; font-size: 0.7rem;">
                                        <?php echo e(strtoupper(substr($log->user->name ?? 'S', 0, 1))); ?>

                                    </div>
                                    <div class="fw-bold text-dark" style="font-size: 0.8rem;"><?php echo e($log->user->name ?? 'System'); ?>

                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-dark" style="font-size: 0.85rem;">
                                    <?php echo e($log->arsip->no_registrasi ?? 'Deleted Doc'); ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?php echo e($log->arsip->no_transaksi ?? '-'); ?></div>
                                <div class="mt-1">
                                    <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.65rem;">
                                        <?php echo e($log->arsip->department->code ?? '-'); ?> / <?php echo e($log->arsip->unit->code ?? '-'); ?>

                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php
                                    $badgeClass = match ($log->action) {
                                        'created' => 'bg-success',
                                        'updated' => 'bg-info',
                                        'deleted' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                ?>
                                <span class="badge <?php echo e($badgeClass); ?> text-white rounded-pill px-3 py-1 shadow-sm"
                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <?php echo e(strtoupper($log->action)); ?>

                                </span>
                            </td>
                            <td class="pe-4" style="max-width: 400px;">
                                <?php if($log->action == 'updated'): ?>
                                    <div class="p-2 rounded bg-light border border-opacity-10" style="font-size: 0.75rem;">
                                        <?php $__currentLoopData = $log->new_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $newValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php 
                                                                            $oldValue = $log->old_values[$key] ?? 'null';
                                                // Format values if they are arrays or objects
                                                if (is_array($oldValue))
                                                    $oldValue = json_encode($oldValue);
                                                if (is_array($newValue))
                                                    $newValue = json_encode($newValue);
                                            ?>
                                            <div class="mb-2">
                                                    <span class="fw-bold text-primary"><?php echo e(strtoupper(str_replace('_', ' ', $key))); ?>:</span><br>
                                                    <span class="text-danger text-decoration-line-through opacity-75"><?php echo e($oldValue ?: '(empty)'); ?></span>
                                                        <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                                    <span class="text-success fw-bold"><?php echo e($newValue ?: '(empty)'); ?></span>
                                                    </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                <?php elseif($log->action == 'created'): ?>
                                    <span class="text-muted italic small">Data baru ditambahkan ke sistem.</span>
                                <?php elseif($log->action == 'deleted'): ?>
                                    <span class="text-danger small fw-bold">Data telah dihapus permanent.</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-slash display-4 opacity-25"></i>
                                <p class="mt-2 small">Belum ada riwayat aktivitas mendalam yang tercatat.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($logs->hasPages()): ?>
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-center mt-2">
                    <?php echo e($logs->links()); ?>

                </div>
            </div>



           <?php endif; ?>


           </div>

    <style>
        .text-primary-dark { color: #0f172a; }
        .table-responsive { scrollbar-width: thin; }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\e_arsip\resources\views\superadmin\activity_logs\index.blade.php ENDPATH**/ ?>