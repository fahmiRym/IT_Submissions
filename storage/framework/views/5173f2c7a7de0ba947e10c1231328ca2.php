
<?php $steps = $arsip->relationLoaded('approvals') ? $arsip->approvals : $arsip->approvals()->get(); ?>
<?php if($steps->isNotEmpty()): ?>
<div class="d-flex flex-column gap-1">
    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $c = match($s->status) {
                'approved' => ['bg'=>'#dcfce7','text'=>'#166534','icon'=>'bi-check-circle-fill','label'=>'Disetujui'],
                'rejected' => ['bg'=>'#fee2e2','text'=>'#991b1b','icon'=>'bi-x-circle-fill','label'=>'Ditolak'],
                default    => ['bg'=>'#f1f5f9','text'=>'#475569','icon'=>'bi-clock','label'=>'Menunggu'],
            };
        ?>
        <div class="d-flex align-items-center gap-2 px-2 py-1 rounded-2" style="background: <?php echo e($c['bg']); ?>;">
            <i class="bi <?php echo e($c['icon']); ?>" style="color: <?php echo e($c['text']); ?>;"></i>
            <div class="flex-grow-1 lh-1">
                <span class="fw-bold" style="font-size:0.72rem; color: <?php echo e($c['text']); ?>;"><?php echo e($s->step_order); ?>. <?php echo e($s->role_label); ?></span>
                <span class="text-muted" style="font-size:0.66rem;">
                    — <?php echo e($s->approver->name ?? ($s->role_label === 'Departemen IT' ? 'Tim IT' : 'belum ditentukan')); ?>

                    <?php if($s->acted_at): ?> · <?php echo e($s->acted_at->format('d/m/Y H:i')); ?> <?php endif; ?>
                </span>
                <?php if($s->status === 'rejected' && $s->note): ?>
                    <div class="text-danger" style="font-size:0.64rem;">Alasan: <?php echo e($s->note); ?></div>
                <?php endif; ?>
            </div>
            <span class="badge" style="background: <?php echo e($c['text']); ?>; font-size:0.58rem;"><?php echo e(strtoupper($c['label'])); ?></span>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php else: ?>
    <div class="text-muted small fst-italic">Tidak ada alur persetujuan.</div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\e_arsip\resources\views\partials\_approval_timeline.blade.php ENDPATH**/ ?>