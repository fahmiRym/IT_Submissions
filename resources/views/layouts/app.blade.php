<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','IT Submission')</title>
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
        <div class="d-flex align-items-center">
            {{-- Mobile Toggle --}}
            <button class="btn btn-light d-md-none me-2" onclick="document.querySelector('.sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <span class="fw-bold fs-5 text-dark">@yield('page-title')</span>
        </div>
        
        <div class="d-flex align-items-center gap-3">
             {{-- User Dropdown or Info could go here --}}
             <div class="text-end d-none d-sm-block">
                @auth
                    <small class="d-block text-muted" style="line-height:1; font-size: 0.75rem;">Halo,</small>
                    <span class="fw-semibold text-dark">{{ auth()->user()->name ?? 'User' }}</span>
                @endauth
             </div>
             @auth
             <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width: 38px; height: 38px;">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
             </div>
             @endauth
        </div>
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
