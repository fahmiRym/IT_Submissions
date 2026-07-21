{{-- Hero strip dashboard: greeting + KPI singkat + quick actions.
     Param: $role (admin|superadmin), $arsipQuery (Eloquent builder dasar utk hitung KPI). --}}
@php
    $role = $role ?? (auth()->user()->role ?? 'admin');
    $hour = (int) now()->format('H');
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $userName = auth()->user()->name ?? 'User';

    // KPI calculation (hindari error bila $arsipQuery tidak passed)
    $now = now();
    try {
        $base = ($arsipQuery ?? \App\Models\Arsip::query())->clone();
        $countToday = (clone $base)->whereDate('created_at', $now->toDateString())->count();
        $countWeek  = (clone $base)->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count();
        $countMonth = (clone $base)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $pendingApprovalCount = $pendingApprovalCount ?? 0;
    } catch (\Throwable $e) {
        $countToday = $countWeek = $countMonth = 0;
        $pendingApprovalCount = 0;
    }

    $isAdmin = in_array($role, ['admin', 'accounting']);
@endphp

<div class="dash-hero mb-4 p-4 rounded-4 shadow-sm position-relative overflow-hidden">
    <div class="dash-hero-deco-1"></div>
    <div class="dash-hero-deco-2"></div>
    <div class="position-relative" style="z-index:2;">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-lg-6">
                <div class="d-flex align-items-center gap-3">
                    <div class="dash-hero-avatar d-flex align-items-center justify-content-center">
                        @if(auth()->user()->photo)
                            <img src="{{ asset('profile_photos/' . auth()->user()->photo) }}" alt="me"
                                 style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                        @else
                            {{ strtoupper(substr($userName, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div class="text-white-50 fw-semibold" style="font-size:0.8rem; letter-spacing:0.2px;">
                            <i class="bi bi-sun-fill me-1"></i>{{ $greeting }},
                        </div>
                        <h3 class="text-white fw-extrabold mb-0" style="letter-spacing:-0.5px;">
                            {{ $userName }}
                        </h3>
                        <div class="text-white-50 mt-1" style="font-size:0.75rem;">
                            <i class="bi bi-calendar3 me-1"></i>{{ $now->translatedFormat('l, d F Y') }}
                            · <i class="bi bi-clock me-1"></i><span id="heroLiveClock">{{ $now->format('H:i') }}</span> WIB
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="row g-2">
                    <div class="col-4">
                        <div class="dash-hero-kpi">
                            <div class="dash-hero-kpi-label">HARI INI</div>
                            <div class="dash-hero-kpi-value">{{ number_format($countToday) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="dash-hero-kpi">
                            <div class="dash-hero-kpi-label">MINGGU INI</div>
                            <div class="dash-hero-kpi-value">{{ number_format($countWeek) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="dash-hero-kpi">
                            <div class="dash-hero-kpi-label">BULAN INI</div>
                            <div class="dash-hero-kpi-value">{{ number_format($countMonth) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Action strip --}}
        <div class="d-flex flex-wrap gap-2 mt-3">
            @if($isAdmin)
                <a href="{{ route('admin.arsip.index') }}" class="dash-quick-action">
                    <i class="bi bi-plus-circle-fill"></i> Buat Pengajuan Baru
                </a>
                <a href="{{ route('admin.approvals.index') }}" class="dash-quick-action position-relative">
                    <i class="bi bi-check2-square"></i> Persetujuan Saya
                    @if(($pendingApprovalCount ?? 0) > 0)
                        <span class="badge bg-danger ms-1">{{ $pendingApprovalCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.arsip.index', ['jenis' => 'Adjust']) }}" class="dash-quick-action">
                    <i class="bi bi-sliders2-vertical"></i> Adjustment
                </a>
                <a href="{{ route('admin.profile') }}" class="dash-quick-action">
                    <i class="bi bi-person-badge"></i> Profil
                </a>
            @else
                <a href="{{ route('superadmin.arsip.index') }}" class="dash-quick-action">
                    <i class="bi bi-clipboard2-data"></i> Semua Pengajuan
                </a>
                <a href="{{ route('superadmin.arsip.index', ['jenis' => 'Adjust']) }}" class="dash-quick-action">
                    <i class="bi bi-sliders2-vertical"></i> Adjustment
                </a>
                @if(Route::has('superadmin.approvals.index'))
                    <a href="{{ route('superadmin.approvals.index') }}" class="dash-quick-action position-relative">
                        <i class="bi bi-check2-square"></i> Persetujuan Final (IT)
                        @if(($pendingApprovalCount ?? 0) > 0)
                            <span class="badge bg-danger ms-1">{{ $pendingApprovalCount }}</span>
                        @endif
                    </a>
                @endif
                @if(Route::has('superadmin.laporan.index'))
                    <a href="{{ route('superadmin.laporan.index') }}" class="dash-quick-action">
                        <i class="bi bi-graph-up"></i> Laporan
                    </a>
                @endif
                @if(Route::has('superadmin.users.index'))
                    <a href="{{ route('superadmin.users.index') }}" class="dash-quick-action">
                        <i class="bi bi-people"></i> Users
                    </a>
                @endif
            @endif
        </div>
    </div>
</div>

@once
@push('styles')
<style>
    .dash-hero {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 45%, #6366f1 100%);
        color: #fff;
        border-radius: 20px;
    }
    .dash-hero-deco-1 {
        position: absolute; top: -40%; right: -8%;
        width: 320px; height: 320px;
        background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
        pointer-events: none;
    }
    .dash-hero-deco-2 {
        position: absolute; bottom: -30%; left: -5%;
        width: 280px; height: 280px;
        background: radial-gradient(circle, rgba(255,255,255,0.10) 0%, transparent 70%);
        pointer-events: none;
    }
    .dash-hero-avatar {
        width: 64px; height: 64px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border: 2px solid rgba(255, 255, 255, 0.35);
        color: #fff;
        font-weight: 800;
        font-size: 1.8rem;
        flex-shrink: 0;
    }
    .dash-hero-kpi {
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 14px;
        padding: 0.65rem 0.5rem;
        text-align: center;
    }
    .dash-hero-kpi-label {
        font-size: 0.55rem;
        letter-spacing: 0.15em;
        font-weight: 800;
        color: rgba(255,255,255,0.7);
        margin-bottom: 0.1rem;
    }
    .dash-hero-kpi-value {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        line-height: 1;
        color: #fff;
    }
    .dash-quick-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.45rem 0.85rem;
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 999px;
        color: #fff !important;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .dash-quick-action:hover {
        background: #fff;
        color: #4f46e5 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .dash-quick-action .badge {
        font-size: 0.55rem !important;
        padding: 0.2em 0.45em !important;
    }
    @media (max-width: 575.98px) {
        .dash-hero { padding: 1.25rem !important; }
        .dash-hero-avatar { width: 50px; height: 50px; font-size: 1.35rem; }
        .dash-hero h3 { font-size: 1.1rem; }
        .dash-hero-kpi-value { font-size: 1.1rem; }
        .dash-hero-kpi-label { font-size: 0.5rem; }
        .dash-quick-action { font-size: 0.7rem; padding: 0.35rem 0.65rem; }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        function tick() {
            const el = document.getElementById('heroLiveClock');
            if (!el) return;
            const d = new Date();
            const hh = String(d.getHours()).padStart(2, '0');
            const mm = String(d.getMinutes()).padStart(2, '0');
            el.textContent = `${hh}:${mm}`;
        }
        setInterval(tick, 30000);
        document.addEventListener('DOMContentLoaded', tick);
    })();
</script>
@endpush
@endonce
