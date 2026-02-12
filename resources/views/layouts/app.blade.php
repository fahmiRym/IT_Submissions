<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', config('app.name'))</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Custom Modern Theme --}}
    <link href="{{ asset('css/modern-theme.css') }}" rel="stylesheet">

    {{-- Chart --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Essential Layout Structure only */
        
        /* FIX BOOTSTRAP MODAL SCROLL */
        .modal-dialog { max-height: 95vh; }
        .modal-content { max-height: 95vh; display: flex; flex-direction: column; }
        .modal-body { overflow-y: auto; scrollbar-width: thin; max-height: calc(95vh - 160px); }

        /* ENSURE BODY IS NOT FLEX */
        body { display: block !important; }

        /* LAYOUT */
        .content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: block; /* Removed flex */
            transition: margin-left 0.3s ease;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        main {
            padding: 2rem;
            /* Removed flex: 1 */
        }
        
        /* .table-responsive { max-height: 70vh; overflow-y: auto; } REMOVED to allow page scroll */
        .table-responsive { overflow-x: auto; }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .content { margin-left: 0; }
            .sidebar.show { transform: translateX(0); }
        }
    </style>

    @stack('styles')
</head>

<body>

{{-- ================= SIDEBAR ================= --}}
@auth
    @if(auth()->user()->role === 'superadmin')
        @include('layouts.sidebar.superadmin')
    @elseif(auth()->user()->role === 'admin')
        @include('layouts.sidebar.admin')
    @endif
@endauth


{{-- ================= CONTENT ================= --}}
<div class="content">
    <nav class="topbar d-flex justify-content-between align-items-center px-4 py-3">
        <div class="d-flex align-items-center gap-3">
            {{-- Mobile Toggle --}}
            <button class="btn btn-light d-md-none" onclick="document.querySelector('.sidebar').classList.toggle('show')" style="border-radius: 10px;">
                <i class="bi bi-list fs-5"></i>
            </button>
            <span class="fw-bold fs-5 text-dark">@yield('page-title')</span>
        </div>
        
        @auth
        <div class="d-flex align-items-center gap-3">
            {{-- Notification Bell --}}
            <div class="dropdown">
                <button class="btn btn-light position-relative rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                        style="width: 42px; height: 42px; border: none;" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false">
                    <i class="bi bi-bell-fill text-primary fs-5"></i>
                    @php
                        $unreadCount = \App\Models\Notification::where('role_target', auth()->user()->role)
                            ->where('is_read', false)
                            ->count();
                    @endphp
                    @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                          style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                    @endif
                </button>
                
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0" style="width: 320px; max-height: 400px; overflow-y: auto; border-radius: 12px;">
                    <div class="p-3 border-bottom bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-bell-fill me-2"></i>Notifikasi</h6>
                        @if($unreadCount > 0)
                        <span class="badge bg-white text-primary rounded-pill">{{ $unreadCount }}</span>
                        @endif
                    </div>
                    <div style="max-height: 300px; overflow-y: auto;">
                        @php
                            $notifications = \App\Models\Notification::where('role_target', auth()->user()->role)
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();
                        @endphp
                        @forelse($notifications as $notif)
                            <div class="p-3 border-bottom {{ !$notif->is_read ? 'bg-light' : '' }} hover-bg-light" style="cursor: pointer;">
                                <div class="d-flex gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" 
                                         style="width: 36px; height: 36px;">
                                        <i class="bi bi-info-circle-fill"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small text-dark">{{ $notif->title }}</div>
                                        <div class="small text-muted" style="font-size: 0.8rem;">{{ Str::limit($notif->message, 60) }}</div>
                                        <div class="text-muted mt-1" style="font-size: 0.7rem;">
                                            <i class="bi bi-clock me-1"></i>{{ $notif->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    @if(!$notif->is_read)
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-primary rounded-circle" style="width: 8px; height: 8px; padding: 0;"></span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                <small>Tidak ada notifikasi</small>
                            </div>
                        @endforelse
                    </div>
                    @if($notifications->count() > 0)
                    <div class="p-2 text-center border-top">
                        <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.notifications.index') : route('admin.notifications.index') }}" 
                           class="small text-primary text-decoration-none fw-bold">
                            Lihat Semua Notifikasi
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Profile Dropdown --}}
            <div class="dropdown">
                <button class="btn d-flex align-items-center gap-2 rounded-pill shadow-sm px-3 py-2" 
                        style="background: white; border: 1px solid #e2e8f0;" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false">
                    @if(auth()->user()->photo)
                        <img src="{{ asset('profile_photos/' . auth()->user()->photo) }}" 
                             alt="Profile" 
                             class="rounded-circle" 
                             style="width: 32px; height: 32px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center fw-bold" 
                             style="width: 32px; height: 32px; font-size: 0.9rem;">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                    @endif
                    <div class="text-start d-none d-sm-block">
                        <div class="fw-semibold text-dark" style="font-size: 0.9rem; line-height: 1.2;">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="text-muted" style="font-size: 0.75rem; line-height: 1;">{{ ucfirst(auth()->user()->role ?? 'user') }}</div>
                    </div>
                    <i class="bi bi-chevron-down text-muted small"></i>
                </button>
                
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2" style="min-width: 220px; border-radius: 12px;">
                    <div class="px-3 py-2 border-bottom">
                        <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                        <div class="small text-muted">{{ auth()->user()->email }}</div>
                    </div>
                    
                    <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.profile') : route('admin.profile') }}" 
                       class="dropdown-item rounded-3 mt-2 d-flex align-items-center gap-2 py-2">
                        <i class="bi bi-person-circle text-primary"></i>
                        <span>Profil Saya</span>
                    </a>
                    
                    <div class="dropdown-divider my-2"></div>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item rounded-3 d-flex align-items-center gap-2 py-2 text-danger">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endauth
    </nav>

    <main class="fade-in">
        @yield('content')
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

</body>
</html>
