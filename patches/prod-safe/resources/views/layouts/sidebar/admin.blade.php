{{-- ============================================================================
     PROD-SAFE BACKPORT — Sidebar Admin
     Baseline prod .200 (147ff58): 54 baris, cuma Dashboard + Buat Pengajuan.
     Backport: tambah collapse Data Pengajuan + 6 jenis submenu + Notifikasi + Profil.

     NON-METHOD-CALL: tidak panggil canAccessJenis(), sharedArsips(),
     canViewPrice() — semua method itu belum ada di User.php prod.
     NON-NEW-ROUTE: hanya reference route yang sudah registered di prod
     (admin.dashboard, admin.arsip.index, admin.notifications.index, admin.profile).

     Style: PRESERVE prod baseline class (`sidebar sidebar-dark`, custom-scrollbar-dark)
     supaya CSS existing di prod tetap apply.
     ============================================================================ --}}
<aside class="sidebar sidebar-dark d-flex flex-column h-100 shadow-lg">

    {{-- HEADER --}}
    <div class="sidebar-header p-4 d-flex align-items-center">
        <div class="bg-white rounded p-1 me-3 d-flex align-items-center justify-content-center shadow-sm"
             style="width: 40px; height: 40px;">
            @if($app_logo)
                <img src="{{ asset('storage/settings/' . $app_logo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            @else
                <img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            @endif
        </div>
        <div>
            <h6 class="mb-0 fw-bold text-white sidebar-title">{{ $app_name }}</h6>
            <small class="text-slate-500" style="font-size: 0.75rem;">Admin Panel</small>
        </div>
    </div>

    {{-- MENU WRAPPER --}}
    <div class="sidebar-menu flex-grow-1 overflow-auto p-3 custom-scrollbar-dark">
        <ul class="nav flex-column gap-2">

            <li class="nav-header text-xs fw-bold text-secondary text-uppercase mt-2 mb-1 ps-3">Menu Utama</li>

            {{-- DASHBOARD --}}
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill me-3 text-primary"></i>
                    <span class="fw-medium">Dashboard</span>
                </a>
            </li>

            <li class="nav-header text-xs fw-bold text-secondary text-uppercase mt-3 mb-1 ps-3">Operasional</li>

            {{-- DATA PENGAJUAN (collapse dgn 6 jenis submenu) --}}
            @php $isArsip = request()->is('admin/arsip*'); @endphp
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between {{ $isArsip ? 'text-white' : '' }}"
                   data-bs-toggle="collapse" href="#arsipMenu" aria-expanded="{{ $isArsip ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-clipboard2-data-fill me-3 text-warning"></i>
                        <span class="fw-medium">Data Pengajuan</span>
                    </div>
                    <i class="bi bi-chevron-down transition-icon" style="transition:transform .2s; {{ $isArsip ? 'transform:rotate(180deg);' : '' }}"></i>
                </a>
                <div class="collapse {{ $isArsip ? 'show' : '' }}" id="arsipMenu">
                    <ul class="nav flex-column ms-3 mt-1 ps-3 border-start border-secondary border-opacity-25">
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index') }}"
                               class="nav-link py-2 text-sm {{ request('jenis') == null && request()->routeIs('admin.arsip.index') ? 'text-warning fw-bold' : '' }}">
                                <i class="bi bi-stack me-2"></i>Semua Data
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Cancel']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis') == 'Cancel' ? 'text-warning fw-bold' : '' }}">
                                <i class="bi bi-trash3-fill me-2 text-danger"></i>Cancel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Adjust']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis') == 'Adjust' ? 'text-warning fw-bold' : '' }}">
                                <i class="bi bi-sliders2-vertical me-2 text-info"></i>Adjustment
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Mutasi_Billet']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis') == 'Mutasi_Billet' ? 'text-warning fw-bold' : '' }}">
                                <i class="bi bi-arrow-repeat me-2 text-primary"></i>Mutasi Billet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Mutasi_Produk']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis') == 'Mutasi_Produk' ? 'text-warning fw-bold' : '' }}">
                                <i class="bi bi-box-fill me-2 text-success"></i>Mutasi Produk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Internal_Memo']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis') == 'Internal_Memo' ? 'text-warning fw-bold' : '' }}">
                                <i class="bi bi-file-earmark-richtext-fill me-2 text-warning"></i>Internal Memo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Bundel']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis') == 'Bundel' ? 'text-warning fw-bold' : '' }}">
                                <i class="bi bi-collection-fill me-2 text-danger"></i>Bundel
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- BUAT PENGAJUAN — shortcut ke index dgn trigger modal via ?action=new --}}
            <li class="nav-item mt-1">
                <a href="{{ route('admin.arsip.index') }}?action=new"
                   class="nav-link d-flex align-items-center">
                    <i class="bi bi-plus-circle-fill me-3 text-success"></i>
                    <span class="fw-medium">Buat Pengajuan</span>
                </a>
            </li>

            <li class="nav-header text-xs fw-bold text-secondary text-uppercase mt-3 mb-1 ps-3">Sistem</li>

            {{-- NOTIFIKASI --}}
            <li class="nav-item">
                <a href="{{ route('admin.notifications.index') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-bell-fill me-3 text-info"></i>
                    <span class="fw-medium">Notifikasi</span>
                    @if(($unreadCount ?? 0) > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>

            {{-- PROFIL --}}
            <li class="nav-item">
                <a href="{{ route('admin.profile') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-badge-fill me-3 text-secondary"></i>
                    <span class="fw-medium">Profil Saya</span>
                </a>
            </li>

        </ul>
    </div>

    {{-- FOOTER --}}
    <div class="sidebar-footer p-3 border-top border-secondary border-opacity-25 bg-dark bg-opacity-25 text-center">
        <small class="text-secondary text-xs">© 2026 IT Submission V1.0</small>
    </div>

</aside>
