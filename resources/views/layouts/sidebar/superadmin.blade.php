<aside class="sidebar bg-white shadow-sm d-flex flex-column h-100">

    {{-- ================= HEADER ================= --}}
    <div class="sidebar-header p-4 d-flex align-items-center">
        <div class="bg-primary text-white rounded p-2 me-3 d-flex align-items-center justify-content-center shadow-sm"
             style="width:40px;height:40px">
            <i class="bi bi-archive-fill fs-5"></i>
        </div>
        <div>
            <h6 class="mb-0 fw-bold text-dark">IT - Submission</h6>
            <small class="text-slate-500 text-xs">Superadmin Panel</small>
        </div>
    </div>

    {{-- ================= MENU ================= --}}
    <div class="sidebar-menu flex-grow-1 overflow-auto p-3 custom-scrollbar">
        <ul class="nav flex-column gap-1">

            {{-- NOTIFICATION DROPDOWN --}}
            <li class="nav-item dropdown mb-3">
                <a class="nav-link d-flex align-items-center justify-content-between px-3 py-2 rounded-3 text-dark bg-light border border-secondary border-opacity-10" 
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

                <div class="dropdown-menu shadow-lg border-0 mt-2 p-0 w-100 overflow-hidden" style="z-index: 1050;">
                    <div class="p-3 border-bottom bg-primary text-white fw-bold">
                        🔔 Info Terbaru
                    </div>
                    <div style="max-height: 250px; overflow-y: auto;">
                        @forelse(($notifications ?? []) as $n)
                            <div class="p-3 border-bottom small hover-bg-light">
                                {{ $n->message }}
                            </div>
                        @empty
                            <div class="p-3 text-center text-muted small">
                                Tidak ada notifikasi baru
                            </div>
                        @endforelse
                    </div>
                </div>
            </li>

            {{-- DASHBOARD --}}
            <li class="nav-item">
                <a href="{{ route('superadmin.dashboard') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill me-3"></i>
                    <span class="fw-medium">Dashboard</span>
                </a>
            </li>

            <li class="nav-header text-xs fw-bold text-slate-500 text-uppercase mt-4 mb-2 ps-3">
                Operasional
            </li>

            {{-- ================= ARSIP PENGAJUAN ================= --}}
            @php
                $isArsip = request()->is('superadmin/arsip*');
            @endphp

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between"
                   data-bs-toggle="collapse"
                   href="#arsipMenu"
                   aria-expanded="{{ $isArsip ? 'true' : 'false' }}">

                    <div class="d-flex align-items-center">
                        <i class="bi bi-folder2-open me-3 text-warning"></i>
                        <span class="fw-medium">Data Pengajuan</span>
                    </div>
                    <i class="bi bi-chevron-down transition-icon {{ $isArsip ? 'rotate-180' : '' }}"></i>
                </a>

                <div class="collapse {{ $isArsip ? 'show' : '' }}" id="arsipMenu">
                    <ul class="nav flex-column ms-3 mt-1 ps-3 border-start border-2">

                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index',['jenis'=>'Cancel']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Cancel' ? 'text-primary fw-bold' : '' }}">
                                ❌ Cancel
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index',['jenis'=>'Adjust']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Adjust' ? 'text-primary fw-bold' : '' }}">
                                🔄 Adjustment
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index',['jenis'=>'Mutasi_Billet']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Mutasi_Billet' ? 'text-primary fw-bold' : '' }}">
                                🔀 Mutasi Billet
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index',['jenis'=>'Mutasi_Produk']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Mutasi_Produk' ? 'text-primary fw-bold' : '' }}">
                                📦 Mutasi Produk
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index',['jenis'=>'Internal_Memo']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Internal_Memo' ? 'text-primary fw-bold' : '' }}">
                                📝 Internal Memo
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('superadmin.arsip.index',['jenis'=>'Bundel']) }}"
                               class="nav-link py-2 text-sm {{ request('jenis')=='Bundel' ? 'text-primary fw-bold' : '' }}">
                                📚 Bundel
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
                $isMaster = request()->is('superadmin/departments*', 'superadmin/units*', 'superadmin/managers*', 'superadmin/users*');
            @endphp

            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between"
                   data-bs-toggle="collapse"
                   href="#masterMenu"
                   aria-expanded="{{ $isMaster ? 'true' : 'false' }}">

                    <div class="d-flex align-items-center">
                        <i class="bi bi-database-fill me-3 text-info"></i>
                        <span class="fw-medium">Master Data</span>
                    </div>
                    <i class="bi bi-chevron-down transition-icon {{ $isMaster ? 'rotate-180' : '' }}"></i>
                </a>

                <div class="collapse {{ $isMaster ? 'show' : '' }}" id="masterMenu">
                    <ul class="nav flex-column ms-3 mt-1 ps-3 border-start border-2">
                        <li><a href="{{ route('superadmin.departments.index') }}" class="nav-link py-2 text-sm">🏢 Departemen</a></li>
                        <li><a href="{{ route('superadmin.units.index') }}" class="nav-link py-2 text-sm">📦 Unit</a></li>
                        <li><a href="{{ route('superadmin.managers.index') }}" class="nav-link py-2 text-sm">🧑‍💼 Manager</a></li>
                        <li><a href="{{ route('superadmin.users.index') }}" class="nav-link py-2 text-sm">👥 User</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-header text-xs fw-bold text-slate-500 text-uppercase mt-4 mb-2 ps-3">
                Akun & Sistem
            </li>

            {{-- PROFILE --}}
            <li class="nav-item">
                <a href="{{ route('superadmin.profile') }}"
                   class="nav-link d-flex align-items-center {{ request()->routeIs('superadmin.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle me-3"></i>
                    <span class="fw-medium">Profile Saya</span>
                </a>
            </li>

            {{-- LOGOUT --}}
            <li class="nav-item mt-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="nav-link w-100 d-flex align-items-center text-danger border-0 bg-transparent hover-bg-danger-light">
                        <i class="bi bi-box-arrow-right me-3"></i>
                        <span class="fw-medium">Logout</span>
                    </button>
                </form>
            </li>

        </ul>
    </div>

    {{-- FOOTER --}}
    <div class="sidebar-footer p-3 border-top bg-light text-center">
        <small class="text-slate-500 text-xs">© 2026 IT Submission V1.0 </small>
    </div>
</aside>
