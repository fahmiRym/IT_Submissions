<aside class="sidebar">

    {{-- HEADER --}}
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="bg-white rounded-3 p-1 me-2 d-flex align-items-center justify-content-center shadow-sm"
                style="width: 42px; height: 42px; min-width: 42px;">
                @if($app_logo ?? false)
                    <img src="{{ asset('storage/settings/' . $app_logo) }}" alt="Logo"
                        style="width: 100%; height: 100%; object-fit: contain;">
                @else
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                @endif
            </div>
            <div class="sidebar-title overflow-hidden">
                <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.95rem;">
                    {{ $app_name ?? 'IT Submission' }}
                </h6>
                <small style="font-size: 0.65rem; font-weight: 800; color: #dc2626; letter-spacing: 0.5px;">SUPER ADMIN</small>
            </div>
        </div>
        
        {{-- Desktop Toggle Button inside Sidebar --}}
        <button class="btn btn-light d-none d-lg-flex shadow-sm rounded-circle p-0 align-items-center justify-content-center sidebar-toggle-in"
            onclick="toggleSidebar();"
            style="width: 32px; height: 32px; min-width: 32px; border: none; background: #f1f5f9;">
            <i class="bi bi-chevron-left text-primary" style="font-size: 0.9rem;"></i>
        </button>
    </div>

    {{-- MENU WRAPPER --}}
    <div class="sidebar-menu">
        <ul class="nav flex-column">

            <li class="nav-header">DASHBOARD</li>
            <li class="nav-item">
                <a href="{{ route('superadmin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill text-primary"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-header">OPERASIONAL</li>

            {{-- ================= ARSIP PENGAJUAN ================= --}}
            @php
                $isArsip = request()->is('superadmin/arsip*');
            @endphp

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between {{ $isArsip ? 'bg-light text-primary' : '' }}"
                    data-bs-toggle="collapse" href="#arsipMenu" aria-expanded="{{ $isArsip ? 'true' : 'false' }}">

                    <div class="d-flex align-items-center">
                        <i class="bi bi-clipboard2-data-fill {{ $isArsip ? 'text-primary' : 'text-warning' }}"></i>
                        <span>Data Pengajuan</span>
                    </div>
                    <i class="bi bi-chevron-right transition-icon ms-auto {{ $isArsip ? 'rotate-90' : '' }}"
                        style="font-size: 0.8rem; margin-right:0;"></i>
                </a>

                <div class="collapse {{ $isArsip ? 'show' : '' }}" id="arsipMenu">
                    <ul class="nav flex-column ms-4 mt-2 ps-2 border-start border-secondary border-opacity-25">
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index') }}"
                                class="nav-link py-2 {{ request('jenis') == null && request()->routeIs('superadmin.arsip.index') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-stack text-secondary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Semua Data</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Cancel']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Cancel' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-trash3-fill text-danger"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Cancel</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Adjust']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Adjust' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-sliders2-vertical text-info"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Adjustment</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Mutasi_Billet']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Mutasi_Billet' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-arrow-repeat text-primary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Mutasi Billet</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Mutasi_Produk']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Mutasi_Produk' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-box-fill text-success"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Mutasi Produk</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Internal_Memo']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Internal_Memo' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-file-earmark-richtext-fill text-warning"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Internal Memo</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Bundel']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Bundel' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-collection-fill text-danger"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Bundel</span>
                            </a>
                        </li>
                        {{-- Daftar Produk Baru — DIBEKUKAN SEMENTARA --}}
                        {{-- <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Produk_Baru']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Produk_Baru' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-box-seam-fill text-primary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Daftar Produk Baru</span>
                                <span class="badge bg-primary ms-auto" style="font-size:0.5rem;"></span>
                            </a>
                        </li> --}}
                    </ul>
                </div>
            </li>

            {{-- PERSETUJUAN --}}
            <li class="nav-item mt-2">
                <a href="{{ route('superadmin.approvals.index') }}"
                    class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.approvals.*') ? 'active' : '' }}">
                    <i class="bi bi-check2-square text-success"></i>
                    <span>Persetujuan (Final IT)</span>
                    @if(($pendingApprovalCount ?? 0) > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingApprovalCount }}</span>
                    @endif
                </a>
            </li>

            {{-- LAPORAN --}}
            <li class="nav-item mt-2">
                <a href="{{ route('superadmin.laporan.index') }}"
                    class="nav-link {{ request()->routeIs('superadmin.laporan.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph-fill text-success"></i>
                    <span>Laporan</span>
                </a>
            </li>

            <li class="nav-header">PENGATURAN UMUM</li>

            {{-- MASTER DATA --}}
            @php
                $isMaster = request()->is('superadmin/departments*', 'superadmin/units*', 'superadmin/managers*', 'superadmin/users*', 'superadmin/locations*', 'superadmin/settings*');
            @endphp

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between {{ $isMaster ? 'bg-light text-primary' : '' }}"
                    data-bs-toggle="collapse" href="#masterMenu" aria-expanded="{{ $isMaster ? 'true' : 'false' }}">

                    <div class="d-flex align-items-center">
                        <i class="bi bi-database-fill {{ $isMaster ? 'text-primary' : 'text-info' }}"></i>
                        <span>Master Data</span>
                    </div>
                    <i class="bi bi-chevron-right transition-icon ms-auto {{ $isMaster ? 'rotate-90' : '' }}"
                        style="font-size: 0.8rem; margin-right:0;"></i>
                </a>

                <div class="collapse {{ $isMaster ? 'show' : '' }}" id="masterMenu">
                    <ul class="nav flex-column ms-4 mt-2 ps-2 border-start border-secondary border-opacity-25">
                        <li class="nav-item">
                            <a href="{{ route('superadmin.locations.index') }}"
                               class="nav-link py-2 {{ request()->routeIs('superadmin.locations.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-geo-alt-fill" style="font-size:1rem; min-width:20px; margin-right:8px; color:#10b981;"></i>
                                <span>Lokasi Fisik</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.departments.index') }}"
                               class="nav-link py-2 {{ request()->routeIs('superadmin.departments.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-building-fill" style="font-size:1rem; min-width:20px; margin-right:8px; color:#3b82f6;"></i>
                                <span>Departemen</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.units.index') }}"
                               class="nav-link py-2 {{ request()->routeIs('superadmin.units.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-diagram-3-fill" style="font-size:1rem; min-width:20px; margin-right:8px; color:#8b5cf6;"></i>
                                <span>Unit</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.managers.index') }}"
                               class="nav-link py-2 {{ request()->routeIs('superadmin.managers.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-person-badge-fill" style="font-size:1rem; min-width:20px; margin-right:8px; color:#f59e0b;"></i>
                                <span>Manager</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.users.index') }}"
                               class="nav-link py-2 {{ request()->routeIs('superadmin.users.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-people-fill" style="font-size:1rem; min-width:20px; margin-right:8px; color:#ec4899;"></i>
                                <span>User</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.pengajuan-access.index') }}"
                               class="nav-link py-2 d-flex align-items-center {{ request()->routeIs('superadmin.pengajuan-access.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-shield-lock-fill" style="font-size:1rem; min-width:20px; margin-right:8px; color:#7c3aed;"></i>
                                <span>Akses Pengajuan</span>
                                <span class="badge text-white ms-auto" style="font-size:0.5rem; background:linear-gradient(135deg,#7c3aed,#5b21b6);">NEW</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('superadmin.products.index') }}"
                               class="nav-link py-2 d-flex align-items-center {{ request()->routeIs('superadmin.products.*') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-box-seam-fill" style="font-size:1rem; min-width:20px; margin-right:8px; color:#06b6d4;"></i>
                                <span>Master Produk</span>
                                <span class="badge bg-gradient text-white ms-auto" style="font-size:0.5rem; background:linear-gradient(135deg,#06b6d4,#0891b2);"></span>
                            </a>
                        </li>
                        <li class="nav-item mt-2">
                            <a href="{{ route('superadmin.settings.index') }}"
                               class="nav-link py-2 {{ request()->routeIs('superadmin.settings.*') ? 'fw-bold text-primary' : 'text-warning fw-bold' }}">
                                <i class="bi bi-gear-fill" style="font-size:1rem; min-width:20px; margin-right:8px; color:#f97316;"></i>
                                <span>Pengaturan APP</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-header">SYSTEM MONITOR</li>

            <li class="nav-item">
                <a href="{{ route('superadmin.activity-logs.index') }}" 
                    class="nav-link {{ request()->routeIs('superadmin.activity-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text text-info"></i>
                    <span>Log Aktivitas</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.backup.index') }}"
                    class="nav-link {{ request()->is('superadmin/backup*') ? 'active' : '' }}">
                    <i class="bi bi-database-check text-primary"></i>
                    <span>Manajemen DB</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.server-stats.index') }}"
                    class="nav-link {{ request()->routeIs('superadmin.server-stats.*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 text-warning"></i>
                    <span>Statistik Server</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.app-versions.index') }}"
                    class="nav-link {{ request()->routeIs('superadmin.app-versions.*') ? 'active' : '' }}">
                    <i class="bi bi-android2 text-success"></i>
                    <span>Kelola APK Android</span>
                </a>
            </li>
        </ul>
    </div>

</aside>