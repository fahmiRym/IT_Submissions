<?php $__env->startSection('title', 'Persetujuan Saya'); ?>
<?php $__env->startSection('page-title', 'Persetujuan Saya'); ?>

<?php $__env->startSection('content'); ?>
<?php $rp = auth()->user()->role === 'superadmin' ? 'superadmin' : 'admin'; ?>

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3 d-flex align-items-center gap-2">
        <i class="bi bi-inbox-fill fs-4 text-primary"></i>
        <div>
            <h6 class="fw-bold mb-0">Menunggu Persetujuan Anda</h6>
            <small class="text-muted"><?php echo e($arsips->count()); ?> pengajuan perlu Anda tindak (setujui = tanda tangan digital).</small>
        </div>
    </div>
</div>

<?php if(session('success')): ?> <div class="alert alert-success border-0 shadow-sm"><?php echo e(session('success')); ?></div> <?php endif; ?>
<?php if(session('error')): ?> <div class="alert alert-danger border-0 shadow-sm"><?php echo e(session('error')); ?></div> <?php endif; ?>

<?php $__empty_1 = true; $__currentLoopData = $arsips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php $cur = $a->currentApproval(); ?>
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace"><?php echo e($a->no_registrasi); ?></span>
                        <span class="badge bg-light text-dark border"><?php echo e(str_replace('_',' ',$a->jenis_pengajuan)); ?></span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Tahap Anda: <?php echo e($cur->role_label ?? '-'); ?></span>
                    </div>
                    <div class="small text-muted mb-1">
                        Pengaju: <span class="fw-bold text-dark"><?php echo e($a->admin->name ?? '-'); ?></span> ·
                        <?php echo e($a->department->name ?? '-'); ?> / <?php echo e($a->unit->name ?? '-'); ?> ·
                        <?php echo e(optional($a->tgl_pengajuan)->format('d/m/Y')); ?>

                    </div>
                    <?php if($a->keterangan): ?>
                        <div class="small text-muted fst-italic">"<?php echo e(\Illuminate\Support\Str::limit($a->keterangan, 120)); ?>"</div>
                    <?php endif; ?>
                    <div class="mt-3">
                        <?php echo $__env->make('partials._approval_timeline', ['arsip' => $a], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
                <div class="col-lg-5 d-flex flex-column justify-content-center">
                    <?php if(!auth()->user()->hasSignature()): ?>
                        <div class="alert alert-warning border-0 small mb-2">
                            <i class="bi bi-exclamation-triangle me-1"></i>Atur specimen tanda tangan di
                            <a href="<?php echo e(route($rp.'.profile')); ?>" class="fw-bold">Profil</a> sebelum menyetujui.
                        </div>
                    <?php endif; ?>
                    <div class="d-flex gap-2">
                        <form action="<?php echo e(route($rp.'.arsip.approve', $a->id)); ?>" method="POST" class="flex-fill"
                              onsubmit="return confirm('Setujui & tanda tangani tahap <?php echo e($cur->role_label ?? ''); ?>?')">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold shadow-sm" <?php echo e(auth()->user()->hasSignature() ? '' : 'disabled'); ?>>
                                <i class="bi bi-check2-circle me-1"></i>Setujui & TTD
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger rounded-pill fw-bold" data-bs-toggle="collapse" data-bs-target="#reject<?php echo e($a->id); ?>">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="collapse mt-2" id="reject<?php echo e($a->id); ?>">
                        <form action="<?php echo e(route($rp.'.arsip.reject', $a->id)); ?>" method="POST"
                              onsubmit="return confirm('Tolak pengajuan ini?')">
                            <?php echo csrf_field(); ?>
                            <textarea name="note" class="form-control form-control-sm mb-2" rows="2" placeholder="Alasan penolakan (opsional)"></textarea>
                            <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold">Konfirmasi Tolak</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <i class="bi bi-check2-all fs-1 text-success opacity-50"></i>
            <h6 class="fw-bold mt-3">Tidak ada yang menunggu</h6>
            <p class="text-muted small mb-0">Semua pengajuan untuk Anda sudah ditindak.</p>
        </div>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\e_arsip\resources\views/approvals/index.blade.php ENDPATH**/ ?>