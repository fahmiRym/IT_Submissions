@extends('layouts.app')

@section('title', 'IT Submission Dashboard')
@section('page-title', 'Superadmin Dashboard')

@push('styles')
<style>
    /* 1. TOP STATS CARDS (Soft & Bright) */
    .card-stat-vibrant {
        border: none;
        border-radius: 16px;
        color: white;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .card-stat-vibrant:hover { 
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
    }
    /* Bright Gradients */
    .bg-gradient-blue { background: linear-gradient(135deg, #60a5fa, #2563eb); }
    .bg-gradient-indigo { background: linear-gradient(135deg, #818cf8, #4f46e5); }
    .bg-gradient-green { background: linear-gradient(135deg, #34d399, #059669); }
    .bg-gradient-orange { background: linear-gradient(135deg, #fbbf24, #d97706); }
    
    .stat-overlay-icon {
        position: absolute;
        right: -15px;
        bottom: -15px;
        font-size: 7rem;
        opacity: 0.15;
        transform: rotate(-10deg);
        color: white;
    }

    /* 2. PIPELINE STATUS */
    .card-pipeline {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.02);
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }
    .card-pipeline::before {
        content: ''; position: absolute; top:0; left:0; width: 4px; height: 100%;
        background: currentColor;
    }
    .card-pipeline:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    
    .status-pending { color: #94a3b8; }
    .status-review { color: #38bdf8; }
    .status-process { color: #fbbf24; }
    .status-partial { color: #818cf8; }
    .status-done { color: #34d399; }

    .icon-pipeline {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
        background: rgba(0,0,0,0.03);
        margin-bottom: 0.5rem;
    }

    /* 3. CATEGORY COUNTS */
    .card-category {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        text-align: center;
        padding: 1.5rem 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        transition: transform 0.2s;
    }
    .card-category:hover { transform: translateY(-3px); border-color: #e2e8f0; }
    .icon-category-circle {
        width: 50px; height: 50px;
        border-radius: 50%;
        margin: 0 auto 1rem;
        display: flex; align-items: center; justify-content: center;
        color: white;
        font-size: 1.4rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .cat-count { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.5px; }

    /* 4. CHARTS */
    .card-chart {
        border: none;
        border-radius: 20px;
        background: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        overflow: hidden;
    }
    .card-header-chart {
        background: transparent;
        padding: 1.5rem 1.5rem 0.5rem;
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
    }
    .chart-legend-box {
        display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px;
    }

    /* 5. TABLE MODERN */
    .table-modern thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem;
    }
    .table-modern td { vertical-align: middle; padding: 1.2rem 1rem; color: #334155; }
    .table-modern tbody tr:hover { background-color: #f8fafc; }
</style>
@endpush

@section('content')

{{-- FILTER SECTION --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('superadmin.dashboard') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="small fw-bold text-muted mb-2">DARI TANGGAL</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control bg-light border-0 py-2 rounded-3">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted mb-2">SAMPAI TANGGAL</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control bg-light border-0 py-2 rounded-3">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-2">DEPARTEMEN</label>
                <select name="department_id" class="form-select bg-light border-0 py-2 rounded-3">
                    <option value="">-- Semua Departemen --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                 <label class="small fw-bold text-muted mb-2">JENIS PENGAJUAN</label>
                <select name="jenis_pengajuan" class="form-select bg-light border-0 py-2 rounded-3">
                    <option value="">-- Semua Jenis --</option>
                     @foreach(['Adjust', 'Mutasi_Billet', 'Mutasi_Produk', 'Internal_Memo', 'Bundel', 'Cancel'] as $jenis)
                    <option value="{{ $jenis }}" {{ request('jenis_pengajuan') == $jenis ? 'selected' : '' }}>{{ str_replace('_', ' ', $jenis) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100 fw-bold shadow-sm py-2 rounded-3" style="background: linear-gradient(to right, #4f46e5, #4338ca); border:none;">
                    <i class="bi bi-funnel-fill me-2"></i> FILTER
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 1. TOP CARDS --}}
<div class="row g-4 mb-4">
    <!-- Terarsip -->
    <div class="col-md-3">
        <div class="card-stat-vibrant bg-gradient-indigo h-100 p-4">
            <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Pengajuan Terarsip</h6>
            <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($totalArsip) }}</h2>
            <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-check-all me-1"></i> DONE</div>
            <i class="bi bi-archive-fill stat-overlay-icon"></i>
        </div>
    </div>
    <!-- Total -->
    <div class="col-md-3">
        <div class="card-stat-vibrant bg-gradient-blue h-100 p-4">
             <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Total Pengajuan</h6>
             <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($totalPengajuan) }}</h2>
             <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-inbox-fill me-1"></i> ALL DATA</div>
             <i class="bi bi-layers-fill stat-overlay-icon"></i>
        </div>
    </div>
    <!-- Selesai -->
    <div class="col-md-3">
        <div class="card-stat-vibrant bg-gradient-green h-100 p-4">
             <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Pengajuan Selesai</h6>
             <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($arsipDone) }}</h2>
             <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-check-circle-fill me-1"></i> FINALIZED</div>
             <i class="bi bi-check-circle-fill stat-overlay-icon"></i>
        </div>
    </div>
    <!-- Proses -->
    <div class="col-md-3">
        <div class="card-stat-vibrant bg-gradient-orange h-100 p-4">
             <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Dinamika Proses</h6>
             <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($arsipProcess) }}</h2>
             <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-activity me-1"></i> ON GOING</div>
             <i class="bi bi-graph-up-arrow stat-overlay-icon"></i>
        </div>
    </div>
</div>

{{-- 2. STATUS PROCESS --}}
<h6 class="fw-bold text-dark mb-3 ps-1">Status Proses Pengerjaan</h6>
<div class="row g-4 mb-4">
    @php
        $proc = [
            ['label'=>'PENDING', 'val'=>$ketPending, 'color'=>'status-pending'],
            ['label'=>'REVIEW', 'val'=>$ketReview, 'color'=>'status-review'],
            ['label'=>'PROCESS', 'val'=>$ketProcess, 'color'=>'status-process'],
            ['label'=>'PARTIAL DONE', 'val'=>$ketPartial, 'color'=>'status-partial'],
            ['label'=>'SELESAI', 'val'=>$ketDone, 'color'=>'status-done']
        ];
    @endphp
    @foreach($proc as $p)
    <div class="col">
        <a href="{{ route('superadmin.arsip.index', ['ket_process' => ucfirst(strtolower(str_replace(' (DONE)','',$p['label']))) ]) }}" class="text-decoration-none">
            <div class="card-pipeline {{ $p['color'] }} h-100 p-4">
                <div class="d-flex flex-column align-items-center text-center">
                    <div class="icon-pipeline {{ $p['color'] }} bg-opacity-10 mb-2">
                         <i class="bi bi-circle-fill fs-6"></i>
                    </div>
                     <h3 class="mb-1 fw-bold {{ $p['color'] }}">{{ number_format($p['val']) }}</h3>
                     <div class="small fw-bold text-muted text-uppercase tracking-wider">{{ $p['label'] }}</div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- 3. GLOBAL CHARTS: MONTHLY & STATUS --}}
<div class="row g-4 mb-4">
    <!-- Monthly Trend -->
    <div class="col-md-8">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box bg-primary"></span> Tren Data Masuk Bulanan
            </div>
            <div class="p-4">
                <div style="height: 320px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Status Pie -->
    <div class="col-md-4">
         <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box bg-info"></span> Komposisi Status
            </div>
            <div class="p-4 d-flex align-items-center justify-content-center">
                 <div style="height: 280px; width: 100%; position: relative;">
                    <canvas id="statusChart"></canvas>
                    <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                        <span class="small fw-bold d-block text-uppercase" style="color: #64748b; opacity: 0.8;">Total Data</span>
                        <span class="fw-bold display-6" style="color: #3b82f6;">{{ $totalPengajuan }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. STATISTIK PER JENIS (Cards) --}}
<h6 class="fw-bold text-dark mb-3 ps-1">Statistik per Jenis Pengajuan</h6>
<div class="row g-3 mb-4">
    @php
        $cats = [
            ['label'=>'CANCEL', 'code'=>'Cancel', 'color'=>'#94a3b8'],
            ['label'=>'ADJUST', 'code'=>'Adjust', 'color'=>'#38bdf8'],
            ['label'=>'INT. MEMO', 'code'=>'Internal_Memo', 'color'=>'#fbbf24'],
            ['label'=>'MUTASI PROD', 'code'=>'Mutasi_Produk', 'color'=>'#34d399'],
            ['label'=>'BUNDEL', 'code'=>'Bundel', 'color'=>'#f87171'],
            ['label'=>'MUTASI BIL', 'code'=>'Mutasi_Billet', 'color'=>'#818cf8'],
        ];
    @endphp
    @foreach($cats as $c)
     @php
        $val = $trendByType->where('jenis_pengajuan', $c['code'])->sum('total');
    @endphp
    <div class="col-md-2">
        <div class="card-category h-100">
            <div class="icon-category-circle" style="background: {{ $c['color'] }}">
                <i class="bi bi-folder2-open"></i>
            </div>
            <div class="small fw-bold text-muted mb-1 text-uppercase">{{ $c['label'] }}</div>
            <div class="cat-count" style="color: {{ $c['color'] }}">{{ number_format($val) }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- 5. SPECIFIC TREND CHARTS --}}
<div class="row g-4 mb-4">
    {{-- Adjust --}}
    <div class="col-md-4">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                 <span class="chart-legend-box" style="background: #38bdf8;"></span> Tren Adjustment
            </div>
            <div class="p-3"><div style="height: 200px;"><canvas id="chartAdjust"></canvas></div></div>
        </div>
    </div>
    {{-- Memo --}}
    <div class="col-md-4">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                 <span class="chart-legend-box" style="background: #fbbf24;"></span> Tren Internal Memo
            </div>
            <div class="p-3"><div style="height: 200px;"><canvas id="chartMemo"></canvas></div></div>
        </div>
    </div>
    {{-- Bundel --}}
    <div class="col-md-4">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                 <span class="chart-legend-box" style="background: #f87171;"></span> Tren Bundel
            </div>
            <div class="p-3"><div style="height: 200px;"><canvas id="chartBundel"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Mutasi Produk --}}
    <div class="col-md-4">
        <div class="card-chart h-100">
             <div class="card-header-chart">
                 <span class="chart-legend-box" style="background: #34d399;"></span> Tren Mutasi Produk
            </div>
            <div class="p-3"><div style="height: 200px;"><canvas id="chartMutasiProduk"></canvas></div></div>
        </div>
    </div>
    {{-- Mutasi Billet --}}
    <div class="col-md-4">
        <div class="card-chart h-100">
             <div class="card-header-chart">
                 <span class="chart-legend-box" style="background: #818cf8;"></span> Tren Mutasi Billet
            </div>
            <div class="p-3"><div style="height: 200px;"><canvas id="chartMutasiBillet"></canvas></div></div>
        </div>
    </div>
    {{-- Cancel --}}
    <div class="col-md-4">
        <div class="card-chart h-100">
             <div class="card-header-chart">
                 <span class="chart-legend-box" style="background: #94a3b8;"></span> Tren Cancel / Batal
            </div>
            <div class="p-3"><div style="height: 200px;"><canvas id="chartCancel"></canvas></div></div>
        </div>
    </div>
</div>

{{-- 6. DEPARTMENT STATS --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card-chart">
             <div class="card-header-chart">
                 <span class="chart-legend-box" style="background: #6366f1;"></span> Statistik Total Pengajuan per Departemen
             </div>
             <div class="p-4">
                <div style="height: 350px;">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 7. PENGAJUAN PER DEPARTEMEN (BY TYPE) --}}
<h6 class="fw-bold text-dark mb-3 ps-1">📊 Pengajuan Per Departemen</h6>
<div class="row g-4 mb-4">
    {{-- Cancel --}}
    <div class="col-md-6">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background: #94a3b8;"></span> CANCEL
            </div>
            <div class="p-3"><div style="height: 280px;"><canvas id="chartDeptCancel"></canvas></div></div>
        </div>
    </div>
    {{-- Adjustment --}}
    <div class="col-md-6">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background: #38bdf8;"></span> ADJUSTMENT
            </div>
            <div class="p-3"><div style="height: 280px;"><canvas id="chartDeptAdjust"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Mutasi Produk --}}
    <div class="col-md-6">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background: #34d399;"></span> MUTASI PRODUK
            </div>
            <div class="p-3"><div style="height: 280px;"><canvas id="chartDeptMutasiProduk"></canvas></div></div>
        </div>
    </div>
    {{-- Mutasi Billet --}}
    <div class="col-md-6">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background: #818cf8;"></span> MUTASI BILLET
            </div>
            <div class="p-3"><div style="height: 280px;"><canvas id="chartDeptMutasiBillet"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Internal Memo --}}
    <div class="col-md-6">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background: #fbbf24;"></span> INTERNAL MEMO
            </div>
            <div class="p-3"><div style="height: 280px;"><canvas id="chartDeptMemo"></canvas></div></div>
        </div>
    </div>
    {{-- Bundel --}}
    <div class="col-md-6">
        <div class="card-chart h-100">
            <div class="card-header-chart">
                <span class="chart-legend-box" style="background: #f87171;"></span> BUNDEL
            </div>
            <div class="p-3"><div style="height: 280px;"><canvas id="chartDeptBundel"></canvas></div></div>
        </div>
    </div>
</div>

{{-- 8. LATEST DATA TABLE --}}
<div class="card-chart mb-4">
    <div class="card-header-chart d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2 text-primary"></i> Riwayat Pengajuan Terbaru</span>
        <a href="{{ route('superadmin.arsip.index') }}" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3 shadow-sm">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th class="ps-4">No Registrasi</th>
                    <th>User Pengaju</th>
                    <th>Jenis</th>
                    <th>Departemen</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestArsip as $submission)
                <tr>
                    <td class="ps-4">
                        <span class="font-monospace fw-bold text-primary small">{{ $submission->no_registrasi ?? '-' }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-gradient-indigo text-white d-flex align-items-center justify-content-center me-2 shadow-sm" style="width:32px;height:32px;font-size:0.8rem; font-weight:bold;">
                                {{ substr($submission->admin->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <div class="small fw-bold text-dark">{{ $submission->admin->name ?? 'User' }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $submission->role ?? 'Staff' }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark border fw-bold px-3 py-2">{{ str_replace('_',' ', $submission->jenis_pengajuan) }}</span></td>
                    <td class="small fw-semibold text-secondary">{{ $submission->department->name ?? '-' }}</td>
                    <td>
                        @php
                            $colors = ['Review' => 'info', 'Process' => 'warning', 'Done' => 'success', 'Partial Done' => 'primary', 'Pending' => 'secondary'];
                            $sc = $colors[$submission->ket_process] ?? 'secondary';
                        @endphp
                       <span class="badge bg-{{ $sc }} bg-opacity-10 text-{{ $sc }} border border-{{ $sc }} border-opacity-20 rounded-pill px-3 py-1">
                            {{ $submission->ket_process }}
                       </span>
                    </td>
                    <td class="text-end pe-4 small text-muted font-monospace">{{ $submission->tgl_pengajuan ? $submission->tgl_pengajuan->format('d/m/Y') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5 small">Belum ada data terbaru.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Chart.register(ChartDataLabels);
        const monthData   = @json($monthlyChart);
        const statusData  = @json($statusChart);
        const deptData    = @json($auditByDepartment); 
        const trendByType = @json($trendByType);
        
        Chart.defaults.font.family = "'Outfit', sans-serif";
        Chart.defaults.color = '#64748b';
        Chart.defaults.scale.grid.color = '#f1f5f9';
        Chart.defaults.elements.point.hoverRadius = 6;

        // Gradient Helper
        function createGradient(ctx, colorStart, colorEnd) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, colorStart);
            gradient.addColorStop(1, colorEnd);
            return gradient;
        }

        // 1. Monthly Chart - BRIGHT & CLEAN
        const ctxMonth = document.getElementById('monthlyChart').getContext('2d');
        const gradMonth = createGradient(ctxMonth, 'rgba(56, 189, 248, 0.4)', 'rgba(255, 255, 255, 0)'); // Sky Blue to Transparent

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
                    borderColor: '#0ea5e9', // Sky 500
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
                    tooltip: { mode: 'index', intersect: false, backgroundColor: 'rgba(255,255,255,0.9)', titleColor:'#1e293b', bodyColor:'#64748b', borderColor:'#e2e8f0', borderWidth:1 },
                    datalabels: {
                        align: 'top',
                        anchor: 'end',
                        color: '#0ea5e9',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value) { return value > 0 ? value : ''; },
                        offset: 4
                    }
                },
                scales: { 
                    y: { beginAtZero: true, grid: { color: '#f1f5f9', borderDash: [5,5] }, border: {display:false} }, 
                    x: { grid: { display: false }, border: {display:false} } 
                }
            }
        });

        // 2. Status Chart - SOFT PASTELS (NO DARK COLORS)
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusData.map(d => d.status),
                datasets: [{
                    data: statusData.map(d => d.total),
                    // Light Gray, Sky Blue, Amber, Violet, Emerald
                    backgroundColor: ['#cbd5e1', '#38bdf8', '#fbbf24', '#a78bfa', '#34d399'],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '75%',
                plugins: { 
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10, padding: 20, font: {size: 11} } },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold' },
                        formatter: function(value, ctx) { return value > 0 ? value : ''; }
                    }
                }
            }
        });

        // 3. Dept Chart - VERTICAL
        new Chart(document.getElementById('deptChart'), {
            type: 'bar',
            data: {
                labels: deptData.map(d => d.name),
                datasets: [{
                    label: 'Total',
                    data: deptData.map(d => d.total),
                    backgroundColor: '#818cf8', // Indigo 400
                    hoverBackgroundColor: '#6366f1',
                    borderRadius: 6,
                    barThickness: 25
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#6366f1',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value) { return value > 0 ? value : ''; },
                        offset: 4
                    }
                },
                scales: { 
                    y: { beginAtZero: true, grid: { color: '#f1f5f9', borderDash: [5,5] }, border: { display: false } }, 
                    x: { grid: { display: false }, border: { display:false }, ticks: { autoSkip: false, font: {size: 10}, maxRotation: 45, minRotation: 45 } } 
                }
            }
        });

        // 4. Trend Charts Helper
        function createTrendChart(id, code, color, labelText) {
            const canvas = document.getElementById(id);
            if(!canvas) return;
            const ctx = canvas.getContext('2d');
            
            let dataArr = new Array(12).fill(0);
            if(Array.isArray(code)) {
                code.forEach(c => {
                    trendByType.filter(d => d.jenis_pengajuan === c).forEach(d => dataArr[(d.bulan||1)-1] += d.total);
                });
            } else {
                trendByType.filter(d => d.jenis_pengajuan === code).forEach(d => dataArr[(d.bulan||1)-1] = d.total);
            }

            // Create soft gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, color + '60'); // 60% opacity
            gradient.addColorStop(1, '#ffffff00'); // transparent

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                    datasets: [{
                        label: labelText,
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
                            display: function(context) { return context.dataset.data[context.dataIndex] > 0; },
                            align: 'top',
                            anchor: 'end',
                            color: color,
                            backgroundColor: 'white',
                            borderRadius: 4,
                            padding: { top: 4, bottom: 4, left: 6, right: 6 },
                            font: { size: 11, weight: 'bold' },
                            offset: 4,
                            listeners: {
                                enter: function(context) { context.hovered = true; return true; },
                                leave: function(context) { context.hovered = false; return true; }
                            }
                        }
                    },
                    scales: { 
                        y: { display: false, beginAtZero: true, min: 0, grace: '10%' }, // Add grace for label space
                        x: { display: true, grid: { display: false }, ticks: { font: { size: 10 } } } 
                    },
                    layout: { padding: 5 }
                }
            });
        }

        createTrendChart('chartAdjust', 'Adjust', '#38bdf8', 'Adjust'); // Sky 400
        createTrendChart('chartMemo', 'Internal_Memo', '#fbbf24', 'Memo'); // Amber 400
        createTrendChart('chartBundel', 'Bundel', '#f87171', 'Bundel'); // Red 400
        createTrendChart('chartCancel', 'Cancel', '#94a3b8', 'Cancel'); // Slate 400
        createTrendChart('chartMutasiProduk', 'Mutasi_Produk', '#34d399', 'Mutasi Produk'); // Emerald 400
        createTrendChart('chartMutasiBillet', 'Mutasi_Billet', '#818cf8', 'Mutasi Billet'); // Indigo 400

        // 5. Department Charts by Type
        const deptCancel = @json($deptCancel);
        const deptAdjust = @json($deptAdjust);
        const deptBundel = @json($deptBundel);
        const deptMemo = @json($deptMemo);
        const deptMutasi = @json($deptMutasi);

        // Helper function to create VERTICAL bar chart for departments
        function createDeptChart(canvasId, data, color, label) {
            const canvas = document.getElementById(canvasId);
            if(!canvas) return;
            
            // Filter out departments with 0 submissions and sort by total
            const filteredData = data.filter(d => d.total > 0).sort((a, b) => b.total - a.total);
            
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: filteredData.map(d => d.name),
                    datasets: [{
                        label: label,
                        data: filteredData.map(d => d.total),
                        backgroundColor: color,
                        hoverBackgroundColor: color + 'dd',
                        borderRadius: 6,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(255,255,255,0.95)',
                            titleColor: '#1e293b',
                            bodyColor: '#64748b',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' Pengajuan';
                                }
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: color,
                            font: { weight: 'bold', size: 11 },
                            formatter: function(value) { 
                                return value > 0 ? value : ''; 
                            },
                            offset: 4
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: '#f1f5f9', borderDash: [5,5] }, 
                            border: { display: false },
                            ticks: { 
                                stepSize: 1,
                                font: { size: 10 }
                            }
                        },
                        x: { 
                            grid: { display: false }, 
                            border: { display: false },
                            ticks: {
                                autoSkip: false,
                                font: { size: 10 },
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }

        // Create all department charts
        createDeptChart('chartDeptCancel', deptCancel, '#94a3b8', 'Cancel');
        createDeptChart('chartDeptAdjust', deptAdjust, '#38bdf8', 'Adjustment');
        createDeptChart('chartDeptBundel', deptBundel, '#f87171', 'Bundel');
        createDeptChart('chartDeptMemo', deptMemo, '#fbbf24', 'Internal Memo');
        
        // Mutasi Produk and Billet data from controller
        const deptMutasiProduk = @json($deptMutasiProduk);
        const deptMutasiBillet = @json($deptMutasiBillet);

        createDeptChart('chartDeptMutasiProduk', deptMutasiProduk, '#34d399', 'Mutasi Produk');
        createDeptChart('chartDeptMutasiBillet', deptMutasiBillet, '#818cf8', 'Mutasi Billet');
    });
</script>
@endpush
