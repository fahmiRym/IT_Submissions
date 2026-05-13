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

            {{-- ================= ARSIP PENGAJUAN ================= --}}
            @php
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
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Cancel']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Cancel' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-trash3-fill text-danger"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Cancel</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Adjust']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Adjust' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-sliders2-vertical text-info"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Adjustment</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Mutasi_Billet']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Mutasi_Billet' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-arrow-repeat text-primary"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Mutasi Billet</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Mutasi_Produk']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Mutasi_Produk' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-box-fill text-success"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Mutasi Produk</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Internal_Memo']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Internal_Memo' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-file-earmark-richtext-fill text-warning"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Internal Memo</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.arsip.index', ['jenis' => 'Bundel']) }}"
                                class="nav-link py-2 {{ request('jenis') == 'Bundel' ? 'text-primary fw-bold' : '' }}">
                                <i class="bi bi-collection-fill text-danger"
                                    style="font-size:1rem; min-width:20px; margin-right:8px;"></i>
                                <span>Bundel</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-header">SISTEM</li>
            <li class="nav-item">
                <a href="{{ route('admin.profile') ?? '#' }}"
                    class="nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-badge-fill text-secondary"></i>
                    <span>Profil Saya</span>
                </a>
            </li>

            {{-- Menu khusus Accounting --}}
            @if(auth()->user()->role === 'accounting')
                <li class="nav-header">ACCOUNTING</li>
                <li class="nav-item">
                    <a href="{{ route('admin.arsip.index', ['jenis' => 'Adjust']) }}"
                        class="nav-link {{ request('jenis') == 'Adjust' && request()->routeIs('admin.arsip.index') ? 'active' : '' }}">
                        <i class="bi bi-upload text-warning"></i>
                        <span>Upload Scan BA Adjust</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>

    {{-- BOTTOM PROFILE (Premium Sidebar Addition) --}}
    <div class="sidebar-footer border-top border-light px-3 py-3" style="background: #ffffff;">
        <a href="{{ route('admin.profile') }}" class="text-decoration-none">
            <div class="bg-light rounded-4 p-2 d-flex align-items-center gap-3 border border-light shadow-sm transition-hover"
                style="cursor: pointer;">
                @if(auth()->user()->photo)
                    <img src="{{ asset('profile_photos/' . auth()->user()->photo) }}" alt="Profile"
                        class="rounded-circle shadow-sm"
                        style="width: 38px; height: 38px; min-width: 38px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center shadow-sm fw-bold"
                        style="width: 38px; height: 38px; min-width: 38px; font-size: 1rem;">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                @endif
                <div class="sidebar-title overflow-hidden">
                    <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.85rem;">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </h6>
                    @php
                        $footerRole = match (auth()->user()->role) {
                            'superadmin' => ['text' => 'SUPER ADMIN', 'color' => '#dc2626', 'icon' => 'bi-shield-lock-fill'],
                            'accounting' => ['text' => 'ACCOUNTING', 'color' => '#f59e0b', 'icon' => 'bi-calculator-fill'],
                            default => ['text' => 'ADMIN', 'color' => '#4f46e5', 'icon' => 'bi-person-check-fill'],
                        };
                    @endphp
                    <small class="text-truncate d-block fw-bold"
                        style="font-size: 0.65rem; color: {{ $footerRole['color'] }};">
                        <i class="bi {{ $footerRole['icon'] }} me-1"></i>{{ $footerRole['text'] }}
                    </small>
                </div>
            </div>
        </a>
    </div>
</aside>