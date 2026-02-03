<aside class="sidebar sidebar-dark d-flex flex-column h-100 shadow-lg">
    
    {{-- HEADER --}}
    <div class="sidebar-header p-4 d-flex align-items-center">
        <div class="bg-primary text-white rounded p-2 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
            <i class="bi bi-shield-lock-fill fs-5"></i>
        </div>
        <div>
            <h6 class="mb-0 fw-bold text-white sidebar-title">IT - Submission</h6>
            <small class="text-slate-500" style="font-size: 0.75rem;">Admin Panel</small>
        </div>
    </div>

    {{-- MENU WRAPPER --}}
    <div class="sidebar-menu flex-grow-1 overflow-auto p-3 custom-scrollbar-dark">
        <ul class="nav flex-column gap-2">

            {{-- NOTIFICATION DROPDOWN --}}
            <li class="nav-item dropdown mb-3">
                <a class="nav-link d-flex align-items-center justify-content-between px-3 py-2 rounded-3 text-light bg-dark bg-opacity-50 border border-secondary border-opacity-25" 
                   data-bs-toggle="dropdown" style="cursor: pointer;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-bell-fill me-3 text-warning"></i>
                        <span class="fw-medium text-sm">Notifikasi</span>
                    </div>
                    @if(($unreadCount ?? 0) > 0)
                    <span class="badge bg-danger rounded-pill px-2">
                        {{ $unreadCount }}
                    </span>
                    @endif
                </a>

                <div class="dropdown-menu dropdown-menu-dark shadow-lg border-0 mt-2 p-0 w-100 overflow-hidden" style="z-index: 1050;">
                    <div class="p-3 border-bottom border-secondary border-opacity-25 fw-semibold text-white bg-dark">
                        🔔 Info Terbaru
                    </div>
                    <div style="max-height: 250px; overflow-y: auto;">
                        @forelse(($notifications ?? []) as $n)
                            <div class="p-3 border-bottom border-secondary border-opacity-25 small text-light hover-bg-dark">
                                {{ $n->message }}
                            </div>
                        @empty
                            <div class="p-3 text-center text-secondary small">
                                Tidak ada notifikasi baru
                            </div>
                        @endforelse
                    </div>
                    <div class="p-2 text-center bg-dark">
                        <a href="{{ route('notifications.index') }}" class="small text-primary text-decoration-none fw-bold">
                            Lihat Semua
                        </a>
                    </div>
                </div>
            </li>
            
            <li class="nav-header text-xs fw-bold text-secondary text-uppercase mt-2 mb-1 ps-3">Menu Utama</li>

            {{-- DASHBOARD --}}
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" 
                   class="nav-link d-flex align-items-center {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill me-3"></i>
                    <span class="fw-medium">Dashboard</span>
                </a>
            </li>

            {{-- ARSIP --}}
            <li class="nav-item">
                <a href="{{ route('admin.arsip.index') }}" 
                   class="nav-link d-flex align-items-center {{ request()->routeIs('admin.arsip.*') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill me-3"></i>
                    <span class="fw-medium">Buat Pengajuan</span>
                </a>
            </li>

            <li class="nav-header text-xs fw-bold text-secondary text-uppercase mt-4 mb-1 ps-3">Akun & Sesi</li>

            {{-- PROFILE --}}
            <li class="nav-item">
                <a href="{{ route('admin.profile') }}" 
                   class="nav-link d-flex align-items-center {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle me-3"></i>
                    <span class="fw-medium">Profil Saya</span>
                </a>
            </li>

            {{-- LOGOUT --}}
            <li class="nav-item mt-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link w-100 d-flex align-items-center text-danger border-0 bg-transparent hover-bg-danger-dark">
                        <i class="bi bi-box-arrow-right me-3"></i>
                        <span class="fw-medium">Keluar / Logout</span>
                    </button>
                </form>
            </li>

        </ul>
    </div>

    {{-- FOOTER --}}
    <div class="sidebar-footer p-3 border-top border-secondary border-opacity-25 bg-dark bg-opacity-25 text-center">
        <small class="text-secondary text-xs">© 2026 IT Submission V1.0 </small>
    </div>

</aside>
