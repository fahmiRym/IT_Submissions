@extends('layouts.app')

@section('title', 'Maintenance Mode | IT Submission')
@section('page-title', 'Maintenance Mode')

@push('styles')
<style>
    .maintenance-hero {
        border-radius: 20px;
        padding: 2rem;
        color: white;
    }
    .maintenance-hero.status-down {
        background: linear-gradient(135deg, #dc2626, #991b1b);
    }
    .maintenance-hero.status-up {
        background: linear-gradient(135deg, #16a34a, #15803d);
    }
    .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    .status-dot.pulse {
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .info-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        background: #f8fafc;
    }
    .btn-toggle {
        min-height: 54px;
        font-size: 1rem;
        font-weight: 700;
        border-radius: 12px;
    }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm"
         style="width:56px; height:56px; background:linear-gradient(135deg,#7c3aed,#6d28d9); color:white; font-size:1.5rem;">
        <i class="bi bi-tools"></i>
    </div>
    <div>
        <h5 class="fw-bold mb-0 text-dark">Maintenance Mode</h5>
        <p class="text-muted small mb-0">Trigger halaman maintenance dari sini tanpa perlu SSH ke server</p>
    </div>
    <a href="{{ route('superadmin.dashboard') }}" class="btn btn-light rounded-pill ms-auto px-4 fw-bold shadow-sm">
        <i class="bi bi-arrow-left me-2"></i>Kembali
    </a>
</div>

{{-- Alert sukses --}}
@if(session('success'))
<div class="alert border-0 rounded-4 shadow-sm d-flex align-items-center gap-3 mb-4"
     style="background:#f0fdf4; border-left: 4px solid #22c55e !important;">
    <i class="bi bi-check-circle-fill text-success fs-4"></i>
    <div class="flex-grow-1">
        <div class="fw-bold text-success">Berhasil!</div>
        <div class="small text-muted">{{ session('success') }}</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Bypass URL kalau baru saja enable --}}
@if(session('bypass_url'))
<div class="alert border-0 rounded-4 shadow-sm mb-4"
     style="background:#fff7ed; border-left: 4px solid #f59e0b !important;">
    <div class="d-flex gap-3">
        <i class="bi bi-key-fill text-warning fs-4 flex-shrink-0"></i>
        <div class="flex-grow-1">
            <div class="fw-bold text-dark mb-1">🔑 BYPASS URL (khusus untukmu)</div>
            <div class="small text-muted mb-2">
                Buka URL ini di browser (di tab baru / incognito) untuk BYPASS maintenance mode.
                URL ini akan set cookie yg membolehkanmu akses aplikasi normal walaupun mode down aktif.
            </div>
            <div class="d-flex gap-2 align-items-center">
                <code class="p-2 rounded flex-grow-1"
                      style="background:white; border:1px dashed #cbd5e1; font-size:0.85rem; word-break:break-all;">{{ session('bypass_url') }}</code>
                <button class="btn btn-warning fw-bold" onclick="navigator.clipboard.writeText('{{ session('bypass_url') }}').then(()=>{this.innerHTML='<i class=bi bi-check-lg></i> Tersalin'; setTimeout(()=>{this.innerHTML='<i class=bi bi-clipboard></i> Copy';},2000);})">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
            </div>
            <div class="small text-muted mt-2">
                <b>Simpan URL ini!</b> Setelah tab ini di-refresh, URL tidak akan tampil lagi.
            </div>
        </div>
    </div>
</div>
@endif

{{-- Status Hero --}}
<div class="maintenance-hero shadow-sm mb-4 {{ $isDown ? 'status-down' : 'status-up' }}">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <div class="d-flex align-items-center mb-2">
                <span class="status-dot pulse" style="background:white;"></span>
                <small class="fw-bold text-uppercase" style="letter-spacing: 2px; font-size: 0.7rem; opacity: 0.9;">
                    {{ $isDown ? 'MAINTENANCE MODE AKTIF' : 'APLIKASI NORMAL' }}
                </small>
            </div>
            <h2 class="fw-bold mb-1">
                @if($isDown)
                    <i class="bi bi-tools me-2"></i>Sedang Maintenance
                @else
                    <i class="bi bi-check-circle-fill me-2"></i>Aplikasi Live
                @endif
            </h2>
            <p class="mb-0" style="opacity: 0.9;">
                @if($isDown)
                    Semua request non-superadmin dialihkan ke halaman maintenance.
                @else
                    Semua user bisa akses aplikasi normal.
                @endif
            </p>
        </div>
        <div style="font-size: 5rem; opacity: 0.15;">
            @if($isDown)
                <i class="bi bi-cone-striped"></i>
            @else
                <i class="bi bi-shield-check"></i>
            @endif
        </div>
    </div>
</div>

{{-- Info + Actions grid --}}
<div class="row g-4">

    {{-- LEFT: Info panel --}}
    <div class="col-lg-6">
        <div class="info-card h-100">
            <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-info-circle-fill text-primary me-2"></i>Apa yang terjadi saat Maintenance ON?
            </h6>
            <ul class="small text-muted ps-3 mb-3">
                <li class="mb-1">Semua request Laravel di-blok dgn HTTP <b>503 Service Unavailable</b></li>
                <li class="mb-1">Nginx catch 503 → serve <code>public/maintenance.html</code> (dark theme cantik)</li>
                <li class="mb-1">User lihat halaman: <i>"Sedang Dalam Perbaikan – IT Submissions Inkalum"</i> dgn auto-heartbeat retry setiap 15 detik</li>
                <li class="mb-1">Kamu (superadmin) masih bisa akses aplikasi via <b>Bypass URL</b> yg dikeluarkan saat enable</li>
                <li>Perfect untuk: deploy, migrasi DB, restart layanan, atau maintenance planned</li>
            </ul>
            <div class="alert alert-info mb-0 py-2 small">
                <i class="bi bi-lightbulb-fill me-1"></i>
                <b>Tips</b>: kalau kamu deploy code baru, aktifkan maintenance mode dulu → deploy → nonaktifkan. Zero downtime perception.
            </div>
        </div>
    </div>

    {{-- RIGHT: Action panel --}}
    <div class="col-lg-6">
        <div class="info-card h-100">
            <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Aksi
            </h6>

            @if(!$isDown)
                {{-- ENABLE FORM --}}
                <form action="{{ route('superadmin.maintenance.enable') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Auto-refresh browser (detik)</label>
                        <input type="number" name="refresh" class="form-control" value="15" min="0" max="3600"
                               placeholder="Interval retry auto (default: 15)">
                        <small class="text-muted">Berapa detik browser user auto-cek apakah aplikasi sudah UP kembali.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">HTTP Retry-After header (detik)</label>
                        <input type="number" name="retry" class="form-control" value="60" min="0" max="86400"
                               placeholder="Header retry (default: 60)">
                        <small class="text-muted">Hint untuk browser/proxy kapan retry (SEO safe).</small>
                    </div>

                    <button type="submit" class="btn btn-danger btn-toggle w-100 shadow-sm"
                            onclick="return confirm('Yakin aktifkan maintenance mode? Semua user (kecuali kamu via bypass URL) tidak bisa akses aplikasi.');">
                        <i class="bi bi-power me-2"></i>Aktifkan Maintenance Mode
                    </button>
                </form>
            @else
                {{-- DISABLE FORM --}}
                <div class="alert alert-warning mb-3 small">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Maintenance mode saat ini <b>AKTIF</b>. User lain lihat halaman maintenance.
                </div>

                <form action="{{ route('superadmin.maintenance.disable') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-toggle w-100 shadow-sm">
                        <i class="bi bi-power me-2"></i>Nonaktifkan Maintenance Mode
                    </button>
                </form>

                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted d-block">
                        <b>Status file:</b><br>
                        <code class="text-xs">{{ $downFile }}</code>
                    </small>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- CLI equivalents info --}}
<div class="info-card mt-4">
    <h6 class="fw-bold text-dark mb-3">
        <i class="bi bi-terminal-fill text-secondary me-2"></i>Equivalen SSH (untuk referensi)
    </h6>
    <div class="row g-3 small">
        <div class="col-md-6">
            <div class="fw-bold text-danger mb-1">Aktifkan dari SSH:</div>
            <code class="d-block p-2 rounded bg-dark text-white" style="font-size:0.8rem;">docker exec it_app php artisan down</code>
        </div>
        <div class="col-md-6">
            <div class="fw-bold text-success mb-1">Nonaktifkan dari SSH:</div>
            <code class="d-block p-2 rounded bg-dark text-white" style="font-size:0.8rem;">docker exec it_app php artisan up</code>
        </div>
    </div>
</div>

@endsection
