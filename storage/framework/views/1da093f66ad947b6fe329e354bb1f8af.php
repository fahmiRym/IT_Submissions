<?php $__env->startSection('title', 'Statistik Server'); ?>
<?php $__env->startSection('page-title', 'Server Health & Metrics'); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-4">
    
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 text-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-hdd-fill fs-3"></i>
                </div>
                <h6 class="fw-bold text-secondary mb-1">Disk Storage</h6>
                <h3 class="fw-bold text-dark mb-3"><?php echo e($stats['disk_used_percent']); ?>% <small class="text-muted" style="font-size: 0.8rem;">Used</small></h3>
                <div class="progress rounded-pill mb-3" style="height: 10px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo e($stats['disk_used_percent']); ?>%"></div>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Free: <?php echo e($stats['disk_free']); ?></span>
                    <span class="text-muted">Total: <?php echo e($stats['disk_total']); ?></span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 text-center">
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-database-fill fs-3"></i>
                </div>
                <h6 class="fw-bold text-secondary mb-1">Database Size</h6>
                <h3 class="fw-bold text-dark mb-1"><?php echo e($stats['database_size']); ?></h3>
                <p class="text-muted small">Current e-Arsip database volume</p>
                <div class="bg-light p-2 rounded-3 text-start mt-3">
                    <div class="d-flex justify-content-between extra-small">
                        <span class="text-muted">Connection:</span>
                        <span class="fw-bold text-dark">MySQL/MariaDB</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-12 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-info-circle-fill text-info me-2"></i>Software Environment</h6>
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0">
                        <span class="text-muted">PHP Version</span>
                        <span class="badge bg-light text-dark border"><?php echo e($stats['php_version']); ?></span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0">
                        <span class="text-muted">Laravel Framework</span>
                        <span class="badge bg-light text-dark border">v<?php echo e($stats['laravel_version']); ?></span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0">
                        <span class="text-muted">Operating System</span>
                        <span class="badge bg-light text-dark border"><?php echo e($stats['os']); ?></span>
                    </div>
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0">
                        <span class="text-muted">Web Server</span>
                        <span class="badge bg-light text-dark border text-truncate" style="max-width: 150px;"><?php echo e($stats['server_software']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\e_arsip\resources\views/superadmin/server_stats/index.blade.php ENDPATH**/ ?>