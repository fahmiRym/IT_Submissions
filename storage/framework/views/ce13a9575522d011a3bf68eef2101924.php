<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Dokumen - <?php echo e($arsip->no_registrasi); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f1f5f9; font-family: system-ui, sans-serif; }
        .wrap { max-width: 720px; margin: 24px auto; padding: 0 12px; }
        .sig-img { max-height: 70px; max-width: 180px; object-fit: contain; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="p-4 text-white" style="background: linear-gradient(135deg, #4f46e5, #3730a3);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-patch-check-fill fs-3"></i>
                <div>
                    <h5 class="fw-bold mb-0">Verifikasi Tanda Tangan Digital</h5>
                    <small class="opacity-75">Dokumen terverifikasi oleh sistem IT Submission</small>
                </div>
            </div>
        </div>

        <div class="p-4">
            <div class="row g-2 small mb-3">
                <div class="col-6"><span class="text-muted">No Registrasi</span><div class="fw-bold font-monospace"><?php echo e($arsip->no_registrasi); ?></div></div>
                <div class="col-6"><span class="text-muted">No Dokumen</span><div class="fw-bold font-monospace"><?php echo e($arsip->no_doc ?? '-'); ?></div></div>
                <div class="col-6"><span class="text-muted">Jenis</span><div class="fw-bold"><?php echo e(str_replace('_',' ',$arsip->jenis_pengajuan)); ?></div></div>
                <div class="col-6"><span class="text-muted">Pengaju</span><div class="fw-bold"><?php echo e($arsip->admin->name ?? '-'); ?></div></div>
                <div class="col-6"><span class="text-muted">Dept / Unit</span><div class="fw-bold"><?php echo e($arsip->department->name ?? '-'); ?> / <?php echo e($arsip->unit->name ?? '-'); ?></div></div>
                <div class="col-6"><span class="text-muted">Tanggal Pengajuan</span><div class="fw-bold"><?php echo e(optional($arsip->tgl_pengajuan)->format('d/m/Y')); ?></div></div>
            </div>

            <h6 class="fw-bold mb-2"><i class="bi bi-pen me-1"></i>Tanda Tangan (<?php echo e($signatures->count()); ?>)</h6>

            <?php $__empty_1 = true; $__currentLoopData = $signatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sig): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex align-items-center gap-3 p-2 mb-2 rounded-3 border bg-white">
                    <div class="text-center" style="width:190px;">
                        <?php if($sig->signatureUrl()): ?>
                            <img src="<?php echo e($sig->signatureUrl()); ?>" class="sig-img" alt="ttd">
                        <?php else: ?>
                            <span class="text-muted small fst-italic">[tanpa gambar]</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold"><?php echo e($sig->signer_name); ?></div>
                        <div class="small text-muted"><?php echo e($sig->role_label); ?> · <?php echo e(optional($sig->signed_at)->format('d/m/Y H:i')); ?> WIB</div>
                        <div class="small font-monospace text-muted text-truncate" style="max-width:300px;" title="<?php echo e($sig->hash); ?>">#<?php echo e(\Illuminate\Support\Str::limit($sig->hash, 24, '')); ?></div>
                    </div>
                    <div>
                        <?php if($sig->is_valid): ?>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="bi bi-check-circle-fill me-1"></i>VALID</span>
                        <?php else: ?>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="bi bi-x-circle-fill me-1"></i>TIDAK VALID</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="alert alert-warning border-0 small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Belum ada tanda tangan digital pada dokumen ini.</div>
            <?php endif; ?>

            <?php if($arsip->approvals->isNotEmpty()): ?>
                <h6 class="fw-bold mt-4 mb-2"><i class="bi bi-diagram-3 me-1"></i>Alur Persetujuan</h6>
                <?php echo $__env->make('partials._approval_timeline', ['arsip' => $arsip], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
        </div>

        <div class="px-4 py-3 bg-light border-top small text-muted text-center">
            Diverifikasi pada <?php echo e(now()->format('d/m/Y H:i')); ?> WIB
        </div>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\laragon\www\e_arsip\resources\views\verify\show.blade.php ENDPATH**/ ?>