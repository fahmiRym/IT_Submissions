{{-- ============================================================================
     UNIFIED SIDEBAR ADMIN — safe di prod .200 (era April 2026) DAN dev .199 (modern)
     ----------------------------------------------------------------------------
     Struktur identik dgn dev/local (main branch), dgn defensive guards:
     - `Route::has('...')` untuk route yang belum ada di prod
     - `method_exists($u, 'method')` fallback true untuk User modern methods
       yang belum ada di User.php prod (canAccessJenis, sharedArsips)

     Di prod → menu yg route belum registered otomatis skip.
     Di dev → semua menu tampil normal.

     Deploy 2026-07-30 M-Sync (samakan sidebar dgn dev).
     Class `bg-white` trigger light-theme override di modern-theme.css prod
     (default `.sidebar` = dark #1e293b; `.sidebar.bg-white` = light putih).
     ============================================================================ --}}
<aside class="sidebar bg-white">

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
                @php
                    $roleBadge = match (auth()->user()->role) {
                        'superadmin' => ['text' => 'SUPER ADMIN', 'color' => '#dc2626'],
                        'accounting' => ['text' => 'ACCOUNTING', 'color' => '#f59e0b'],
                        default => ['text' => 'ADMIN', 'color' => '#4f46e5'],
                    };
                @endphp
                <small
                    style="font-size: 0.65rem; font-weight: 800; color: {{ $roleBadge['color'] }}; letter-spacing: 0.5px;">{{ $roleBadge['text'] }}</small>
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
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill text-primary"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-header">OPERASIONAL</li>

            {{-- ================= ARSIP PENGAJUAN =================
                 canAccessJenis fallback: kalau method belum ada (prod), tampilkan semua.
                 Kalau method ada (dev/local), cek permission per jenis.
            --}}
            @php
                $u = auth()->user();
                $hasAccessCheck = method_exists($u, 'canAccessJenis');
                $canAccess = fn($j) => !$hasAccessCheck || $u->canAccessJenis($j);
                $isArsip = request()->is('admin/arsip*');
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
                            <a href="{{ route('admin.arsip.index') }}"
                                class="nav-link py-2 {{ request('jenis') == null && request()->routeIs('admin.arsip.index') ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-stack text-secondary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Semua Data</span>
                            </a>
                        </li>
                        @if($canAccess('Cancel'))
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Cancel']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Cancel' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-trash3-fill text-danger"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Cancel</span>
                            </a>
                        </li>
                        @endif
                        @if($canAccess('Adjust'))
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Adjust']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Adjust' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-sliders2-vertical text-info"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Adjustment</span>
                            </a>
                        </li>
                        @endif
                        @if($canAccess('Mutasi_Billet'))
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Mutasi_Billet']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Mutasi_Billet' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-arrow-repeat text-primary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Mutasi Billet</span>
                            </a>
                        </li>
                        @endif
                        @if($canAccess('Mutasi_Produk'))
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Mutasi_Produk']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Mutasi_Produk' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-box-fill text-success"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Mutasi Produk</span>
                            </a>
                        </li>
                        @endif
                        @if($canAccess('Internal_Memo'))
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Internal_Memo']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Internal_Memo' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-file-earmark-richtext-fill text-warning"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Internal Memo</span>
                            </a>
                        </li>
                        @endif
                        @if($canAccess('Bundel'))
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Bundel']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Bundel' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-collection-fill text-danger"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Bundel</span>
                            </a>
                        </li>
                        @endif
                        {{-- Daftar Produk Baru — DIBEKUKAN SEMENTARA --}}
                        {{-- <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Produk_Baru']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Produk_Baru' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-box-seam-fill text-primary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Daftar Produk Baru</span>
                            </a>
                        </li> --}}
                    </ul>
                </div>
            </li>

            {{-- PERSETUJUAN SAYA — butuh arsip_approvals table (prod skip via Route::has) --}}
            @if(Route::has('admin.approvals.index'))
            <li class="nav-item">
                <a href="{{ route('admin.approvals.index') }}"
                    class="nav-link d-flex align-items-center {{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}">
                    <i class="bi bi-check2-square text-success"></i>
                    <span>Persetujuan Saya</span>
                    @if(($pendingApprovalCount ?? 0) > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingApprovalCount }}</span>
                    @endif
                </a>
            </li>
            @endif

            {{-- DIBAGIKAN KE SAYA — butuh arsip_shares table + method User::sharedArsips (prod skip) --}}
            @if(Route::has('admin.arsip.shared-inbox') && method_exists($u, 'sharedArsips'))
            <li class="nav-item">
                <a href="{{ route('admin.arsip.shared-inbox') }}"
                    class="nav-link d-flex align-items-center {{ request()->routeIs('admin.arsip.shared-inbox') ? 'active' : '' }}">
                    <i class="bi bi-inbox-fill text-info"></i>
                    <span>Dibagikan ke Saya</span>
                    @php $sharedCount = $u->sharedArsips()->count(); @endphp
                    @if($sharedCount > 0)
                        <span class="badge text-white ms-auto" style="background:linear-gradient(135deg,#06b6d4,#0891b2);">{{ $sharedCount }}</span>
                    @endif
                </a>
            </li>
            @endif

            {{-- MASTER HARGA — DISABLED (hidden per user request). Route + gate + tabel tetap ada,
                 tinggal unwrap komentar ini kalau nanti mau on-kan lagi. --}}
            {{--
            @if(Route::has('admin.prices.index'))
                @can('view-price')
                <li class="nav-header">ACCOUNTING</li>
                <li class="nav-item">
                    <a href="{{ route('admin.prices.index') }}"
                        class="nav-link {{ request()->routeIs('admin.prices.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-coin text-success"></i>
                        <span>Master Harga</span>
                    </a>
                </li>
                @endcan
            @endif
            --}}

            <li class="nav-header">SISTEM</li>
            <li class="nav-item">
                <a href="{{ route('admin.profile') ?? '#' }}"
                    class="nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-badge-fill text-secondary"></i>
                    <span>Profil Saya</span>
                </a>
            </li>

        </ul>
    </div>

</aside>
