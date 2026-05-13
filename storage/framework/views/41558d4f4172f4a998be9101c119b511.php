<aside class="sidebar">

    
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="bg-white rounded-3 p-1 me-2 d-flex align-items-center justify-content-center shadow-sm"
                style="width: 42px; height: 42px; min-width: 42px;">
                <?php if($app_logo ?? false): ?>
                    <img src="<?php echo e(asset('storage/settings/' . $app_logo)); ?>" alt="Logo"
                        style="width: 100%; height: 100%; object-fit: contain;">
                <?php else: ?>
                    <img src="<?php echo e(asset('img/logo.png')); ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                <?php endif; ?>
            </div>
            <div class="sidebar-title overflow-hidden">
                <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.95rem;">
                    <?php echo e($app_name ?? 'IT Submission'); ?>

                </h6>
                <small style="font-size: 0.65rem; font-weight: 800; color: #dc2626; letter-spacing: 0.5px;">SUPER ADMIN</small>
            </div>
        </div>
        
        
        <button class="btn btn-light d-none d-lg-flex shadow-sm rounded-circle p-0 align-items-center justify-content-center sidebar-toggle-in"
            onclick="toggleSidebar();"
            style="width: 32px; height: 32px; min-width: 32px; border: none; background: #f1f5f9;">
            <i class="bi bi-chevron-left text-primary" style="font-size: 0.9rem;"></i>
        </button>
    </div>

    
    <div class="sidebar-menu">
        <ul class="nav flex-column">

            <li class="nav-header">DASHBOARD</li>
            <li class="nav-item">
                <a href="<?php echo e(route('superadmin.dashboard')); ?>"
                    class="nav-link <?php echo e(request()->routeIs('superadmin.dashboard') ? 'active' : ''); ?>">
                    <i class="bi bi-grid-fill text-primary"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-header">OPERASIONAL</li>

            
            <?php
                $isArsip = request()->is('superadmin/arsip*');
            ?>

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between <?php echo e($isArsip ? 'bg-light text-primary' : ''); ?>"
                    data-bs-toggle="collapse" href="#arsipMenu" aria-expanded="<?php echo e($isArsip ? 'true' : 'false'); ?>">

                    <div class="d-flex align-items-center">
                        <i class="bi bi-clipboard2-data-fill <?php echo e($isArsip ? 'text-primary' : 'text-warning'); ?>"></i>
                        <span>Data Pengajuan</span>
                    </div>
                    <i class="bi bi-chevron-right transition-icon ms-auto <?php echo e($isArsip ? 'rotate-90' : ''); ?>"
                        style="font-size: 0.8rem; margin-right:0;"></i>
                </a>

                <div class="collapse <?php echo e($isArsip ? 'show' : ''); ?>" id="arsipMenu">
                    <ul class="nav flex-column ms-4 mt-2 ps-2 border-start border-secondary border-opacity-25">
                        <li class="nav-item">
                            <a href="<?php echo e(route('superadmin.arsip.index')); ?>"
                                class="nav-link py-2 <?php echo e(request('jenis') == null && request()->routeIs('superadmin.arsip.index') ? 'text-primary fw-bold' : ''); ?>">
                                <i class="bi bi-stack text-secondary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Semua Data</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('superadmin.arsip.index', ['jenis' => 'Cancel'])); ?>"
                                class="nav-link py-2 <?php echo e(request('jenis') == 'Cancel' ? 'text-primary fw-bold' : ''); ?>">
                                <i class="bi bi-trash3-fill text-danger"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Cancel</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('superadmin.arsip.index', ['jenis' => 'Adjust'])); ?>"
                                class="nav-link py-2 <?php echo e(request('jenis') == 'Adjust' ? 'text-primary fw-bold' : ''); ?>">
                                <i class="bi bi-sliders2-vertical text-info"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Adjustment</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('superadmin.arsip.index', ['jenis' => 'Mutasi_Billet'])); ?>"
                                class="nav-link py-2 <?php echo e(request('jenis') == 'Mutasi_Billet' ? 'text-primary fw-bold' : ''); ?>">
                                <i class="bi bi-arrow-repeat text-primary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Mutasi Billet</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('superadmin.arsip.index', ['jenis' => 'Mutasi_Produk'])); ?>"
                                class="nav-link py-2 <?php echo e(request('jenis') == 'Mutasi_Produk' ? 'text-primary fw-bold' : ''); ?>">
                                <i class="bi bi-box-fill text-success"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Mutasi Produk</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('superadmin.arsip.index', ['jenis' => 'Internal_Memo'])); ?>"
                                class="nav-link py-2 <?php echo e(request('jenis') == 'Internal_Memo' ? 'text-primary fw-bold' : ''); ?>">
                                <i class="bi bi-file-earmark-richtext-fill text-warning"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Internal Memo</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('superadmin.arsip.index', ['jenis' => 'Bundel'])); ?>"
                                class="nav-link py-2 <?php echo e(request('jenis') == 'Bundel' ? 'text-primary fw-bold' : ''); ?>">
                                <i class="bi bi-collection-fill text-danger"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Bundel</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            
            <li class="nav-item mt-2">
                <a href="<?php echo e(route('superadmin.laporan.index')); ?>"
                    class="nav-link <?php echo e(request()->routeIs('superadmin.laporan.*') ? 'active' : ''); ?>">
                    <i class="bi bi-file-earmark-bar-graph-fill text-success"></i>
                    <span>Laporan</span>
                </a>
            </li>

            <li class="nav-header">PENGATURAN UMUM</li>

            
            <?php
                $isMaster = request()->is('superadmin/departments*', 'superadmin/units*', 'superadmin/managers*', 'superadmin/users*', 'superadmin/locations*', 'superadmin/settings*');
            ?>

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between <?php echo e($isMaster ? 'bg-light text-primary' : ''); ?>"
                    data-bs-toggle="collapse" href="#masterMenu" aria-expanded="<?php echo e($isMaster ? 'true' : 'false'); ?>">

                    <div class="d-flex align-items-center">
                        <i class="bi bi-database-fill <?php echo e($isMaster ? 'text-primary' : 'text-info'); ?>"></i>
                        <span>Master Data</span>
                    </div>
                    <i class="bi bi-chevron-right transition-icon ms-auto <?php echo e($isMaster ? 'rotate-90' : ''); ?>"
                        style="font-size: 0.8rem; margin-right:0;"></i>
                </a>

                <div class="collapse <?php echo e($isMaster ? 'show' : ''); ?>" id="masterMenu">
                    <ul class="nav flex-column ms-4 mt-2 ps-2 border-start border-secondary border-opacity-25">
                        <li class="nav-item"><a href="<?php echo e(route('superadmin.locations.index')); ?>"
                                class="nav-link py-2 <?php echo e(request()->routeIs('superadmin.locations.*') ? 'text-primary fw-bold' : ''); ?>"><i
                                    class="bi bi-geo-fill text-secondary" style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Lokasi Fisik</span></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('superadmin.departments.index')); ?>"
                                class="nav-link py-2 <?php echo e(request()->routeIs('superadmin.departments.*') ? 'text-primary fw-bold' : ''); ?>"><i
                                    class="bi bi-building-fill text-secondary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i> <span>Departemen</span></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('superadmin.units.index')); ?>"
                                class="nav-link py-2 <?php echo e(request()->routeIs('superadmin.units.*') ? 'text-primary fw-bold' : ''); ?>"><i
                                    class="bi bi-boxes text-secondary" style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Unit</span></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('superadmin.managers.index')); ?>"
                                class="nav-link py-2 <?php echo e(request()->routeIs('superadmin.managers.*') ? 'text-primary fw-bold' : ''); ?>"><i
                                    class="bi bi-person-badge-fill text-secondary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i> <span>Manager</span></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('superadmin.users.index')); ?>"
                                class="nav-link py-2 <?php echo e(request()->routeIs('superadmin.users.*') ? 'text-primary fw-bold' : ''); ?>"><i
                                    class="bi bi-people-fill text-secondary" style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>User</span></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('superadmin.products.index')); ?>"
                                class="nav-link py-2 <?php echo e(request()->routeIs('superadmin.products.*') ? 'text-primary fw-bold' : ''); ?>"><i
                                    class="bi bi-box-seam-fill text-secondary" style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Master Produk</span> <span class="badge bg-primary ms-auto" style="font-size:0.5rem;">NEW</span></a></li>
                        <li class="nav-item mt-2"><a href="<?php echo e(route('superadmin.settings.index')); ?>"
                                class="nav-link py-2 text-warning fw-bold"><i class="bi bi-gear-fill text-warning"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i> <span>Pengaturan APP</span></a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-header">SYSTEM MONITOR</li>

            <li class="nav-item">
                <a href="<?php echo e(route('superadmin.activity-logs.index')); ?>" 
                    class="nav-link <?php echo e(request()->routeIs('superadmin.activity-logs.*') ? 'active' : ''); ?>">
                    <i class="bi bi-journal-text text-info"></i>
                    <span>Log Aktivitas</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?php echo e(route('superadmin.backup.index')); ?>"
                    class="nav-link <?php echo e(request()->is('superadmin/backup*') ? 'active' : ''); ?>">
                    <i class="bi bi-database-check text-primary"></i>
                    <span>Manajemen DB</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?php echo e(route('superadmin.server-stats.index')); ?>" 
                    class="nav-link <?php echo e(request()->routeIs('superadmin.server-stats.*') ? 'active' : ''); ?>">
                    <i class="bi bi-speedometer2 text-warning"></i>
                    <span>Statistik Server</span>
                </a>
            </li>
        </ul>
    </div>

    
    <div class="sidebar-footer border-top border-light px-3 py-3" style="background: #ffffff;">
        <a href="<?php echo e(route('superadmin.profile')); ?>" class="text-decoration-none">
            <div class="bg-light rounded-4 p-2 d-flex align-items-center gap-3 border border-light shadow-sm transition-hover" style="cursor: pointer;">
                <?php if(auth()->user()->photo): ?>
                    <img src="<?php echo e(asset('profile_photos/' . auth()->user()->photo)); ?>" alt="Profile" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; min-width: 38px; object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center shadow-sm fw-bold" style="width: 38px; height: 38px; min-width: 38px; font-size: 1rem;">
                        <?php echo e(substr(auth()->user()->name ?? 'S', 0, 1)); ?>

                    </div>
                <?php endif; ?>
                <div class="sidebar-title overflow-hidden">
                    <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.85rem;"><?php echo e(auth()->user()->name ?? 'Superadmin'); ?></h6>
                    <small class="text-truncate d-block fw-bold" style="font-size: 0.65rem; color: #dc2626;">
                        <i class="bi bi-shield-lock-fill me-1"></i>SUPER ADMIN
                    </small>
                </div>
            </div>
        </a>
    </div>
</aside><?php /**PATH C:\laragon\www\e_arsip\resources\views/layouts/sidebar/superadmin.blade.php ENDPATH**/ ?>