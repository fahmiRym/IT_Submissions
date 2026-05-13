<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?php echo $__env->yieldContent('title', config('app.name')); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <meta property="og:title" content="<?php echo $__env->yieldContent('title', config('app.name')); ?>" />
    <meta property="og:description"
        content="Aplikasi IT Submissions - Pengajuan Cancel, Adjustment, Internal Memo, Bundle, dan Mutasi" />
    <meta property="og:image" content="<?php echo e(asset('img/og-image.jpeg')); ?>" />
    <meta property="og:url" content="<?php echo e(url()->current()); ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    
    <link rel="icon" type="image/x-icon" href="<?php echo e($app_logo_url); ?>">
    <link rel="apple-touch-icon" href="<?php echo e($app_logo_url); ?>">

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    
    <link href="<?php echo e(asset('css/modern-theme.css')); ?>?v=<?php echo e(filemtime(public_path('css/modern-theme.css'))); ?>" rel="stylesheet">

    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Essential Layout Structure only */

        /* FIX BOOTSTRAP MODAL SCROLL */
        .modal-dialog {
            max-height: 95vh;
        }

        .modal-content {
            max-height: 95vh;
            display: flex;
            flex-direction: column;
        }

        .modal-body {
            overflow-y: auto;
            scrollbar-width: thin;
            max-height: calc(95vh - 160px);
        }

        /* ENSURE BODY IS NOT FLEX */
        body {
            display: block !important;
        }

        /* LAYOUT */
        .content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--body-bg);
            width: calc(100% - var(--sidebar-width));
        }

        main {
            padding: 1.5rem;
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Mobile Responsive Enhancements */
        @media (max-width: 991.98px) {
            main {
                padding: 1.25rem;
            }


            .topbar {
                padding: 0.75rem 1rem !important;
            }
        }

        @media (max-width: 576px) {
            .page-title-text {
                font-size: 1.1rem !important;
            }
        }

        /* GLOBAL TYPOGRAPHY SOFTENING */
        body {
            color: #334155;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: #1e293b;
        }

        .fw-bold {
            font-weight: 600 !important;
        }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>

    <div class="sidebar-overlay"
        onclick="document.querySelector('.sidebar').classList.remove('show'); document.querySelector('.sidebar-overlay').classList.remove('show')">
    </div>

    <script>
        // Check sidebar state before page renders to prevent flickering (Desktop only)
        (function() {
            const sidebarState = localStorage.getItem('sidebar-mini');
            if (sidebarState === 'true' && window.innerWidth >= 992) {
                document.documentElement.classList.add('sidebar-mini-active');
                document.addEventListener('DOMContentLoaded', function() {
                    document.body.classList.add('sidebar-mini');
                });
            }
        })();
    </script>
    
    <?php if(auth()->guard()->check()): ?>
        <?php if(auth()->user()->role === 'superadmin'): ?>
            <?php echo $__env->make('layouts.sidebar.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif(in_array(auth()->user()->role, ['admin', 'accounting'])): ?>
            <?php echo $__env->make('layouts.sidebar.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    <?php endif; ?>


    
    <div class="content">
        <nav class="topbar d-flex justify-content-between align-items-center px-4 py-3">
            <div class="d-flex align-items-center gap-2 gap-md-3">
                
                <button class="btn btn-light d-lg-none shadow-sm rounded-circle p-2"
                    onclick="document.querySelector('.sidebar').classList.add('show'); document.querySelector('.sidebar-overlay').classList.add('show')"
                    style="width: 40px; height: 40px; border: none;">
                    <i class="bi bi-list text-primary fs-5"></i>
                </button>
                <span class="fw-bold fs-5 text-dark page-title-text text-truncate ms-2"><?php echo $__env->yieldContent('page-title'); ?></span>
            </div>

            <?php if(auth()->guard()->check()): ?>
                <div class="d-flex align-items-center gap-3">
                    
                    <div class="dropdown">
                        <button
                            class="btn btn-light position-relative rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 42px; height: 42px; border: none;" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-bell-fill text-primary fs-5"></i>
                            <?php
                                $unreadCount = \App\Models\Notification::where('role_target', auth()->user()->role)
                                    ->where('is_read', false)
                                    ->count();
                            ?>
                            <span id="notification-badge"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?php echo e($unreadCount == 0 ? 'd-none' : ''); ?>"
                                style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                                <?php echo e($unreadCount > 9 ? '9+' : $unreadCount); ?>

                            </span>
                        </button>


                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0"
                            style="width: 320px; max-height: 400px; overflow-y: auto; border-radius: 12px;">
                            <div
                                class="p-3 border-bottom bg-primary text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-bell-fill me-2"></i>Notifikasi</h6>
                                <span id="dropdown-notification-badge"
                                    class="badge bg-white text-primary rounded-pill <?php echo e($unreadCount == 0 ? 'd-none' : ''); ?>"><?php echo e($unreadCount); ?></span>
                            </div>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php
                                    $notifications = \App\Models\Notification::where('role_target', auth()->user()->role)
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();
                                ?>
                                <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="p-3 border-bottom <?php echo e(!$notif->is_read ? 'bg-light' : ''); ?> hover-bg-light"
                                        style="cursor: pointer;">
                                        <div class="d-flex gap-2">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                                                style="width: 36px; height: 36px;">
                                                <i class="bi bi-info-circle-fill"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small text-dark"><?php echo e($notif->title); ?></div>
                                                <div class="small text-muted" style="font-size: 0.8rem;">
                                                    <?php echo e(Str::limit($notif->message, 60)); ?></div>
                                                <div class="text-muted mt-1" style="font-size: 0.7rem;">
                                                    <i class="bi bi-clock me-1"></i><?php echo e($notif->created_at->diffForHumans()); ?>

                                                </div>
                                            </div>
                                            <?php if(!$notif->is_read): ?>
                                                <div class="flex-shrink-0">
                                                    <span class="badge bg-primary rounded-circle"
                                                        style="width: 8px; height: 8px; padding: 0;"></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="p-4 text-center text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                        <small>Tidak ada notifikasi</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if($notifications->count() > 0): ?>
                                <div class="p-2 text-center border-top">
                                    <a href="<?php echo e(auth()->user()->role === 'superadmin' ? route('superadmin.notifications.index') : route('admin.notifications.index')); ?>"
                                        class="small text-primary text-decoration-none fw-bold">
                                        Lihat Semua Notifikasi
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="dropdown">
                        <button class="btn d-flex align-items-center gap-2 rounded-pill shadow-sm px-3 py-2"
                            style="background: white; border: 1px solid #e2e8f0;" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <?php if(auth()->user()->photo): ?>
                                <img src="<?php echo e(asset('profile_photos/' . auth()->user()->photo)); ?>" alt="Profile"
                                    class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center fw-bold"
                                    style="width: 32px; height: 32px; font-size: 0.9rem;">
                                    <?php echo e(substr(auth()->user()->name ?? 'U', 0, 1)); ?>

                                </div>
                            <?php endif; ?>
                            <div class="text-start d-none d-sm-block">
                                <div class="fw-semibold text-dark" style="font-size: 0.9rem; line-height: 1.2;">
                                    <?php echo e(auth()->user()->name ?? 'User'); ?></div>
                                <div style="font-size: 0.65rem; line-height: 1; font-weight: 800;">
                                    <?php
                                        $topRole = match(auth()->user()->role) {
                                            'superadmin' => ['text' => 'SUPER ADMIN', 'color' => '#dc2626'],
                                            'accounting' => ['text' => 'ACCOUNTING', 'color' => '#f59e0b'],
                                            default      => ['text' => 'ADMIN', 'color' => '#4f46e5'],
                                        };
                                    ?>
                                    <span style="color: <?php echo e($topRole['color']); ?>;"><?php echo e($topRole['text']); ?></span>
                                </div>
                            </div>
                            <i class="bi bi-chevron-down text-muted small"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2"
                            style="min-width: 220px; border-radius: 12px;">
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark"><?php echo e(auth()->user()->name); ?></div>
                                <div class="small text-muted"><?php echo e(auth()->user()->email); ?></div>
                            </div>

                            <a href="<?php echo e(auth()->user()->role === 'superadmin' ? route('superadmin.profile') : route('admin.profile')); ?>"
                                class="dropdown-item rounded-3 mt-2 d-flex align-items-center gap-2 py-2">
                                <i class="bi bi-person-circle text-primary"></i>
                                <span>Profil Saya</span>
                            </a>

                            <div class="dropdown-divider my-2"></div>

                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                    class="dropdown-item rounded-3 d-flex align-items-center gap-2 py-2 text-danger">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </nav>

        <main class="fade-in">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>

    <?php if(auth()->guard()->check()): ?>
        <audio id="notification-sound" src="<?php echo e(asset('audio/notif.mp3')); ?>" preload="auto"></audio>
        <script>
            // INITIALIZE SIDEBAR STATE IMMEDIATELY
            (function() {
                const sidebarStatus = localStorage.getItem('sidebar-mini');
                if (sidebarStatus === 'true') {
                    document.body.classList.add('sidebar-mini');
                }
            })();

            function toggleSidebar() {
                const isMini = document.body.classList.toggle('sidebar-mini');
                localStorage.setItem('sidebar-mini', isMini);
                
                // Close all collapses when switching to mini to keep it clean
                if (isMini) {
                    $('.sidebar .collapse').collapse('hide');
                }
                
                updateTooltips();
            }

            function updateTooltips() {
                const isMini = document.body.classList.contains('sidebar-mini');
                // Hanya targetkan nav-link utama (yang bukan di dalam collapse)
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('.sidebar > .sidebar-menu > .nav > .nav-item > .nav-link'));
                
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    const title = tooltipTriggerEl.querySelector('span') ? tooltipTriggerEl.querySelector('span').innerText : '';
                    const hasCollapse = tooltipTriggerEl.getAttribute('href') && tooltipTriggerEl.getAttribute('href').startsWith('#');
                    
                    if (isMini && title) {
                        // Jangan timpa data-bs-toggle jika sudah ada 'collapse'
                        if (!hasCollapse) {
                            tooltipTriggerEl.setAttribute('data-bs-toggle', 'tooltip');
                        }
                        
                        tooltipTriggerEl.setAttribute('data-bs-original-title', title);
                        tooltipTriggerEl.setAttribute('data-bs-placement', 'right');
                        
                        // Inisialisasi tooltip manual tanpa merusak atribut toggle lainnya
                        new bootstrap.Tooltip(tooltipTriggerEl, {
                            trigger: 'hover'
                        });
                    } else {
                        const tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                        if (tooltip) {
                            tooltip.dispose();
                        }
                        // Kembalikan ke collapse jika itu menu dropdown
                        if (hasCollapse) {
                            tooltipTriggerEl.setAttribute('data-bs-toggle', 'collapse');
                        } else {
                            tooltipTriggerEl.removeAttribute('data-bs-toggle');
                        }
                        tooltipTriggerEl.removeAttribute('data-bs-original-title');
                    }
                });
            }

            document.addEventListener("DOMContentLoaded", function () {
                // Initialize tooltips
                updateTooltips();

                let lastUnreadCount = <?php echo e($unreadCount ?? 0); ?>;

                setInterval(function () {
                    $.ajax({
                        url: "<?php echo e(route('notifications.check')); ?>",
                        type: "GET",
                        success: function (response) {
                            let currentCount = response.unreadCount;

                            if (currentCount > lastUnreadCount) {
                                let audio = document.getElementById("notification-sound");
                                if (audio) {
                                    audio.play().catch(function (error) {
                                        console.log("Suara dinonaktifkan oleh browser:", error);
                                    });
                                }
                            }

                            lastUnreadCount = currentCount;

                            let badge = document.getElementById("notification-badge");
                            let ddBadge = document.getElementById("dropdown-notification-badge");

                            if (currentCount > 0) {
                                if (badge) {
                                    badge.classList.remove('d-none');
                                    badge.innerText = currentCount > 9 ? '9+' : currentCount;
                                }
                                if (ddBadge) {
                                    ddBadge.classList.remove('d-none');
                                    ddBadge.innerText = currentCount;
                                }
                            } else {
                                if (badge) badge.classList.add('d-none');
                                if (ddBadge) ddBadge.classList.add('d-none');
                            }
                        }
                    });
                }, 10000);

                $('.collapse').on('show.bs.collapse', function () {
                    $(this).prev('.nav-link').find('.transition-icon').addClass('rotate-180');
                }).on('hide.bs.collapse', function () {
                    $(this).prev('.nav-link').find('.transition-icon').removeClass('rotate-180');
                });

                // Handle menu clicks in mini mode
                $(document).on('click', '.sidebar .nav-link[data-bs-toggle="collapse"]', function(e) {
                    if (document.body.classList.contains('sidebar-mini')) {
                        const target = $(this).attr('href');
                        
                        // Tutup menu collapse lain yang sedang terbuka
                        $('.sidebar .collapse.show').not(target).collapse('hide');
                        
                        // Sembunyikan tooltip pada ikon yang diklik agar tidak menutupi submenu
                        const tooltip = bootstrap.Tooltip.getInstance(this);
                        if (tooltip) tooltip.hide();
                    }
                });

                // Tambahan: Pastikan saat sidebar di-toggle (mini <-> full), semua menu tertutup dulu agar rapi
                window.addEventListener('resize', function() {
                    if (window.innerWidth < 992) {
                        document.body.classList.remove('sidebar-mini');
                    }
                });
            });
        </script>
    <?php endif; ?>
</body>

</html><?php /**PATH C:\laragon\www\e_arsip\resources\views/layouts/app.blade.php ENDPATH**/ ?>