@extends('layouts.app')

@section('title', 'IT Submission Data Pengajuan')
@section('page-title', 'Data Pengajuan')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
    
    body { font-family: 'Outfit', sans-serif; background-color: #f4f7fa; color: #0f172a; }
    
    /* Global Typography & Clarity - High Contrast */
    .text-secondary { color: #334155 !important; font-weight: 600; } 
    .text-muted { color: #64748b !important; }
    .small, small { font-size: 0.8rem; font-weight: 600; }
    .fw-extrabold { font-weight: 800 !important; }
    
    /* Responsive Table Utility */
    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .table-responsive::-webkit-scrollbar { height: 6px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* Table internal styling */
    .table thead th {
        font-size: 0.725rem;
        letter-spacing: 0.1em;
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        text-transform: uppercase;
        color: #475569;
        font-weight: 800;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f1f5f9;
        transition: background-color 0.2s ease;
    }

    /* Premium Stats Cards Refinements */
    .stat-card-main {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); /* Fallback */
        box-shadow: 0 20px 25px -5px rgba(239, 68, 68, 0.2);
    }
    
    /* Mesh Gradient Overlay */
    .mesh-gradient {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: 
            radial-gradient(at 0% 0%, rgba(255,255,255,0.15) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(255,255,255,0.1) 0px, transparent 50%);
        z-index: 1;
    }
    
    /* Subtle Pattern Texture */
    .pattern-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        z-index: 1;
        opacity: 0.3;
    }

    .glass-badge {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .mini-stat-card {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .mini-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        border-color: #e2e8f0;
    }

    /* Table Item Cards Premium Styling */
    .item-detail-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 6px;
        transition: border-color 0.2s;
    }
    .item-detail-card:hover { border-color: #cbd5e1; }
    
    .note-detail-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 5px solid #0ea5e9 !important;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    
    /* Optimized Typography */
    .qty-display-large { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.8px; }
    .text-primary-dark { color: #0f172a !important; }
</style>

{{-- MODAL CLEANUP STORAGE --}}
<div class="modal fade" id="modalCleanupStorage" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-danger text-white border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-trash3-fill fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Manajemen Penyimpanan</h5>
                        <small class="text-white-50">Hapus file scan untuk menghemat ruang</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('superadmin.arsip.cleanup-storage') }}" method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN? File scan yang dihapus tidak dapat dikembalikan, namun data transaksi tetap ada di sistem.')">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4">
                        <div class="d-flex gap-3">
                            <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Informasi Penting</h6>
                                <p class="small mb-0 opacity-75">Fitur ini akan menghapus **File Fisik (PDF/Gambar)** dari server untuk menghemat ruang. Data detail pengajuan (No Dokumen, No Transaksi, Item) **TIDAK AKAN DIHAPUS**.</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Mulai Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control border-0 bg-light" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control border-0 bg-light" required>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 rounded-3 bg-light border border-dashed border-secondary border-opacity-25">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="bi bi-info-circle"></i>
                            <span class="text-xs fw-bold">Tips: Gunakan range tanggal yang sudah sangat lama (misal: 1-2 tahun yang lalu).</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-trash3 me-2"></i>Bersihkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endpush

@section('content')

{{-- ================= FILTER SECTION ================= --}}
<div class="card border-0 shadow-sm mb-4 animate-on-scroll" style="border-radius: 12px;">
    <div class="card-body p-4">
        <form method="GET" class="row g-3">
            {{-- Baris 1 --}}
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">🔍 Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-0 px-3" placeholder="User, No Dok, Transaksi..." style="border-radius: 0 8px 8px 0;">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">📅 Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control bg-light border-0 px-3" style="border-radius: 8px;">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">📅 Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control bg-light border-0 px-3" style="border-radius: 8px;">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">📄 Jenis Pengajuan</label>
                <select name="jenis_pengajuan" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                    <option value="">Semua</option>
                    @foreach(['Cancel','Adjust','Mutasi_Billet','Mutasi_Produk','Internal_Memo','Bundel'] as $jp)
                        <option value="{{ $jp }}" {{ request('jenis_pengajuan')==$jp?'selected':'' }}>{{ str_replace('_', ' ', $jp) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                 <label class="form-label small fw-bold text-secondary mb-1">⚙️ Status Proses</label>
                 <select name="ket_process" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                     <option value="">Semua</option>
                     @foreach(['Review','Process','Done','Pending','Void'] as $st)
                         <option value="{{ $st }}" {{ request('ket_process')==$st?'selected':'' }}>{{ $st }}</option>
                     @endforeach
                 </select>
            </div>

            {{-- Baris 2 --}}
            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">🏢 Departemen</label>
                <select name="department_id" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                    <option value="">-- Semua --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

             <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">👤 Pengaju</label>
                <select name="admin_id" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                    <option value="">-- Semua --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (request('admin_id') ?? request('user_id')) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">⚠️ Kategori</label>
                <select name="kategori" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                    <option value="">Semua</option>
                    <option value="Human" {{ request('kategori')=='Human'?'selected':'' }}>Human Error</option>
                    <option value="System" {{ request('kategori')=='System'?'selected':'' }}>System Error</option>
                    <option value="None" {{ request('kategori')=='None'?'selected':'' }}>None/Adjust</option>
                </select>
            </div>

            <div class="col-md-5 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary fw-bold shadow-sm flex-fill" style="background: #4f46e5; border-color: #4f46e5; border-radius: 8px; height: 38px;">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <a href="{{ route('superadmin.arsip.index') }}" class="btn btn-light border" style="border-radius: 8px; width: 45px; height: 38px;" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ================= STATS OVERVIEW ================= --}}
@php
    $fJenis = request('jenis') ?? request('jenis_pengajuan');
    
    // Config for Card 1 (Total)
    $sConfig = [
        'title' => 'TOTAL ADJUSTMENT', // Disesuaikan dng screenshot "TOTAL ADJUSTMENT"
        'icon'  => 'bi-sliders',
        'color' => '#0ea5e9',
        'bg'    => 'linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%)',
    ];

    if($fJenis == 'Cancel') {
        $sConfig = [ 'title' => 'TOTAL CANCEL', 'icon' => 'bi-x-octagon-fill', 'color' => '#ef4444', 'bg' => 'linear-gradient(135deg, #f87171 0%, #ef4444 100%)' ];
    } elseif($fJenis == 'Mutasi_Billet') {
        $sConfig = [ 'title' => 'TOTAL MUTASI BILLET', 'icon' => 'bi-arrow-left-right', 'color' => '#6366f1', 'bg' => 'linear-gradient(135deg, #818cf8 0%, #6366f1 100%)' ];
    } elseif($fJenis == 'Mutasi_Produk') {
        $sConfig = [ 'title' => 'TOTAL MUTASI PRODUK', 'icon' => 'bi-box-seam-fill', 'color' => '#10b981', 'bg' => 'linear-gradient(135deg, #34d399 0%, #10b981 100%)' ];
    } elseif($fJenis == 'Internal_Memo') {
        $sConfig = [ 'title' => 'TOTAL MEMO', 'icon' => 'bi-file-text-fill', 'color' => '#f59e0b', 'bg' => 'linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%)' ];
    } elseif($fJenis == 'Bundel') {
        $sConfig = [ 'title' => 'TOTAL BUNDEL', 'icon' => 'bi-collection-fill', 'color' => '#8b5cf6', 'bg' => 'linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%)' ];
    } elseif(!$fJenis) {
        $sConfig = [ 'title' => 'TOTAL ALL DATA', 'icon' => 'bi-grid-fill', 'color' => '#3b82f6', 'bg' => 'linear-gradient(135deg, #60a5fa 0%, #2563eb 100%)' ];
    }
@endphp

<div class="row g-3 mb-4 animate-on-scroll">
    {{-- Main Stat Card - Premium Refined --}}
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card border-0 stat-card-main text-white h-100 p-1" style="background: {{ $sConfig['bg'] }}; min-height: 160px;">
            <div class="mesh-gradient"></div>
            <div class="pattern-overlay"></div>
            
            <div class="card-body d-flex flex-column justify-content-between p-4 position-relative" style="z-index: 2;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 56px; height: 56px; backdrop-filter: blur(4px);">
                        <i class="bi {{ $sConfig['icon'] }} fs-3 text-white"></i>
                    </div>
                    <div class="glass-badge">ACTIVE</div>
                </div>
                <div>
                    <h1 class="fw-extrabold mb-0 lh-1 stat-card-text-large" style="font-size: 2.8rem; letter-spacing: -2px;">{{ number_format($stats['total'] ?? 0) }}</h1>
                    <p class="mb-0 small fw-extrabold opacity-75 mt-2 letter-spacing-1 text-uppercase text-white" style="font-size: 0.72rem;">{{ $sConfig['title'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tiny Stat Cards matching Mockup --}}
    @php
        $mainStats = [
            ['label' => 'REVIEW', 'key' => 'Review', 'icon' => 'bi-clock-history', 'color' => '#3b82f6', 'param' => 'ket_process'],
            ['label' => 'ON PROCESS', 'key' => 'Process', 'icon' => 'bi-hourglass-split', 'color' => '#f59e0b', 'param' => 'ket_process'],
            ['label' => 'PENDING', 'key' => 'Pending', 'icon' => 'bi-pause-circle-fill', 'color' => '#94a3b8', 'param' => 'ket_process'],
            ['label' => 'DONE', 'key' => 'Done', 'icon' => 'bi-check-circle-fill', 'color' => '#10b981', 'param' => 'ket_process'],
            ['label' => 'VOID / REJECT', 'key' => 'Void', 'icon' => 'bi-slash-circle-fill', 'color' => '#ef4444', 'param' => 'ket_process'],
        ];

        $docStats = [
            ['label' => 'BA PENDING', 'key' => 'ba_pending', 'icon' => 'bi-file-earmark-lock-fill', 'color' => '#94a3b8', 'param' => 'ba', 'val' => 'Pending'],
            ['label' => 'BA PROCESS', 'key' => 'ba_process', 'icon' => 'bi-file-earmark-play-fill', 'color' => '#0ea5e9', 'param' => 'ba', 'val' => 'Process'],
            ['label' => 'BA SELESAI', 'key' => 'ba_done', 'icon' => 'bi-file-earmark-check-fill', 'color' => '#0d9488', 'param' => 'ba', 'val' => 'Done'],
            ['label' => 'ARSIP PENDING', 'key' => 'arsip_pending', 'icon' => 'bi-folder-x', 'color' => '#cbd5e1', 'param' => 'arsip', 'val' => 'Pending'],
            ['label' => 'ARSIP SELESAI', 'key' => 'arsip_done', 'icon' => 'bi-folder-check', 'color' => '#84cc16', 'param' => 'arsip', 'val' => 'Done'],
        ];
    @endphp

    <div class="col-xl-9">
        <div class="d-flex flex-column gap-3 h-100">
            {{-- ROW 1: STATUS UTAMA --}}
            <div class="row g-2 flex-fill">
                @foreach($mainStats as $ts)
                <div class="col">
                    <a href="{{ request()->fullUrlWithQuery([$ts['param'] => $ts['key']]) }}" class="text-decoration-none h-100 d-block">
                        <div class="card mini-stat-card border-0 shadow-sm h-100 py-1" style="background: {{ $ts['color'] }}08; transition: all 0.3s ease;">
                            <div class="card-body p-3 d-flex flex-column justify-content-center gap-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-extrabold text-primary-dark mb-0" style="letter-spacing: -1px;">{{ number_format($stats[$ts['key']] ?? 0) }}</h5>
                                    <div class="bg-white rounded-circle shadow-xs d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                        <i class="bi {{ $ts['icon'] }} small" style="color: {{ $ts['color'] }}; font-size: 0.7rem;"></i>
                                    </div>
                                </div>
                                <div class="text-secondary fw-extrabold text-uppercase mt-1" style="font-size: 0.55rem; letter-spacing: 0.5px; opacity: 0.8;">{{ $ts['label'] }}</div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>

            {{-- ROW 2: STATUS DOKUMEN --}}
            <div class="row g-2 flex-fill">
                @foreach($docStats as $ts)
                <div class="col">
                    <a href="{{ request()->fullUrlWithQuery([$ts['param'] => $ts['val']]) }}" class="text-decoration-none h-100 d-block">
                        <div class="card mini-stat-card border-0 shadow-sm h-100 py-1" style="background: {{ $ts['color'] }}08; transition: all 0.3s ease; border-left: 3px solid {{ $ts['color'] }} !important;">
                            <div class="card-body p-3 d-flex flex-column justify-content-center gap-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-extrabold text-primary-dark mb-0" style="letter-spacing: -1px;">{{ number_format($stats[$ts['key']] ?? 0) }}</h5>
                                    <div class="bg-white rounded-circle shadow-xs d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                        <i class="bi {{ $ts['icon'] }} small" style="color: {{ $ts['color'] }}; font-size: 0.7rem;"></i>
                                    </div>
                                </div>
                                <div class="text-secondary fw-extrabold text-uppercase mt-1" style="font-size: 0.55rem; letter-spacing: 0.5px; opacity: 0.8;">{{ $ts['label'] }}</div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .letter-spacing-1 { letter-spacing: 1px; }
    .mini-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;
        background-color: #fff !important;
    }
</style>

<style>
    .transform-scale { transition: transform 0.2s; }
    .transform-scale:hover { transform: translateY(-3px); }
    .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: none; }
</style>

{{-- ================= HEADER & ACTIONS ================= --}}
{{-- ================= HEADER & ACTIONS ================= --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 animate-on-scroll" style="animation-delay: 0.2s;">
    <div class="mb-2 mb-md-0">
        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-table me-2 text-primary"></i>Data Arsip</h5>
        <div class="text-muted small">
            Showing <span class="fw-bold">{{ $arsips->firstItem() ?? 0 }}</span> - <span class="fw-bold">{{ $arsips->lastItem() ?? 0 }}</span> of <span class="fw-bold">{{ $arsips->total() }}</span> data
        </div>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        {{-- Limit Dropdown --}}
        <div class="d-flex align-items-center bg-white rounded-pill px-3 py-1 shadow-sm border">
            <small class="text-secondary fw-bold me-2" style="font-size: 0.75rem;">SHOW:</small>
            <select id="perPageSelect" class="form-select form-select-sm border-0 bg-transparent fw-bold text-primary py-0 ps-0 pe-4" style="width: auto; cursor: pointer; box-shadow: none;">
                @foreach([10, 25, 50, 100, 250, 500, 1000] as $size)
                    <option value="{{ $size }}" {{ request('per_page') == $size ? 'selected' : '' }}>{{ $size }} Rows</option>
                @endforeach
            </select>
        </div>

        {{-- Management Storage --}}
        <button class="btn btn-outline-danger rounded-pill shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCleanupStorage">
            <i class="bi bi-hdd-network me-2"></i>Unit Pembersihan
        </button>

        {{-- Create Button --}}
        <button class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahArsip">
            <i class="bi bi-plus-lg me-2"></i>Buat Baru
        </button>
    </div>
</div>

{{-- ================= DATA TABLE ================= --}}
<div class="card border-0 shadow-sm animate-on-scroll" style="border-radius: 12px; animation-delay: 0.3s; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                    <tr style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                         <th class="ps-4 text-center" width="40">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'dir' => request('sort') == 'id' && request('dir') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-secondary">
                                #
                            </a>
                        </th>
                        <th width="100" class="text-nowrap ps-4">
                             <a href="{{ request()->fullUrlWithQuery(['sort' => 'tgl_pengajuan', 'dir' => request('sort') == 'tgl_pengajuan' && request('dir') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-secondary">
                                TGL PENGAJUAN
                            </a>
                        </th>
                        <th width="100" class="text-nowrap">
                             <a href="{{ request()->fullUrlWithQuery(['sort' => 'tgl_arsip', 'dir' => request('sort') == 'tgl_arsip' && request('dir') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-secondary">
                                TGL ARSIP
                            </a>
                        </th>
                        <th width="180" class="text-nowrap">
                             <a href="{{ request()->fullUrlWithQuery(['sort' => 'no_registrasi', 'dir' => request('sort') == 'no_registrasi' && request('dir') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-secondary">
                                NO REGISTRASI
                            </a>
                        </th>
                        <th width="80">JENIS</th>
                        <th width="130" class="text-nowrap">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'admin_id', 'dir' => request('sort') == 'admin_id' && request('dir') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-secondary">
                                PENGAJU
                            </a>
                        </th>
                        <th width="150" class="text-nowrap">
                             <a href="{{ request()->fullUrlWithQuery(['sort' => 'department_id', 'dir' => request('sort') == 'department_id' && request('dir') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-secondary">
                                DEPT & UNIT
                            </a>
                        </th>
                        <th width="150" class="text-nowrap">
                             <a href="{{ request()->fullUrlWithQuery(['sort' => 'ket_process', 'dir' => request('sort') == 'ket_process' && request('dir') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-secondary">
                                STATUS DETAIL
                            </a>
                        </th>
                        <th class="text-center" width="80"><span class="text-uppercase fw-bold">QTY</span></th>
                        <th style="min-width: 200px;">DETAIL DOKUMEN</th>
                        <th width="100" class="text-center pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arsips as $a)
                    <tr class="transition-hover" style="font-size: 0.85rem; border-bottom: 1px solid #f1f5f9;">
                        <td class="ps-4 text-center fw-bold text-muted">
                            {{ ($arsips->currentPage() - 1) * $arsips->perPage() + $loop->iteration }}
                        </td>
                        
                        <td class="text-nowrap ps-4 position-relative">
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ optional($a->tgl_pengajuan)->format('d/m/Y') }}</div>
                            <small class="text-primary fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                <i class="bi bi-clock-history me-1"></i>{{ optional($a->tgl_pengajuan)->format('H:i') }} WIB
                            </small>
                        </td>
                        <td class="text-nowrap ps-3">
                            @if($a->tgl_arsip)
                                <div class="text-dark fw-bold" style="font-size: 0.85rem;">{{ $a->tgl_arsip->format('d/m/Y') }}</div>
                                <small class="text-success fw-bold" style="font-size: 0.7rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ $a->updated_at->format('H:i') }}
                                </small>
                            @else
                                <span class="text-muted opacity-30 fw-bold fs-5">-</span>
                            @endif
                        </td>
    
                        <td class="ps-3">
                            <div class="d-flex flex-column gap-1">
                                {{-- No Registrasi styling matching Superadmin --}}
                                 @if($a->no_registrasi)
                                     <div class="d-flex align-items-center mb-1">
                                         <div class="px-3 py-1 rounded-pill fw-bold font-monospace bg-white border border-info border-opacity-50 text-info shadow-sm" 
                                              style="font-size: 0.75rem; letter-spacing: 0.5px; border-width: 2px !important;">
                                             <i class="bi bi-bookmark-fill me-2 opacity-50"></i>{{ $a->no_registrasi }}
                                         </div>
                                     </div>
                                 @endif

                                 {{-- SYSTEM GENERATED NO DOC --}}
                                 @if($a->no_doc)
                                     <div class="d-flex align-items-center mb-1 ps-2">
                                          <div class="text-primary fw-extrabold font-monospace" style="font-size: 0.82rem; border-left: 3px solid #0d6efd; padding-left: 8px;">
                                              {{ strtoupper($a->no_doc) }}
                                          </div>
                                     </div>
                                 @endif
    
                                 {{-- No Transaksi Hierarchy with Monospace & Arrows --}}
                                 @if($a->no_transaksi)
                                     <div class="d-flex flex-column gap-1 ps-2">
                                         @if(!empty($a->no_transaksi_rows))
                                             @foreach($a->no_transaksi_rows as $group)
                                                 @foreach($group as $line)
                                                     @php $isInduk = $loop->first; @endphp
                                                     <div class="d-flex align-items-center {{ $isInduk ? 'mb-1' : 'ms-3 mb-1' }}">
                                                         <i class="bi {{ $isInduk ? 'bi-file-earmark-text' : 'bi-arrow-return-right' }} hierarchy-connector"></i>
                                                         <span class="text-secondary fw-bold font-monospace" style="font-size: 0.78rem; letter-spacing: -0.2px;">{{ $line }}</span>
                                                     </div>
                                                 @endforeach
                                             @endforeach
                                         @else
                                             <div class="d-flex align-items-center">
                                                 <i class="bi bi-file-earmark-text hierarchy-connector"></i>
                                                 <span class="text-secondary fw-bold font-monospace" style="font-size: 0.78rem;">{{ $a->no_transaksi }}</span>
                                             </div>
                                         @endif
                                     </div>
                                 @endif
                            </div>
                        </td>

                        <td class="text-nowrap">
                            @php
                                $jc = 'secondary';
                                if($a->jenis_pengajuan == 'Adjust') $jc = 'info';
                                if(str_contains($a->jenis_pengajuan, 'Mutasi')) $jc = 'primary';
                                if($a->jenis_pengajuan == 'Cancel') $jc = 'danger';
                            @endphp
                            <span class="badge bg-{{ $jc }} bg-opacity-10 text-{{ $jc }} fw-bold px-3 py-1 rounded-pill border-{{ $jc }} border-opacity-25" style="font-size: 0.68rem; border: 1px solid;">
                                {{ str_replace('_', ' ', strtoupper($a->jenis_pengajuan)) }}
                            </span>
                        </td>
    
                        <td class="text-nowrap">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm text-white fw-bold" 
                                     style="width:38px; height:38px; font-size: 1rem; background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);">
                                    {{ substr($a->admin->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="lh-1">
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 110px; font-size: 0.88rem;">{{ $a->admin->name ?? 'Unknown' }}</div>
                                    <small class="text-muted text-uppercase fw-extrabold" style="font-size: 0.62rem; letter-spacing: 0.5px;">{{ $a->admin->role ?? 'PPIC' }}</small>
                                </div>
                            </div>
                        </td>
    
                        <td>
                            <div class="fw-bold text-dark lh-sm mb-1 text-truncate" style="max-width: 160px; font-size: 0.88rem;">{{ $a->department->name ?? '-' }}</div>
                            <span class="bg-light text-secondary px-2 py-0 rounded fw-bold" style="font-size: 0.68rem; border: 1px solid #cbd5e1;">{{ $a->unit->name ?? '-' }}</span>
                        </td>
    
                        <td>
                            <div class="d-flex flex-column gap-2 align-items-start">
                                {{-- Optimized Status Pills for Clarity --}}
                                @php
                                    $kpC = match($a->ket_process) {
                                        'Review'  => ['bg' => '#fefce8', 'text' => '#854d0e', 'border' => '#fde047', 'dot' => '#facc15'],
                                        'Process' => ['bg' => '#f0f9ff', 'text' => '#075985', 'border' => '#7dd3fc', 'dot' => '#38bdf8'], 
                                        'Done'    => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#86efac', 'dot' => '#22c55e'],
                                        'Pending' => ['bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1', 'dot' => '#64748b'],
                                        'Void'    => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fca5a5', 'dot' => '#ef4444'],
                                        'Partial Done' => ['bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe', 'dot' => '#3b82f6'],
                                        default   => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0', 'dot' => '#94a3b8'],
                                    };
                                @endphp
                                <div class="px-3 py-1 rounded-pill fw-bold d-flex align-items-center gap-2 shadow-xs" 
                                     style="font-size: 0.72rem; background: {{ $kpC['bg'] }}; color: {{ $kpC['text'] }}; border: 1.5px solid {{ $kpC['border'] }};">
                                    <div class="rounded-circle shadow-sm" style="width: 6px; height: 6px; background-color: {{ $kpC['dot'] }};"></div>
                                    {{ strtoupper($a->ket_process ?? '-') }}
                                </div>

                                <div class="d-flex gap-1">
                                    <div class="bg-white border text-secondary px-2 py-0 rounded-pill fw-bold shadow-xs d-flex align-items-center" style="font-size: 0.62rem; border-color: #e2e8f0 !important;">
                                        <span class="text-muted opacity-75 me-1">BA:</span> {{ $a->ba ?? 'Pending' }}
                                    </div>
                                    <div class="bg-white border text-secondary px-2 py-0 rounded-pill fw-bold shadow-xs d-flex align-items-center" style="font-size: 0.62rem; border-color: #e2e8f0 !important;">
                                        <span class="text-muted opacity-75 me-1">ARSIP:</span> {{ $a->arsip ?? 'Pending' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <span class="fw-bold text-success" style="font-size: 0.85rem;">+{{ $a->total_qty_in + 0 }}</span>
                                <span class="fw-bold text-danger" style="font-size: 0.85rem;">-{{ $a->total_qty_out + 0 }}</span>
                            </div>
                        </td>
    
                        <td class="ps-3 pe-2">
                            <div class="d-flex flex-column gap-2" style="max-width: 320px;">
                                @php $itemsFound = false; @endphp
                                
                                {{-- BUNDEL --}}
                                @if($a->bundelItems && $a->bundelItems->count() > 0)
                                    @php $itemsFound = true; @endphp
                                    @foreach($a->bundelItems as $item)
                                        <div class="d-flex flex-column">
                                            <div class="item-label-prefix">In - Dokumen Bundel</div>
                                            <div class="item-detail-card">
                                                <div class="lh-1 pe-2">
                                                    <div class="fw-bold text-dark font-monospace mb-1" style="font-size: 0.82rem;">{{ $item->no_doc }}</div>
                                                    <small class="text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 700;">Bundel Archive</small>
                                                </div>
                                                <div class="qty-bubble in">+{{ $item->qty + 0 }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- MUTASI --}}
                                @if($a->mutasiItems && $a->mutasiItems->count() > 0)
                                    @php $itemsFound = true; @endphp
                                    @foreach($a->mutasiItems as $item)
                                        @php 
                                            $isOut = $item->type == 'asal';
                                            $qClass = $isOut ? 'out' : 'in';
                                            $qPrefix = $isOut ? '-' : '+';
                                            $typeLabel = $isOut ? 'Out - Mutasi Asal' : 'In - Mutasi Tujuan';
                                        @endphp
                                        <div class="d-flex flex-column">
                                            <div class="item-label-prefix">{{ $typeLabel }}</div>
                                            <div class="item-detail-card">
                                                <div class="lh-1 pe-2">
                                                    <div class="fw-extrabold text-dark font-monospace mb-1" style="font-size: 0.82rem;">{{ $item->product_code }}</div>
                                                    <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">{{ $item->product_name }}</div>
                                                    @if($item->panjang || $item->location)
                                                        <div class="mt-1 d-flex gap-1" style="font-size: 0.62rem;">
                                                            @if($item->panjang) <span class="badge bg-secondary opacity-75">P: {{ $item->panjang }}</span> @endif
                                                            @if($item->location) <span class="badge bg-light text-dark border" title="{{ $item->location }}"><i class="bi bi-geo-alt"></i> Loc</span> @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="qty-bubble {{ $qClass }}">{{ $qPrefix }}{{ $item->qty + 0 }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- ADJUST --}}
                                @if($a->adjustItems && $a->adjustItems->count() > 0)
                                    @php $itemsFound = true; @endphp
                                    @foreach($a->adjustItems as $item)
                                        @php 
                                            $isOut = ($item->qty_out ?? 0) > 0;
                                            $qClass = $isOut ? 'out' : 'in';
                                            $qPrefix = $isOut ? '-' : '+';
                                            $qty = $isOut ? $item->qty_out : ($item->qty_in ?? ($item->qty_adjust ?? 0));
                                            $typeLabel = $isOut ? 'Out - Adjustment' : 'In - Adjustment';
                                        @endphp
                                        <div class="d-flex flex-column">
                                            <div class="item-label-prefix">{{ $typeLabel }}</div>
                                            <div class="item-detail-card">
                                                <div class="lh-1 pe-2">
                                                    <div class="fw-extrabold text-dark font-monospace mb-1" style="font-size: 0.82rem;">{{ $item->product_code }}</div>
                                                    <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">{{ $item->product_name }}</div>
                                                </div>
                                                <div class="qty-bubble {{ $qClass }}">{{ $qPrefix }}{{ $qty + 0 }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- PEMOHON --}}
                                @if($a->pemohon)
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="badge bg-light text-primary border border-primary border-opacity-10 py-1 px-2" style="font-size: 0.65rem;">
                                            <i class="bi bi-people-fill me-1"></i> {{ Str::limit($a->pemohon, 30) }}
                                        </div>
                                    </div>
                                @endif

                                {{-- Premium Digital Memo Card for fallback keterangan --}}
                                @if(!$itemsFound && $a->keterangan)
                                    <div class="mt-1">
                                        <div class="text-muted italic fw-medium" style="font-size: 0.75rem; line-height: 1.2;">
                                            <i class="bi bi-chat-left-dots me-1"></i> "{{ Str::limit($a->keterangan, 50) }}"
                                        </div>
                                    </div>
                                @elseif(!$itemsFound)
                                    <div class="text-muted small text-center opacity-50">- No Data -</div>
                                @endif
                            </div>
                        </td>

                        <td class="text-center pe-4">
                            <div class="d-flex gap-2 justify-content-center">
                                {{-- VIEW --}}
                                <button class="btn btn-sm btn-info text-white shadow-sm rounded-3 p-2 d-flex align-items-center" 
                                        onclick="showBukti('{{ $a->bukti_scan ? url('/preview-file/'.$a->bukti_scan) : '#' }}')"
                                        {{ !$a->bukti_scan ? 'disabled' : '' }} title="View">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
    
                                 {{-- EDIT --}}
                                 <button class="btn btn-sm btn-warning text-dark shadow-sm rounded-3 p-2 d-flex align-items-center" 
                                         onclick="editArsip({{ $a->id }})" title="Edit">
                                     <i class="bi bi-pencil-fill"></i>
                                 </button>
                                 
                                 {{-- ARSIP SISTEM --}}
                                 <button class="btn btn-sm btn-success text-white shadow-sm rounded-3 p-2 d-flex align-items-center btn-arsip-sistem"
                                         data-id="{{ $a->id }}" data-bs-toggle="modal" data-bs-target="#modalArsipSistem" title="Arsip">
                                     <i class="bi bi-check-lg fw-bold"></i>
                                 </button>
                                
                                {{-- DELETE --}}
                                <form action="{{ route('superadmin.arsip.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus arsip ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger text-white shadow-sm rounded-3 p-2 d-flex align-items-center">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-light rounded-circle p-4 mb-3">
                                    <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                                </div>
                                <h6 class="text-secondary fw-bold">Data Arsip Tidak Ditemukan</h6>
                                <p class="text-muted small mb-0">Coba ubah filter pencarian Anda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 px-2 d-flex justify-content-center">
    {{ $arsips->links('pagination::bootstrap-5') }}
</div>

<script>
    document.getElementById('perPageSelect').addEventListener('change', function() {
        let url = new URL(window.location.href);
        url.searchParams.set('per_page', this.value);
        url.searchParams.set('page', 1); // Reset to page 1
        window.location.href = url.toString();
    });
</script>

{{-- INCLUDE MODALS --}}
@include('superadmin.arsip._create')
@include('superadmin.arsip._view')
@include('superadmin.arsip._arsip_sistem')

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEditArsip" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-gradient-warning text-white border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square fs-4"></i>
                    <div>
                         <h5 class="modal-title fw-bold">Edit Data Pengajuan</h5>
                         <small class="text-white-50">Edit data pengajuan (Superadmin Access)</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formEditArsip" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body p-4 bg-light">
                    <div id="alertEditSuper" class="alert alert-danger d-none py-2 px-3 small border-0 shadow-sm rounded-3"></div>
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            @include('superadmin.arsip._edit')
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-medium text-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // =========================================================================
    // A. LOGIKA TAMPILAN & TAMBAH DATA BARU (CREATE)
    // =========================================================================
    
    // Check if element exists
    const $jenisSelect  = $('#jenisPengajuanTambah');
    const $wrapKategori = $('#wrapperKategori');

    // 1. SHOW/HIDE SECTION
    if($jenisSelect.length) {
        $jenisSelect.on('change', function() {
            const val = $(this).val();
            
            // Reset tampilan
            $('.dynamic-section').addClass('d-none');
            // Reset Inputs (Optional: clear value logic can be added here)
            
            // Clear dynamic rows
            $('tbody.dynamic-row-container').empty(); 

            if (val === 'Cancel') {
                $wrapKategori.removeClass('d-none');
                $('#sectionNoTrans').removeClass('d-none');
            } 
            else if (val === 'Adjust') {
                $wrapKategori.addClass('d-none');
                $('#sectionAdjust').removeClass('d-none');
            } 
            else if (val && val.includes('Mutasi')) {
                $wrapKategori.addClass('d-none');
                $('#sectionMutasi').removeClass('d-none');
            } 
            else if (val === 'Bundel') {
                $wrapKategori.addClass('d-none');
                $('#sectionBundel').removeClass('d-none');
            } 
            else if (val === 'Internal_Memo') {
                 $wrapKategori.addClass('d-none');
                 $('#sectionNoTrans').removeClass('d-none');
            }
            else {
                $wrapKategori.addClass('d-none');
            }
        });

        // Trigger change on load
        $jenisSelect.trigger('change');
    }

    // Helper Random Index
    function getIndex() { return Math.floor(Math.random() * 100000); }

    // 2. TAMBAH BARIS ITEM (CREATE)
    // -- ADJUST --
    $('#btnAddAdjust').on('click', function() {
        let idx = getIndex();
        $('#wrapperAdjust').append(`
            <tr>
                <td><input type="text" name="detail_barang[adjust][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode"></td>
                <td><input type="text" name="detail_barang[adjust][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Barang" required></td>
                <td><input type="number" step="any" name="detail_barang[adjust][${idx}][qty_in]" class="form-control form-control-sm border-0 bg-light text-success fw-bold" value="0"></td>
                <td><input type="number" step="any" name="detail_barang[adjust][${idx}][qty_out]" class="form-control form-control-sm border-0 bg-light text-danger fw-bold" value="0"></td>
                <td><input type="text" name="detail_barang[adjust][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot"></td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    });

    // -- MUTASI --
    window.addMutasiRow = function(targetId, type) {
        let idx = getIndex();
        let locations = @json(\App\Models\ArsipMutasiItem::getLocations());
        let locationOptions = '<option value="">-- Pilih Lokasi --</option>';
        locations.forEach(loc => {
            locationOptions += `<option value="${loc}">${loc}</option>`;
        });

        $(`#${targetId}`).append(`
            <tr>
                <td><input type="text" name="detail_barang[mutasi_${type}][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode" style="width: 80px;"></td>
                <td><input type="text" name="detail_barang[mutasi_${type}][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Produk" required style="min-width: 150px;"></td>
                <td><input type="number" step="any" name="detail_barang[mutasi_${type}][${idx}][qty]" class="form-control form-control-sm border-0 bg-light fw-bold text-center" value="1" required style="width: 70px;"></td>
                <td><input type="text" name="detail_barang[mutasi_${type}][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot" style="width: 90px;"></td>
                <td><input type="text" name="detail_barang[mutasi_${type}][${idx}][panjang]" class="form-control form-control-sm border-0 bg-light" placeholder="Pjg" style="width: 80px;"></td>
                <td>
                    <select name="detail_barang[mutasi_${type}][${idx}][location]" class="form-select form-select-sm border-0 bg-light" style="width: 150px;">
                        ${locationOptions}
                    </select>
                </td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    }
    $('#btnAddAsal').on('click', () => window.addMutasiRow('wrapperAsal', 'asal'));
    $('#btnAddTujuan').on('click', () => window.addMutasiRow('wrapperTujuan', 'tujuan'));

    // -- BUNDEL --
    $('#btnAddBundel').on('click', function() {
        let idx = getIndex();
        $('#wrapperBundel').append(`
            <tr>
                <td><input type="text" name="detail_barang[bundel][${idx}][no_doc]" class="form-control form-control-sm border-0 bg-light" placeholder="No Dokumen" required></td>
                <td><input type="number" step="any" name="detail_barang[bundel][${idx}][qty]" class="form-control form-control-sm border-0 bg-light fw-bold" value="1" required></td>
                <td><input type="text" name="detail_barang[bundel][${idx}][keterangan]" class="form-control form-control-sm border-0 bg-light" placeholder="Keterangan"></td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    });

    // -- HAPUS BARIS --
    $(document).on('click', '.btnRemove', function() { $(this).closest('tr').remove(); });


    // =========================================================================
    // B. LOGIKA EDIT (AJAX)
    // =========================================================================
    
    // Logic Show/Hide Fields di Edit
    $('#editJenisPengajuan').on('change', function() {
        const val = $(this).val();
        
        // Reset Inputs
        $('.dynamic-section-edit').addClass('d-none');
        
        // IMPORTANT: Kita tidak mengosongkan tbody disini agar data lama tidak hilang saat user main-main ganti jenis (UI only)
        // Tapi idealnya bisa di-warning. Untuk simpelnya kita show/hide saja.
        
        if (val === 'Cancel') {
            $('#editWrapperKategori').removeClass('d-none');
            $('#sectionNoTransEdit').removeClass('d-none');
        } 
        else if (val === 'Adjust') {
            $('#sectionAdjustEdit').removeClass('d-none');
        } 
        else if (val && val.includes('Mutasi')) {
            $('#sectionMutasiEdit').removeClass('d-none');
        } 
        else if (val === 'Bundel') {
            $('#sectionBundelEdit').removeClass('d-none');
        }
        else if (val === 'Internal_Memo') {
             $('#sectionNoTransEdit').removeClass('d-none');
        }
    });

    window.editArsip = function(id) {
        // 1. Reset Form
        $('#formEditArsip')[0].reset();
        $('.dynamic-section-edit').addClass('d-none');
        $('#wrapperAdjustEdit, #wrapperAsalEdit, #wrapperTujuanEdit, #wrapperBundelEdit').empty();
        $('#editArsipId').val(id);
        $('#linkBuktiSaatIni').text('');

        // 2. Fetch Data
        $.ajax({
            url: "/superadmin/arsip/" + id + "/edit", // Pastikan Route ini ADA
            type: "GET",
            beforeSend: function() {
                // Bisa kasih loading spinner
            },
            success: function(response) {
                let data = response.data;
                
                // 3. Set URL Update
                $('#formEditArsip').attr('action', "/superadmin/arsip/" + id);
                
                // 4. Fill Data
                $('#editUserId').val(data.admin_id).trigger('change'); // Jika pakai select2
                $('#editTglPengajuan').val(data.tgl_pengajuan ? data.tgl_pengajuan.substring(0, 10) : '');
                $('#editTglArsip').val(data.tgl_arsip ? data.tgl_arsip.substring(0, 10) : '');
                
                $('#editJenisPengajuan').val(data.jenis_pengajuan);
                $('#editKategori').val(data.kategori);
                $('#editDepartment').val(data.department_id);
                $('#editUnit').val(data.unit_id);
                $('#editManager').val(data.manager_id);
                $('#editNoTransaksi').val(data.no_transaksi);
                $('#editPemohon').val(data.pemohon);
                $('#editKeterangan').val(data.keterangan);
                
                $('#editStatus').val(data.status);
                $('#editKetProcess').val(data.ket_process);
                $('#editBa').val(data.ba);
                $('#editArsipStatus').val(data.arsip);

                // 5. Bukti Scan
                if(data.bukti_scan) {
                     $('#linkBuktiSaatIni').html(
                        `<a href="/preview-file/${data.bukti_scan}" target="_blank" class="text-decoration-none fw-bold small text-primary">
                            <i class="bi bi-file-earmark-pdf"></i> Lihat File Saat Ini
                        </a>`
                    );
                }

                // 6. trigger change untuk nampilin section yg benar
                $('#editJenisPengajuan').trigger('change');

                // 7. Fill Detail Items
                // Perlu handling field JSON vs Relasi. Controller edit with relations.
                
                // A. Bundel
                if(data.bundel_items && data.bundel_items.length > 0) {
                    data.bundel_items.forEach(item => {
                        window.addBundelRowEdit(item.no_doc, item.qty, item.keterangan);
                    });
                }
                
                // B. Adjust
                if(data.adjust_items && data.adjust_items.length > 0) {
                    data.adjust_items.forEach(item => {
                        window.addAdjustRowEdit(item.product_code, item.product_name, item.qty_in, item.qty_out, item.lot);
                    });
                }

                // C. Mutasi
                if(data.mutasi_items && data.mutasi_items.length > 0) {
                    data.mutasi_items.forEach(item => {
                        let type = (item.type === 'asal') ? 'asal' : 'tujuan';
                        window.addMutasiRowEdit(type, item.product_code, item.product_name, item.qty, item.lot, item.panjang, item.location);
                    });
                }

                // Fallback Legacy JSON (If needed)
                if((!data.bundel_items || data.bundel_items.length==0) && (!data.adjust_items || data.adjust_items.length==0) && (!data.mutasi_items || data.mutasi_items.length==0)) {
                    // Try parsing details column if exist
                }

                $('#modalEditArsip').modal('show');
            },
            error: function(xgb) {
                alert("Gagal mengambil data arsip.");
            }
        });
    }
    
    // =========================================================================
    // 2.1 STATUS SYNC LOGIC (Superadmin)
    // =========================================================================
    $('#editKetProcess').on('change', function() {
        let val = $(this).val();
        let target = $('#editStatus');
        
        // Auto-Sync Status Utama based on Ket. Proses
        // NOTE: We map 'Done' in Ket. Proses to 'Process' in Status Utama
        // to prevent premature archival date population. Final archival is done via 'Arsip Sistem'.
        if (val === 'Done') target.val('Process');
        else if (val === 'Void') target.val('Void');
        else if (val === 'Pending') target.val('Pending');
        else if (val === 'Partial Done') target.val('Process');
        else if (val === 'Process') target.val('Process');
        else if (val === 'Review') target.val('Check');
    });

    $('#editStatus').on('change', function() {
        let val = $(this).val();
        let target = $('#editKetProcess');
        
        // Auto-Suggest Ket. Proses based on Status Utama
        if (val === 'Done') target.val('Done');
        else if (val === 'Void') target.val('Void');
        else if (val === 'Reject') target.val('Void');
        else if (val === 'Pending') target.val('Pending');
        else if (val === 'Process') target.val('Process');
        else if (val === 'Check') target.val('Review');
    });

    // 3. ARSIP SISTEM
    $('.btn-arsip-sistem').on('click', function() {
         const id = $(this).data('id');
         const f = document.getElementById('formArsipSistem');
         if(f) f.action = `/superadmin/arsip/${id}/arsip-sistem`;
    });

    // 4. AJAX SUBMIT EDIT FORM
    $('#formEditArsip').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');
        let formData = new FormData(this);
        let alertBox = $('#alertEditSuper');

        alertBox.addClass('d-none').removeClass('alert-danger alert-success');

        $.ajax({
            url: url,
            type: 'POST', // Method PUT handled by _method field
            data: formData,
            contentType: false,
            processData: false,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            beforeSend: function() {
                form.find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
            },
            success: function(response) {
                if(response.status === 'success') {
                    alertBox.removeClass('d-none').addClass('alert-success').text(response.message || 'Perubahan berhasil disimpan.');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                form.find('button[type="submit"]').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Perubahan');
                let res = xhr.responseJSON;
                let msg = res.message || 'Terjadi kesalahan saat menyimpan data.';
                if(res.errors) {
                    msg = '';
                    $.each(res.errors, function(k, v) {
                        msg += `<div>- ${v[0]}</div>`;
                    });
                }
                alertBox.removeClass('d-none').addClass('alert-danger').html(msg);
            }
        });
    });

});

// =========================================================================
// C. HELPERS UNTUK EDIT ROW
// =========================================================================

window.addAdjustRowEdit = function(code='', name='', qtyIn=0, qtyOut=0, lot='') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    // Note: Name input hrs sesuai dengan Controller update method expectation. 
    // Di Superadmin ArsipController::update, 'detail_barang' diambil array.
    // Kita samakan structure dengan create: items[adjust][...] atau detail_barang[adjust]...
    // Cek Controller update.. dia merge $request->detail_barang.
    // Jadi name harus: detail_barang[adjust][idx][...]
    
    $('#wrapperAdjustEdit').append(`
        <tr>
            <td><input type="text" name="detail_barang[adjust][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" value="${code ?? ''}"></td>
            <td><input type="text" name="detail_barang[adjust][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" value="${name ?? ''}" required></td>
            <td><input type="number" step="any" name="detail_barang[adjust][${idx}][qty_in]" class="form-control form-control-sm border-0 bg-light text-center" value="${qtyIn}"></td>
            <td><input type="number" step="any" name="detail_barang[adjust][${idx}][qty_out]" class="form-control form-control-sm border-0 bg-light text-center" value="${qtyOut}"></td>
            <td><input type="text" name="detail_barang[adjust][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" value="${lot ?? ''}"></td>
            <td><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>
    `);
};

window.addMutasiRowEdit = function(type, code='', name='', qty=0, lot='', panjang='', location='') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    let wrapper = (type === 'asal') ? '#wrapperAsalEdit' : '#wrapperTujuanEdit';
    let key = (type === 'asal') ? 'mutasi_asal' : 'mutasi_tujuan'; 
    
    let locations = @json(\App\Models\ArsipMutasiItem::getLocations());
    let locationOptions = '<option value="">-- Pilih Lokasi --</option>';
    locations.forEach(loc => {
        let selected = (loc === location) ? 'selected' : '';
        locationOptions += `<option value="${loc}" ${selected}>${loc}</option>`;
    });

    $(wrapper).append(`
        <tr>
            <td><input type="text" name="detail_barang[${key}][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" value="${code ?? ''}" style="width: 80px;"></td>
            <td><input type="text" name="detail_barang[${key}][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" value="${name ?? ''}" required style="min-width: 150px;"></td>
            <td><input type="number" step="any" name="detail_barang[${key}][${idx}][qty]" class="form-control form-control-sm border-0 bg-light text-center" value="${qty}" style="width: 70px;"></td>
            <td><input type="text" name="detail_barang[${key}][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" value="${lot ?? ''}" style="width: 90px;" placeholder="Lot"></td>
            <td><input type="text" name="detail_barang[${key}][${idx}][panjang]" class="form-control form-control-sm border-0 bg-light" value="${panjang ?? ''}" placeholder="Pjg" style="width: 80px;"></td>
            <td>
                <select name="detail_barang[${key}][${idx}][location]" class="form-select form-select-sm border-0 bg-light" style="width: 150px;">
                    ${locationOptions}
                </select>
            </td>
            <td><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>
    `);
};

window.addBundelRowEdit = function(no_doc='', qty=1, ket='') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    $('#wrapperBundelEdit').append(`
        <tr>
            <td><input type="text" name="detail_barang[bundel][${idx}][no_doc]" class="form-control form-control-sm border-0 bg-light" value="${no_doc ?? ''}" required></td>
            <td><input type="number" step="any" name="detail_barang[bundel][${idx}][qty]" class="form-control form-control-sm border-0 bg-light text-center" value="${qty}"></td>
            <td><input type="text" name="detail_barang[bundel][${idx}][keterangan]" class="form-control form-control-sm border-0 bg-light" value="${ket ?? ''}"></td>
            <td><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>
    `);
};

</script>
@endpush