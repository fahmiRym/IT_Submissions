@extends('layouts.app')

@section('title', 'Dashboard Admin | IT Submission')
@section('page-title', 'Dashboard Saya')

@push('styles')
<style>
    /* ── TOP STAT CARDS ─────────────────────────────── */
    .card-stat-vibrant {
        border: none;
        border-radius: 16px;
        color: white;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .card-stat-vibrant:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 28px rgba(0,0,0,0.13);
    }
    .bg-gradient-blue   { background: linear-gradient(135deg, #60a5fa, #2563eb); }
    .bg-gradient-indigo { background: linear-gradient(135deg, #818cf8, #4f46e5); }
    .bg-gradient-green  { background: linear-gradient(135deg, #34d399, #059669); }
    .bg-gradient-orange { background: linear-gradient(135deg, #fbbf24, #d97706); }
    .bg-gradient-rose   { background: linear-gradient(135deg, #fb7185, #e11d48); }

    .stat-overlay-icon {
        position: absolute;
        right: -15px;
        bottom: -15px;
        font-size: 7rem;
        opacity: 0.13;
        transform: rotate(-10deg);
        color: white;
    }

    /* ── PIPELINE STATUS CARDS ──────────────────────── */
    .card-pipeline {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.02);
        transition: all 0.22s;
        position: relative;
        overflow: hidden;
    }
    .card-pipeline::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 4px; height: 100%;
        background: currentColor;
    }
    .card-pipeline:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(0,0,0,0.07); }

    .status-pending { color: #94a3b8; }
    .status-review  { color: #38bdf8; }
    .status-process { color: #fbbf24; }
    .status-partial { color: #818cf8; }
    .status-done    { color: #34d399; }
    .status-void    { color: #f43f5e; }

    .icon-pipeline {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        background: rgba(0,0,0,0.04);
        margin-bottom: 0.5rem;
    }

    /* ── CATEGORY MINI CARDS ────────────────────────── */
    .card-category {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        text-align: center;
        padding: 1.4rem 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        transition: transform 0.2s;
    }
    .card-category:hover { transform: translateY(-3px); border-color: #e2e8f0; }
    .icon-category-circle {
        width: 48px; height: 48px;
        border-radius: 50%;
        margin: 0 auto 0.8rem;
        display: flex; align-items: center; justify-content: center;
        color: white;
        font-size: 1.25rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .cat-count { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.5px; }

    /* ── CHART CARDS ────────────────────────────────── */
    .card-chart {
        border: none;
        border-radius: 20px;
        background: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .card-header-chart {
        background: transparent;
        padding: 1.4rem 1.5rem 0.5rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
    }
    .chart-legend-box {
        display: inline-block;
        width: 10px; height: 10px;
        border-radius: 50%;
        margin-right: 8px;
    }

    /* ── TABLE MODERN ───────────────────────────────── */
    .table-modern thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem;
    }
    .table-modern td { vertical-align: middle; padding: 1.1rem 1rem; color: #334155; }
    .table-modern tbody tr:hover { background-color: #f8fafc; }

    .hierarchy-connector { color: #6366f1; opacity: 0.8; margin-right: 6px; }

    .item-detail-card-mini {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 5px 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 4px;
    }
    .qty-bubble-mini {
        padding: 3px 9px;
        border-radius: 6px;
        font-weight: 800;
        font-size: 0.68rem;
        color: #fff;
    }
    .qty-bubble-mini.in  { background: linear-gradient(135deg, #10b981, #059669); }
    .qty-bubble-mini.out { background: linear-gradient(135deg, #ef4444, #dc2626); }
</style>
@endpush

@section('content')

{{-- ── FILTER SECTION ────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3">
            {{-- ROW 1 --}}
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-2">DARI TANGGAL</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control bg-light border-0 py-2 rounded-3 shadow-none">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-2">SAMPAI TANGGAL</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control bg-light border-0 py-2 rounded-3 shadow-none">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-2">DEPARTEMEN</label>
                <select name="department_id" class="form-select bg-light border-0 py-2 rounded-3 shadow-none">
                    <option value="">-- Semua Departemen --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-2">UNIT</label>
                <select name="unit_id" class="form-select bg-light border-0 py-2 rounded-3 shadow-none">
                    <option value="">-- Semua Unit --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- ROW 2 --}}
            <div class="col-md-4">
                <label class="small fw-bold text-muted mb-2">MANAGER</label>
                <select name="manager_id" class="form-select bg-light border-0 py-2 rounded-3 shadow-none">
                    <option value="">-- Semua Manager --</option>
                    @foreach($managers as $m)
                        <option value="{{ $m->id }}" {{ request('manager_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-2">KATEGORI</label>
                <select name="kategori" class="form-select bg-light border-0 py-2 rounded-3 shadow-none">
                    <option value="">-- Semua Kategori --</option>
                    <option value="Human" {{ request('kategori')=='Human'?'selected':'' }}>Human Error</option>
                    <option value="System" {{ request('kategori')=='System'?'selected':'' }}>System Error</option>
                    <option value="None" {{ request('kategori')=='None'?'selected':'' }}>None/Adjust</option>
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary flex-fill fw-extrabold shadow-sm py-2 rounded-3" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border:none;">
                    <i class="bi bi-funnel-fill me-1"></i> TERAPKAN FILTER
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light border bg-white shadow-sm py-2 px-3 rounded-3" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── 1. TOP STAT CARDS ──────────────────────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <a href="{{ route('admin.arsip.index', request()->all()) }}" class="text-decoration-none h-100 d-block">
            <div class="card-stat-vibrant bg-gradient-indigo h-100 p-4">
                <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Total Pengajuan</h6>
                <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($total) }}</h2>
                <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-layers-fill me-1"></i> ALL SUBMISSION</div>
                <i class="bi bi-layers-fill stat-overlay-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.arsip.index', array_merge(request()->all(), ['ket_process' => 'Review'])) }}" class="text-decoration-none h-100 d-block">
            <div class="card-stat-vibrant bg-gradient-blue h-100 p-4">
                <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Need Review</h6>
                <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($Review) }}</h2>
                <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-clock-history me-1"></i> WAITING</div>
                <i class="bi bi-clock-history stat-overlay-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.arsip.index', array_merge(request()->all(), ['ket_process' => 'Process'])) }}" class="text-decoration-none h-100 d-block">
            <div class="card-stat-vibrant bg-gradient-orange h-100 p-4">
                <h6 class="text-white-50 text-uppercase small fw-bold mb-2">In Process</h6>
                <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($process) }}</h2>
                <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-gear-fill me-1"></i> ON GOING</div>
                <i class="bi bi-gear-fill stat-overlay-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.arsip.index', array_merge(request()->all(), ['ket_process' => 'Done'])) }}" class="text-decoration-none h-100 d-block">
            <div class="card-stat-vibrant bg-gradient-green h-100 p-4">
                <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Completed</h6>
                <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($done) }}</h2>
                <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-check-circle-fill me-1"></i> FINALIZED</div>
                <i class="bi bi-check-circle-fill stat-overlay-icon"></i>
            </div>
        </a>
    </div>
</div>

{{-- ── 2. PIPELINE STATUS ──────────────────────────────────────── --}}
<h6 class="fw-bold text-dark mb-3 ps-1">Status Proses Pengerjaan</h6>
<div class="row g-3 mb-4">
    @php
        $proc = [
            ['label' => 'PENDING',      'val' => $pending ?? 0, 'db' => 'Pending', 'color' => 'status-pending'],
            ['label' => 'REVIEW',       'val' => $Review ?? 0,  'db' => 'Review',  'color' => 'status-review'],
            ['label' => 'PROCESS',      'val' => $process ?? 0, 'db' => 'Process', 'color' => 'status-process'],
            ['label' => 'PARTIAL DONE', 'val' => $partial ?? 0, 'db' => 'Partial Done', 'color' => 'status-partial'],
            ['label' => 'SELESAI',      'val' => $done ?? 0,    'db' => 'Done',    'color' => 'status-done'],
            ['label' => 'VOID / REJECT','val' => $void ?? 0,    'db' => 'Void',    'color' => 'status-void'],
        ];
    @endphp
    @foreach($proc as $p)
    <div class="col-md-2">
        <a href="{{ route('admin.arsip.index', array_merge(request()->all(), ['ket_process' => $p['db']])) }}" class="text-decoration-none h-100 d-block">
            <div class="card-pipeline {{ $p['color'] }} h-100 p-3">
                <div class="d-flex flex-column align-items-center text-center">
                    <div class="icon-pipeline {{ $p['color'] }} bg-opacity-10 mb-2" style="width: 40px; height: 40px;">
                        <i class="bi bi-circle-fill fs-6"></i>
                    </div>
                    <h4 class="mb-1 fw-bold {{ $p['color'] }}">{{ number_format($p['val']) }}</h4>
                    <div class="fw-bold text-muted text-uppercase tracking-wider" style="font-size: 0.6rem;">{{ $p['label'] }}</div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- ── 3. CHARTS: MONTHLY TREND + STATUS DOUGHNUT ──────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box bg-primary"></span> Tren Data Masuk Bulanan (Pengajuan Saya)
            </div>
            <div class="p-4">
                <div style="height: 300px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box bg-info"></span> Komposisi Status
            </div>
            <div class="p-4 d-flex align-items-center justify-content-center">
                <div style="height: 270px; width: 100%; position: relative;">
                    <canvas id="statusChart"></canvas>
                    <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events:none;">
                        <span class="small fw-bold d-block text-uppercase" style="color:#64748b;opacity:0.8;">Total</span>
                        <span class="fw-bold display-6" style="color:#3b82f6;">{{ $total }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── 4. STATISTIK PER JENIS ──────────────────────────────────── --}}
<h6 class="fw-bold text-dark mb-3 ps-1">Statistik per Jenis Pengajuan</h6>
<div class="row g-3 mb-4">
    @php
        $cats = [
            ['label' => 'CANCEL',       'val' => $cancelCount,       'code' => 'Cancel',        'color' => '#94a3b8'],
            ['label' => 'ADJUST',       'val' => $adjustCount,       'code' => 'Adjust',        'color' => '#38bdf8'],
            ['label' => 'INT. MEMO',    'val' => $internalMemoCount, 'code' => 'Internal_Memo', 'color' => '#fbbf24'],
            ['label' => 'MUTASI PROD',  'val' => $mutasiProdukCount, 'code' => 'Mutasi_Produk', 'color' => '#34d399'],
            ['label' => 'BUNDEL',       'val' => $bundelCount,       'code' => 'Bundel',        'color' => '#f87171'],
            ['label' => 'MUTASI BIL',   'val' => $mutasiBilletCount, 'code' => 'Mutasi_Billet',  'color' => '#818cf8'],
        ];
    @endphp
    @foreach($cats as $c)
    <div class="col-md-2">
        <a href="{{ route('admin.arsip.index', array_merge(request()->all(), ['jenis_pengajuan' => $c['code']])) }}" class="text-decoration-none h-100 d-block">
            <div class="card-category h-100">
                <div class="icon-category-circle" style="background: {{ $c['color'] }}">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <div class="small fw-bold text-muted mb-1 text-uppercase">{{ $c['label'] }}</div>
                <div class="cat-count" style="color: {{ $c['color'] }}">{{ number_format($c['val']) }}</div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- ── 5. TREND CHARTS PER JENIS ───────────────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background:#38bdf8;"></span> Tren Adjustment
            </div>
            <div class="p-3"><div style="height:200px;"><canvas id="chartAdjust"></canvas></div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background:#fbbf24;"></span> Tren Internal Memo
            </div>
            <div class="p-3"><div style="height:200px;"><canvas id="chartMemo"></canvas></div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background:#f87171;"></span> Tren Bundel
            </div>
            <div class="p-3"><div style="height:200px;"><canvas id="chartBundel"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background:#34d399;"></span> Tren Mutasi Produk
            </div>
            <div class="p-3"><div style="height:200px;"><canvas id="chartMutasiProduk"></canvas></div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background:#818cf8;"></span> Tren Mutasi Billet
            </div>
            <div class="p-3"><div style="height:200px;"><canvas id="chartMutasiBillet"></canvas></div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background:#94a3b8;"></span> Tren Cancel / Batal
            </div>
            <div class="p-3"><div style="height:200px;"><canvas id="chartCancel"></canvas></div></div>
        </div>
    </div>
</div>

{{-- ── 6. RIWAYAT PENGAJUAN TABLE ──────────────────────────────── --}}
<div class="card-chart mb-4">
    <div class="card-header-chart d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2 text-primary"></i> Riwayat Pengajuan Saya</span>
        <a href="{{ route('admin.arsip.index') }}" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3 shadow-sm">
            Lihat Semua
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Tgl Pengajuan</th>
                    <th>No Reg / Transaksi</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end pe-4">Detail</th>
                </tr>
            </thead>
            <tbody>
            @forelse($arsips as $a)
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark" style="font-size:.9rem;">{{ optional($a->tgl_pengajuan)->format('d M Y') }}</div>
                        <div class="small text-muted"><i class="bi bi-clock me-1 text-primary opacity-75"></i>{{ optional($a->created_at)->format('H:i') }}</div>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            @if($a->no_registrasi)
                                <div class="px-2 py-0 rounded border border-info border-opacity-50 text-info font-monospace fw-bold shadow-sm" style="font-size:0.72rem;width:fit-content;background:#f0f9ff;">
                                    {{ $a->no_registrasi }}
                                </div>
                            @endif
                            @if($a->no_transaksi)
                                <div class="d-flex align-items-center mt-1">
                                    <i class="bi bi-file-earmark-text hierarchy-connector"></i>
                                    <span class="text-primary fw-bold font-monospace" style="font-size:0.75rem;">{{ $a->no_transaksi }}</span>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td>
                        @php
                            $jc = match($a->jenis_pengajuan) {
                                'Adjust'        => ['bg' => '#f0f9ff', 'text' => '#0ea5e9'],
                                'Cancel'        => ['bg' => '#fef2f2', 'text' => '#ef4444'],
                                'Bundel'        => ['bg' => '#f0fdf4', 'text' => '#10b981'],
                                'Internal_Memo' => ['bg' => '#fffbeb', 'text' => '#d97706'],
                                'Mutasi_Produk' => ['bg' => '#f0fdf4', 'text' => '#059669'],
                                'Mutasi_Billet' => ['bg' => '#f5f3ff', 'text' => '#7c3aed'],
                                default         => ['bg' => '#f5f3ff', 'text' => '#6366f1'],
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-pill fw-bold" style="font-size:0.68rem; background:{{ $jc['bg'] }}; color:{{ $jc['text'] }}; border:1px solid {{ $jc['text'] }}20;">
                            {{ strtoupper(str_replace('_', ' ', $a->jenis_pengajuan)) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $kpC = match($a->ket_process) {
                                'Review'       => ['bg' => '#fefce8', 'text' => '#854d0e', 'border' => '#fde047', 'dot' => '#facc15'],
                                'Process'      => ['bg' => '#f0f9ff', 'text' => '#075985', 'border' => '#7dd3fc', 'dot' => '#38bdf8'],
                                'Done'         => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#86efac', 'dot' => '#22c55e'],
                                'Partial Done' => ['bg' => '#f5f3ff', 'text' => '#5b21b6', 'border' => '#c4b5fd', 'dot' => '#8b5cf6'],
                                default        => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1', 'dot' => '#64748b'],
                            };
                        @endphp
                        <div class="px-3 py-1 rounded-pill fw-bold d-flex align-items-center gap-2"
                             style="font-size:0.68rem; background:{{ $kpC['bg'] }}; color:{{ $kpC['text'] }}; border:1.5px solid {{ $kpC['border'] }}; width:fit-content;">
                            <div class="rounded-circle" style="width:6px;height:6px;background:{{ $kpC['dot'] }};"></div>
                            {{ strtoupper($a->ket_process) }}
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <span class="badge rounded-pill bg-success fw-bold px-2" style="font-size:.72rem;">+{{ (int)$a->total_qty_in }}</span>
                            <span class="badge rounded-pill bg-danger fw-bold px-2" style="font-size:.72rem;">-{{ (int)$a->total_qty_out }}</span>
                        </div>
                        <small class="text-muted fw-bold mt-1 d-block" style="font-size:.6rem;">TOTAL QTY</small>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex flex-column gap-1 align-items-end" style="min-width:200px;">
                            @php $itemsFound = false; @endphp
                            @if($a->adjustItems->count() > 0)
                                @php $itemsFound = true; @endphp
                                @foreach($a->adjustItems->take(2) as $item)
                                    <div class="item-detail-card-mini w-100">
                                        <div class="fw-bold text-dark font-monospace" style="font-size:.7rem;">{{ $item->product_code }}</div>
                                        <div class="qty-bubble-mini {{ $item->qty_out > 0 ? 'out' : 'in' }}">
                                            {{ $item->qty_out > 0 ? '-'.(int)$item->qty_out : '+'.(int)$item->qty_in }}
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            @if(str_contains($a->jenis_pengajuan, 'Mutasi') && $a->mutasiItems->count() > 0)
                                @php $itemsFound = true; @endphp
                                @foreach($a->mutasiItems->take(2) as $item)
                                    <div class="item-detail-card-mini w-100">
                                        <div class="fw-bold text-dark font-monospace" style="font-size:.7rem;">{{ $item->product_code }}</div>
                                        <div class="qty-bubble-mini {{ $item->type == 'asal' ? 'out' : 'in' }}">
                                            {{ $item->type == 'asal' ? '-'.(int)$item->qty : '+'.(int)$item->qty }}
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            @if(!$itemsFound && $a->keterangan)
                                <div class="text-muted small fst-italic text-truncate" style="max-width:190px;">"{{ $a->keterangan }}"</div>
                            @endif
                            @if($a->adjustItems->count() > 2 || $a->mutasiItems->count() > 2)
                                <small class="text-primary fw-bold mt-1" style="font-size:.64rem;">+{{ max($a->adjustItems->count(), $a->mutasiItems->count()) - 2 }} lainnya</small>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted small">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>Belum ada riwayat pengajuan.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($arsips->hasPages())
    <div class="p-4 bg-light bg-opacity-50 border-top">
        {{ $arsips->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Chart.register(ChartDataLabels);

    const monthData   = @json($monthlyChart);
    const statusData  = @json($statusChart);
    const trendByType = @json($trendByType);

    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.scale.grid.color = '#f1f5f9';

    // ── Gradient Helper ──────────────────────────────────────────
    function createGradient(ctx, colorStart, colorEnd) {
        const g = ctx.createLinearGradient(0, 0, 0, 300);
        g.addColorStop(0, colorStart);
        g.addColorStop(1, colorEnd);
        return g;
    }

    // ── 1. Monthly Trend Chart ───────────────────────────────────
    const ctxMonth = document.getElementById('monthlyChart').getContext('2d');
    const gradMonth = createGradient(ctxMonth, 'rgba(56,189,248,0.4)', 'rgba(255,255,255,0)');

    new Chart(ctxMonth, {
        type: 'line',
        data: {
            labels: monthData.map(d => {
                const date = new Date(); date.setMonth((d.bulan||1)-1);
                return date.toLocaleString('id-ID', { month: 'short' });
            }),
            datasets: [{
                label: 'Pengajuan',
                data: monthData.map(d => d.total),
                borderColor: '#0ea5e9',
                backgroundColor: gradMonth,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0ea5e9',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false, backgroundColor: 'rgba(255,255,255,0.95)', titleColor:'#1e293b', bodyColor:'#64748b', borderColor:'#e2e8f0', borderWidth:1 },
                datalabels: {
                    align: 'top', anchor: 'end',
                    color: '#0ea5e9',
                    font: { weight: 'bold', size: 11 },
                    formatter: v => v > 0 ? v : '',
                    offset: 4
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9', borderDash:[5,5] }, border:{display:false} },
                x: { grid: { display: false }, border:{display:false} }
            }
        }
    });

    // ── 2. Status Doughnut ───────────────────────────────────────
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusData.map(d => d.status),
            datasets: [{
                data: statusData.map(d => d.total),
                backgroundColor: ['#cbd5e1','#38bdf8','#fbbf24','#a78bfa','#34d399'],
                borderWidth: 0,
                hoverOffset: 14
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10, padding: 18, font:{size:11} } },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold' },
                    formatter: (v, ctx) => v > 0 ? v : ''
                }
            }
        }
    });

    // ── 3. Trend per Jenis Helper ────────────────────────────────
    function createTrendChart(id, code, color) {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        let dataArr = new Array(12).fill(0);
        if (Array.isArray(code)) {
            code.forEach(c => {
                trendByType.filter(d => d.jenis_pengajuan === c).forEach(d => dataArr[(d.bulan||1)-1] += d.total);
            });
        } else {
            trendByType.filter(d => d.jenis_pengajuan === code).forEach(d => dataArr[(d.bulan||1)-1] = d.total);
        }

        const gradient = ctx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, color + '60');
        gradient.addColorStop(1, '#ffffff00');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                datasets: [{
                    data: dataArr,
                    borderColor: color,
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: color,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false },
                    datalabels: {
                        display: ctx => ctx.dataset.data[ctx.dataIndex] > 0,
                        align: 'top', anchor: 'end',
                        color: color,
                        backgroundColor: 'white',
                        borderRadius: 4,
                        padding: { top:4, bottom:4, left:6, right:6 },
                        font: { size:11, weight:'bold' },
                        offset: 4
                    }
                },
                scales: {
                    y: { display: false, beginAtZero: true, min: 0, grace:'10%' },
                    x: { display: true, grid:{display:false}, ticks:{font:{size:10}} }
                },
                layout: { padding: 5 }
            }
        });
    }

    createTrendChart('chartAdjust',       'Adjust',        '#38bdf8');
    createTrendChart('chartMemo',         'Internal_Memo', '#fbbf24');
    createTrendChart('chartBundel',       'Bundel',        '#f87171');
    createTrendChart('chartCancel',       'Cancel',        '#94a3b8');
    createTrendChart('chartMutasiProduk', 'Mutasi_Produk', '#34d399');
    createTrendChart('chartMutasiBillet', 'Mutasi_Billet', '#818cf8');

    // Auto Refresh setiap 1 menit (60000ms)
    setTimeout(function() {
        window.location.reload();
    }, 60000);
});
</script>
@endpush
