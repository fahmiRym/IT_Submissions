<aside class="sidebar sidebar-dark d-flex flex-column h-100 shadow-lg">
    
    {{-- HEADER --}}
    <div class="sidebar-header p-4 d-flex align-items-center">
        <div class="bg-white rounded p-1 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
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



        </ul>
    </div>

    {{-- FOOTER --}}
    <div class="sidebar-footer p-3 border-top border-secondary border-opacity-25 bg-dark bg-opacity-25 text-center">
        <small class="text-secondary text-xs">© 2026 IT Submission V1.0 </small>
    </div>

</aside>
