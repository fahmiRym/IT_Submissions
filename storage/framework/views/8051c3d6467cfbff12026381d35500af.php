

<?php $__env->startSection('title', 'Pengaturan Aplikasi'); ?>
<?php $__env->startSection('page-title', '⚙️ Pengaturan Aplikasi'); ?>

<?php $__env->startSection('content'); ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 mt-2 mx-2">
                    <h5 class="fw-bold mb-0">Identitas Aplikasi</h5>
                    <p class="text-muted small mb-0">Ubah nama dan logo perusahaan untuk branding sistem</p>
                </div>

                <?php if(session('success')): ?>
                    <div class="alert alert-success mx-4 mt-3 border-0 rounded-4 shadow-sm d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div><?php echo e(session('success')); ?></div>
                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="alert alert-danger mx-4 mt-3 border-0 rounded-4 shadow-sm">
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="card-body p-4 p-md-5">
                    <form action="<?php echo e(route('superadmin.settings.update')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <div class="row g-4">
                            
                            <div class="col-12">
                                <label class="form-label small fw-bold text-uppercase">Nama Aplikasi / Instansi</label>
                                <input type="text" name="app_name" class="form-control bg-light border-0 py-2 px-3"
                                    value="<?php echo e($app_name); ?>" placeholder="Contoh: IT Submissions - PT Inkasa Jaya Alluminium"
                                    required>
                            </div>

                            
                            <div class="col-12">
                                <label class="form-label small fw-bold text-uppercase">
                                    <i class="bi bi-geo-alt-fill text-primary me-1"></i>Location Company
                                </label>
                                <input type="text" name="kota_ba" class="form-control bg-light border-0 py-2 px-3"
                                    value="<?php echo e($kota_ba ?? 'PASURUAN'); ?>" placeholder="Contoh: PASURUAN">
                                <small class="text-muted">
                                    Nama kota ini akan muncul di footer Berita Acara, contoh: <strong>PASURUAN, 09 Mei
                                        2025</strong>
                                </small>
                            </div>

                            
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-uppercase">Logo Saat Ini</label>
                                <div class="bg-light rounded-4 p-3 d-flex align-items-center justify-content-center border"
                                    style="height: 150px;">
                                    <?php if($app_logo): ?>
                                        <img src="<?php echo e(asset('storage/settings/' . $app_logo)); ?>" alt="Logo" class="img-fluid"
                                            style="max-height: 100px;">
                                    <?php else: ?>
                                        <div class="text-center">
                                            <i class="bi bi-image text-muted display-4"></i>
                                            <p class="text-muted small mb-0 mt-2">Belum ada logo</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-uppercase">Ganti Logo Baru</label>
                                <input type="file" name="app_logo" class="form-control bg-light border-0 py-2 mb-2"
                                    accept="image/*">
                                <div class="alert alert-info border-0 rounded-3 py-2 px-3 mb-0" style="font-size: 0.75rem;">
                                    <i class="bi bi-info-circle-fill me-1"></i> Format direkomendasikan: <strong>PNG
                                        Transparan</strong> atau <strong>JPG</strong> (Maks. 2MB).
                                </div>
                            </div>

                            
                            <div class="col-12 mt-4">
                                <div class="bg-light rounded-4 p-4 border border-info border-opacity-25">
                                    <h6 class="fw-bold mb-3 text-info"><i class="bi bi-fonts me-2"></i>Kustomisasi Teks Watermark</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Status DONE / LENGKAP</label>
                                            <input type="text" name="wm_done" class="form-control border-0 shadow-sm" value="<?php echo e($wm_done); ?>" placeholder="Kosongkan jika tidak ingin watermark">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Status VOID / BATAL</label>
                                            <input type="text" name="wm_void" class="form-control border-0 shadow-sm" value="<?php echo e($wm_void); ?>" placeholder="Kosongkan jika tidak ingin watermark">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Status REJECT / TOLAK</label>
                                            <input type="text" name="wm_reject" class="form-control border-0 shadow-sm" value="<?php echo e($wm_reject); ?>" placeholder="Kosongkan jika tidak ingin watermark">
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-3" style="font-size: 0.7rem;">
                                        * Teks ini akan muncul secara diagonal di latar belakang cetakan dokumen (Berita Acara).
                                    </small>
                                </div>
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                                <a href="<?php echo e(route('superadmin.dashboard')); ?>"
                                    class="btn btn-light rounded-pill px-4">Batal</a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\e_arsip\resources\views\superadmin\settings\index.blade.php ENDPATH**/ ?>