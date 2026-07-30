{{-- ============================================================================
     PROD-SAFE BACKPORT — Sidebar Superadmin
     Baseline prod .200 (147ff58): 158 baris, sudah punya struktur bagus.
     Backport: ganti emoji → Bootstrap Icons colored, tambah Backup menu di section
     Sistem, preserve semua route existing.

     NON-METHOD-CALL: tidak panggil canAccessJenis, sharedArsips, canViewPrice.
     NON-NEW-ROUTE: hanya reference route registered di prod
     (superadmin.dashboard, arsip.index dgn ?jenis=X, laporan, locations, departments,
      units, managers, users, settings, backup, notifications, profile).

     Skip: approvals, pengajuan-access, products, activity-logs, server-stats,
     app-versions — route belum ada di prod (butuh migration).
     ============================================================================ --}}
<aside class="sidebar bg-white shadow-sm d-flex flex-column h-100">

    {{-- ================= HEADER ================= --}}
    <div class="sidebar-header p-4 d-flex align-items-center">
        <div class="bg-white rounded p-1 me-3 d-flex align-items-center justify-content-center shadow-sm"
             style="width:40px;height:40px">
            @if($app_logo)
                <img src="{{ asset('storage/settings/' . $app_logo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            @else
                <img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            @endif
        </div>
        <div>
            <h6 class="mb-0 fw-bold text-dark">{{ $app_name }}</h6>
            <small class="text-slate-500 text-xs">Superadmin Panel</small>
        </div>
    </div>

    {{-- ================= MENU ================= --}}
    <div class="sidebar-menu flex-grow-1 overflow-auto p-3 custom-scrollbar">
        <ul class="nav flex-column gap-1">

            {{-- DASHBOARD --}}
            <li class="nav-item">
                <a href="{{ route('superadmin.dashboard') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill me-3 text-primary"></i>
                    <span class="fw-medium">Dashboard</span>
                </a>
            </li>

            <li class="nav-header text-xs fw-bold text-slate-500 text-uppercase mt-4 mb-2 ps-3">
                Operasional
            </li>

            {{-- ================= ARSIP PENGAJUAN ================= --}}
            @php $isArsip = request()->is('superadmin/arsip*'); @endphp
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between"
                   data-bs-toggle="collapse" href="#arsipMenu" aria-expanded="{{ $isArsip ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-folder2-open me-3 text-warning"></i>
                        <span class="fw-medium">Data Pengajuan</span>
                    </div>
                    <i class="bi bi-chevron-down transition-icon" style="transition:transform .2s; {{ $isArsip ? 'transform:rotate(180deg);' : '' }}"></i>
                </a>

                <div class="collapse {{ $isArsip ? 'show' : '' }}" id="arsipMenu">
                    <ul class="nav flex-column ms-3 mt-1 ps-3 border-start border-2">
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index') }}"
                               class="nav-link py-2 text-sm {{ request('jenis') == null && request()->routeIs('superadmin.arsip.index') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-stack me-2 text-secondary"></i>Semua Data
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Cancel']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Cancel' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-trash3-fill me-2 text-danger"></i>Cancel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Adjust']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Adjust' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-sliders2-vertical me-2 text-info"></i>Adjustment
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Mutasi_Billet']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Mutasi_Billet' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-arrow-repeat me-2 text-primary"></i>Mutasi Billet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Mutasi_Produk']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Mutasi_Produk' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-box-fill me-2 text-success"></i>Mutasi Produk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Internal_Memo']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Internal_Memo' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-file-earmark-richtext-fill me-2 text-warning"></i>Internal Memo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Bundel']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Bundel' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-collection-fill me-2 text-danger"></i>Bundel
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- LAPORAN --}}
            <li class="nav-item mt-1">
                <a href="{{ route('superadmin.laporan.index') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.laporan.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph-fill me-3 text-success"></i>
                    <span class="fw-medium">Laporan</span>
                </a>
            </li>

            <li class="nav-header text-xs fw-bold text-slate-500 text-uppercase mt-4 mb-2 ps-3">
                Master Data
            </li>

            {{-- MASTER DATA --}}
            @php
                $isMaster = request()->is('superadmin/departments*', 'superadmin/units*', 'superadmin/managers*', 'superadmin/users*', 'superadmin/locations*');
            @endphp
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between"
                   data-bs-toggle="collapse" href="#masterMenu" aria-expanded="{{ $isMaster ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-database-fill me-3 text-info"></i>
                        <span class="fw-medium">Master Data</span>
                    </div>
                    <i class="bi bi-chevron-down transition-icon" style="transition:transform .2s; {{ $isMaster ? 'transform:rotate(180deg);' : '' }}"></i>
                </a>
                <div class="collapse {{ $isMaster ? 'show' : '' }}" id="masterMenu">
                    <ul class="nav flex-column ms-3 mt-1 ps-3 border-start border-2">
                        <li class="nav-item">
                            <a href="{{ route('superadmin.locations.index') }}"
                               class="nav-link py-2 text-sm {{ request()->routeIs('superadmin.locations.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-geo-alt-fill me-2 text-danger"></i>Lokasi Fisik
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.departments.index') }}"
                               class="nav-link py-2 text-sm {{ request()->routeIs('superadmin.departments.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-building-fill me-2 text-primary"></i>Departemen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.units.index') }}"
                               class="nav-link py-2 text-sm {{ request()->routeIs('superadmin.units.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-box-seam-fill me-2 text-warning"></i>Unit
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.managers.index') }}"
                               class="nav-link py-2 text-sm {{ request()->routeIs('superadmin.managers.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-person-check-fill me-2 text-success"></i>Manager
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.users.index') }}"
                               class="nav-link py-2 text-sm {{ request()->routeIs('superadmin.users.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-people-fill me-2 text-info"></i>User
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-header text-xs fw-bold text-slate-500 text-uppercase mt-4 mb-2 ps-3">
                Sistem
            </li>

            {{-- BACKUP & RESTORE (route existing di prod) --}}
            <li class="nav-item">
                <a href="{{ route('superadmin.backup.index') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.backup.*') ? 'active' : '' }}">
                    <i class="bi bi-database-fill-gear me-3 text-primary"></i>
                    <span class="fw-medium">Backup & Restore</span>
                </a>
            </li>

            {{-- MAINTENANCE MODE (baru — deploy 2026-07-30) --}}
            @if(Route::has('superadmin.maintenance.index'))
            <li class="nav-item">
                <a href="{{ route('superadmin.maintenance.index') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.maintenance.*') ? 'active' : '' }}">
                    <i class="bi bi-tools me-3 text-danger"></i>
                    <span class="fw-medium">Maintenance Mode</span>
                    @if(app()->isDownForMaintenance())
                        <span class="badge bg-danger rounded-pill ms-auto">ON</span>
                    @endif
                </a>
            </li>
            @endif

            {{-- LOG AKTIVITAS (M1 — deploy 2026-07-30) --}}
            @if(Route::has('superadmin.activity-logs.index'))
            <li class="nav-item">
                <a href="{{ route('superadmin.activity-logs.index') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.activity-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text me-3 text-info"></i>
                    <span class="fw-medium">Log Aktivitas</span>
                </a>
            </li>
            @endif

            {{-- PENGATURAN --}}
            <li class="nav-item">
                <a href="{{ route('superadmin.settings.index') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill me-3 text-secondary"></i>
                    <span class="fw-medium">Pengaturan</span>
                </a>
            </li>

            {{-- NOTIFIKASI --}}
            <li class="nav-item">
                <a href="{{ route('superadmin.notifications.index') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-bell-fill me-3 text-warning"></i>
                    <span class="fw-medium">Notifikasi</span>
                    @if(($unreadCount ?? 0) > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>

            {{-- PROFIL --}}
            <li class="nav-item">
                <a href="{{ route('superadmin.profile') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-badge-fill me-3 text-secondary"></i>
                    <span class="fw-medium">Profil Saya</span>
                </a>
            </li>

        </ul>
    </div>

    {{-- FOOTER --}}
    <div class="sidebar-footer p-3 border-top bg-light text-center">
        <small class="text-slate-500 text-xs">© 2026 IT Submission V1.0</small>
    </div>
</aside>
