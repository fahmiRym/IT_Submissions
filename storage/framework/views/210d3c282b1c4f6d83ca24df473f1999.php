
<?php $approverUsers = $approverUsers ?? collect(); ?>
<div class="card border-0 shadow-sm rounded-3 mb-3 approver-card" data-jenis-select="<?php echo e($jenisSelectId); ?>">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-diagram-3-fill text-primary"></i>
            <h6 class="fw-bold text-primary mb-0" style="font-size:0.85rem;">Alur Persetujuan (Approver)</h6>
        </div>
        <p class="text-muted mb-3" style="font-size:0.7rem;">Pilih penanggung jawab tiap tahap. Persetujuan berjalan berurutan: Pemohon → SPV → Kabag → Manager → (Accounting) → Departemen IT.</p>

        <div class="row g-2">
            <?php $__currentLoopData = ['SPV' => 'SPV', 'Kabag' => 'Kabag', 'Manager' => 'Manager', 'Accounting' => 'Accounting']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-12 approver-field" data-role="<?php echo e($role); ?>">
                    <label class="form-label small fw-bold text-secondary mb-1"><?php echo e($label); ?></label>
                    <select name="approvers[<?php echo e($role); ?>]" class="form-select form-select-sm bg-light border-0">
                        <option value="">-- Pilih <?php echo e($label); ?> --</option>
                        <?php $__currentLoopData = $approverUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?><?php echo e($u->jabatan ? ' ('.$u->jabatan.')' : ''); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="text-muted mt-2" style="font-size:0.65rem;"><i class="bi bi-info-circle me-1"></i>Tahap <b>Departemen IT</b> otomatis (oleh Superadmin/Tim IT) sebagai persetujuan final.</div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const card = document.querySelector('.approver-card[data-jenis-select="<?php echo e($jenisSelectId); ?>"]');
    if (!card) return;
    const jenisSel = document.getElementById('<?php echo e($jenisSelectId); ?>');
    if (!jenisSel) return;

    function fieldsFor(jenis) {
        if (jenis === 'Produk_Baru') return [];            // langsung IT
        if (jenis === 'Adjust') return ['SPV','Kabag','Manager','Accounting'];
        return ['SPV','Kabag','Manager'];                  // selainnya
    }

    function apply() {
        const allow = fieldsFor(jenisSel.value);
        card.style.display = allow.length ? '' : 'none';
        card.querySelectorAll('.approver-field').forEach(f => {
            const role = f.getAttribute('data-role');
            const show = allow.includes(role);
            f.style.display = show ? '' : 'none';
            const sel = f.querySelector('select');
            if (sel) sel.disabled = !show; // disabled tidak ikut ter-submit
        });
    }

    jenisSel.addEventListener('change', apply);
    apply();
})();
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\e_arsip\resources\views/partials/_approver_select.blade.php ENDPATH**/ ?>