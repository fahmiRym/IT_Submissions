@extends('layouts.app')

@section('title', 'IT Submission Dashboard')
@section('page-title', 'Superadmin Dashboard')

@section('content')

{{-- FILTER SECTION --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('superadmin.dashboard') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="small fw-bold text-secondary mb-1"><i class="bi bi-calendar-event me-1"></i>Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control bg-light border-0">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-secondary mb-1"><i class="bi bi-calendar-event me-1"></i>Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control bg-light border-0">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-secondary mb-1"><i class="bi bi-building me-1"></i>Departemen</label>
                <select name="department_id" class="form-select bg-light border-0">
                    <option value="">-- Semua Departemen --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-secondary mb-1"><i class="bi bi-tags me-1"></i>Jenis Pengajuan</label>
                <select name="jenis_pengajuan" class="form-select bg-light border-0">
                    <option value="">-- Semua Jenis --</option>
                    <option value="Adjust" {{ request('jenis_pengajuan') == 'Adjust' ? 'selected' : '' }}>Adjust</option>
                    <option value="Mutasi_Billet" {{ request('jenis_pengajuan') == 'Mutasi_Billet' ? 'selected' : '' }}>Mutasi Billet</option>
                    <option value="Mutasi_Produk" {{ request('jenis_pengajuan') == 'Mutasi_Produk' ? 'selected' : '' }}>Mutasi Produk</option>
                    <option value="Internal_Memo" {{ request('jenis_pengajuan') == 'Internal_Memo' ? 'selected' : '' }}>Internal Memo</option>
                    <option value="Bundel" {{ request('jenis_pengajuan') == 'Bundel' ? 'selected' : '' }}>Bundel</option>
                    <option value="Cancel" {{ request('jenis_pengajuan') == 'Cancel' ? 'selected' : '' }}>Cancel</option>
                </select>
            </div>
            <div class="col-md-2">
                 <button class="btn btn-primary w-100 fw-bold shadow-sm" style="background: #4f46e5; border-color: #4f46e5;">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- STATS CARDS --}}
{{-- STATS CARDS --}}
@php
    $stats = [
        [
            'title' => 'PENGAJUAN TERARSIP', 
            'value' => $totalArsip, 
            'icon' => 'bi-archive-fill', 
            'bg_gradient' => 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)', // Indigo Gradient
            'text_color' => 'text-white',
            'desc' => 'Sudah Diarsip (Done)'
        ],
        [
            'title' => 'TOTAL PENGAJUAN', 
            'value' => $totalPengajuan, 
            'icon' => 'bi-grid-fill', 
            'bg_gradient' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)', // Blue Gradient
            'text_color' => 'text-white',
            'desc' => 'Semua Data Masuk'
        ],
        [
            'title' => 'PENGAJUAN SELESAI', 
            'value' => $arsipDone, 
            'icon' => 'bi-check-circle-fill', 
            'bg_gradient' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)', // Emerald Gradient
            'text_color' => 'text-white',
            'desc' => 'Status Final (Done)'
        ],
        [
            'title' => 'DINAMIKA PROSES', 
            'value' => $arsipProcess, 
            'icon' => 'bi-arrow-repeat', 
            'bg_gradient' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)', // Amber Gradient
            'text_color' => 'text-white',
            'desc' => 'Sedang Berjalan'
        ]
    ];
@endphp

<div class="row g-4 mb-4">
    @foreach($stats as $s)
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative" style="border-radius: 16px; background: {{ $s['bg_gradient'] }};">
            <!-- Decorative Circle -->
            <div class="position-absolute top-0 end-0 rounded-circle bg-white opacity-10" style="width: 120px; height: 120px; margin-top: -30px; margin-right: -30px;"></div>
            
            <div class="card-body p-4 text-white position-relative">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-white-50 fw-bold small text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">{{ $s['title'] }}</div>
                        <h2 class="fw-bold mb-0 display-6">{{ number_format($s['value']) }}</h2>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-white bg-opacity-25 text-white shadow-sm" style="width: 48px; height: 48px;">
                        <i class="bi {{ $s['icon'] }} fs-4"></i>
                    </div>
                </div>
                <div class="small text-white-50 d-flex align-items-center">
                    <i class="bi bi-info-circle me-1"></i> {{ $s['desc'] }}
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- SECTION: STATUS PROSES OVERVIEW --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-activity me-2 text-primary"></i>Status Proses Pengerjaan</h6>
    </div>

    {{-- 1. PENDING --}}
    <div class="col-md">
        <a href="{{ route('superadmin.arsip.index', ['ket_process' => 'Pending']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-scale overflow-hidden">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-3 bg-secondary bg-opacity-10 text-secondary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="text-secondary fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Pending</div>
                        <h3 class="fw-bold text-dark mb-0">{{ $ketPending }}</h3>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-secondary" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </a>
    </div>

    {{-- 2. REVIEW --}}
    <div class="col-md">
        <a href="{{ route('superadmin.arsip.index', ['ket_process' => 'Review']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-scale overflow-hidden">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-search fs-4"></i>
                    </div>
                    <div>
                        <div class="text-info fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Review</div>
                        <h3 class="fw-bold text-dark mb-0">{{ $ketReview }}</h3>
                    </div>
                </div>
                 <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </a>
    </div>

    {{-- 3. PROCESS --}}
    <div class="col-md">
        <a href="{{ route('superadmin.arsip.index', ['ket_process' => 'Process']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-scale overflow-hidden">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-gear-wide-connected fs-4"></i>
                    </div>
                    <div>
                        <div class="text-warning fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Process</div>
                        <h3 class="fw-bold text-dark mb-0">{{ $ketProcess }}</h3>
                    </div>
                </div>
                 <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </a>
    </div>

     {{-- 4. PARTIAL DONE --}}
    <div class="col-md">
        <a href="{{ route('superadmin.arsip.index', ['ket_process' => 'Partial Done']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-scale overflow-hidden">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-pie-chart-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-primary fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Partial Done</div>
                        <h3 class="fw-bold text-dark mb-0">{{ $ketPartial }}</h3>
                    </div>
                </div>
                 <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </a>
    </div>

    {{-- 5. DONE --}}
    <div class="col-md">
        <a href="{{ route('superadmin.arsip.index', ['ket_process' => 'Done']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-scale overflow-hidden">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-success fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Selesai (Done)</div>
                        <h3 class="fw-bold text-dark mb-0">{{ $ketDone }}</h3>
                    </div>
                </div>
                 <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-12">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>Statistik per Jenis Pengajuan</h6>
    </div>

@foreach($jenisPengajuanChart as $js)
       @php
          $colors = [
            'Adjust' => ['bg' => 'info', 'icon' => 'bi-sliders'], 
            'Mutasi_Billet' => ['bg' => 'primary', 'icon' => 'bi-arrow-left-right'], 
            'Mutasi_Produk' => ['bg' => 'success', 'icon' => 'bi-box-seam'], 
            'Internal_Memo' => ['bg' => 'warning', 'icon' => 'bi-file-text'], 
            'Bundel' => ['bg' => 'danger', 'icon' => 'bi-collection'], 
            'Cancel' => ['bg' => 'secondary', 'icon' => 'bi-x-circle']
          ];
          $conf = $colors[$js->jenis_pengajuan] ?? ['bg' => 'primary', 'icon' => 'bi-file'];
       @endphp
       <div class="col-md-2 col-6">
           <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden hover-scale">
               <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                   <div class="rounded-circle bg-{{ $conf['bg'] }} bg-opacity-10 text-{{ $conf['bg'] }} p-3 mb-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                       <i class="{{ $conf['icon'] }} fs-5"></i>
                   </div>
                   <h6 class="text-secondary fw-bold small text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ str_replace('_',' ', $js->jenis_pengajuan) }}</h6>
                   <h4 class="fw-bold text-dark mb-0 counter">{{ $js->total }}</h4>
               </div>
               <div class="position-absolute bottom-0 start-0 w-100 bg-{{ $conf['bg'] }}" style="height: 4px;"></div>
           </div>
       </div>
    @endforeach
</div>

{{-- CHARTS ROW 2: Specific Trend Charts (Grid) --}}
<div class="row g-4 mb-4">
    {{-- 1. ADJUSTMENT TREND --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-sliders me-2 text-info"></i>Tren Adjustment</h6>
            </div>
            <div class="card-body">
                <div style="height: 200px;">
                    <canvas id="adjustChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    {{-- 2. INTERNAL MEMO TREND --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-file-text me-2 text-warning"></i>Tren Internal Memo</h6>
            </div>
            <div class="card-body">
                <div style="height: 200px;">
                    <canvas id="memoChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. BUNDEL TREND --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-collection me-2 text-danger"></i>Tren Bundel</h6>
            </div>
            <div class="card-body">
                <div style="height: 200px;">
                    <canvas id="bundelChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. MUTASI TREND --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Tren Mutasi (Billet & Produk)</h6>
            </div>
            <div class="card-body">
                <div style="height: 220px;">
                    <canvas id="mutasiChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. CANCEL TREND (NEW) --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-x-circle me-2 text-secondary"></i>Tren Cancel / Batal</h6>
            </div>
            <div class="card-body">
                <div style="height: 220px;">
                    <canvas id="cancelChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CHARTS ROW 4: DEPARTEMEN TOP 5 PER JENIS (Revised) --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-trophy-fill me-2 text-primary"></i>Pengajuan Per Departemen</h6>
    </div>

    {{-- 1. DEPT: CANCEL --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
             <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-x-circle me-2 text-secondary"></i>CANCEL</h6>
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="deptCancelChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. DEPT: ADJUST --}}
    <div class="col-md-6">
         <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
             <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-sliders me-2 text-info"></i>ADJUSTMENT</h6>
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="deptAdjustChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. DEPT: MUTASI --}}
    <div class="col-md-4">
         <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
             <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-arrow-left-right me-2 text-primary"></i>MUTASI</h6>
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="deptMutasiChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. DEPT: BUNDEL --}}
    <div class="col-md-4">
         <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
             <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-collection me-2 text-danger"></i>BUNDEL</h6>
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="deptBundelChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. DEPT: MEMO --}}
    <div class="col-md-4">
         <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
             <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-file-text me-2 text-warning"></i>MEMO</h6>
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="deptMemoChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CHARTS ROW 4: Monthly Trend (Global) & Status --}}
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
             <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>Grafik Pengajuan Bulanan</h6>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart-fill me-2 text-info"></i>Status Pengajuan</h6>
            </div>
            <div class="card-body">
                 <div style="height: 250px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CHARTS ROW 2: Department Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-building me-2 text-warning"></i>Statistik Pengajuan per Departemen</h6>
            </div>
            <div class="card-body">
                <div style="height: 350px;">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLE LATEST SUBMISSIONS --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pengajuan Terbaru</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4">No Reg</th>
                        <th>User</th>
                        <th>Jenis</th>
                        <th>Departemen</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestArsip as $submission)
                    <tr>
                        <td class="ps-4 fw-bold text-dark text-xs font-monospace">{{ $submission->no_registrasi ?? '-' }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width:24px;height:24px;font-size:0.7rem;">
                                    {{ substr($submission->admin->name ?? 'U', 0, 1) }}
                                </span>
                                <span class="small fw-semibold">{{ $submission->admin->name ?? 'User' }}</span>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-secondary border border-secondary border-opacity-25">{{ $submission->jenis_pengajuan }}</span></td>
                        <td class="small">{{ $submission->department->name ?? '-' }}</td>
                        <td>
                             @php
                                $colors = ['Review' => 'info', 'Process' => 'warning', 'Done' => 'success', 'Partial Done' => 'primary', 'Pending' => 'secondary'];
                                $sc = $colors[$submission->ket_process] ?? 'secondary';
                            @endphp
                           <span class="badge bg-{{ $sc }} text-{{ $sc }} bg-opacity-10 rounded-pill" style="font-size: 0.7rem;">{{ $submission->ket_process }}</span>
                        </td>
                        <td class="text-end pe-4 text-muted small">{{ $submission->tgl_pengajuan ? $submission->tgl_pengajuan->format('d/m/Y') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted small py-3">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Data from Controller
        const monthData   = @json($monthlyChart);
        const statusData  = @json($statusChart);
        const deptData    = @json($auditByDepartment);
        const trendByType = @json($trendByType);
        // New specific dept data
        const deptCancel = @json($deptCancel);
        const deptAdjust = @json($deptAdjust);
        const deptMutasi = @json($deptMutasi);
        const deptBundel = @json($deptBundel);
        const deptMemo   = @json($deptMemo);

        // Common Config
        Chart.defaults.font.family = "'Outfit', sans-serif";
        Chart.defaults.color = '#64748b';

        // 1. Line Chart (Monthly)
        const ctxMonthly = document.getElementById('monthlyChart');
        if (ctxMonthly && monthData.length > 0) {
            new Chart(ctxMonthly, {
                type: 'line',
                data: {
                    labels: monthData.map(d => {
                        const date = new Date();
                        date.setMonth((d.bulan || 1) - 1); // fallback if blank
                        return date.toLocaleString('id-ID', { month: 'short' });
                    }),
                    datasets: [{
                        label: 'Total Pengajuan',
                        data: monthData.map(d => d.total),
                        borderColor: '#4f46e5',
                        backgroundColor: (ctx) => {
                            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
                            gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');
                            return gradient;
                        },
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#4f46e5',
                        pointHoverBackgroundColor: '#4f46e5',
                        pointHoverBorderColor: '#ffffff',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13 },
                            bodyFont: { size: 13 },
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: '#e2e8f0' },
                            ticks: { precision: 0 }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 2. Doughnut Chart (Status)
        const ctxStatus = document.getElementById('statusChart');
        if (ctxStatus && statusData.length > 0) {
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: statusData.map(d => d.status),
                    datasets: [{
                        data: statusData.map(d => d.total),
                        backgroundColor: ['#10b981', '#f59e0b', '#0ea5e9', '#ef4444', '#cbd5e1'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 20, font: { size: 11 } }
                        }
                    }
                }
            });
        } else if (ctxStatus) {
            // Show empty state if no data
            ctxStatus.parentNode.innerHTML = '<div class="d-flex h-100 align-items-center justify-content-center text-muted small">Belum ada data status.</div>';
        }

        // 3. Bar Chart (Department)
        const ctxDept = document.getElementById('deptChart');
        if (ctxDept && deptData.length > 0) {
            new Chart(ctxDept, {
                type: 'bar',
                data: {
                    labels: deptData.map(d => d.name),
                    datasets: [{
                        label: 'Pengajuan',
                        data: deptData.map(d => d.total),
                        backgroundColor: '#6366f1',
                        borderRadius: 4,
                        barThickness: 'flex',
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: '#e2e8f0' },
                            ticks: { precision: 0 }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        } else if (ctxDept) {
            ctxDept.parentNode.innerHTML = '<div class="d-flex h-100 align-items-center justify-content-center text-muted small">Belum ada data departemen.</div>';
        }

        // ==========================================
        // HELPER FUNCTIONS FOR SEPARATE CHARTS
        // ==========================================
        
        // Prepare arrays: fill missing months with 0
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        
        function getDataForType(typeKey) {
            const data = new Array(12).fill(0);
            trendByType.filter(d => d.jenis_pengajuan === typeKey).forEach(d => {
                data[(d.bulan || 1) - 1] = d.total;
            });
            return data;
        }

        // Helper for Department Data
        function getDataForDept(deptName) {
            const data = new Array(12).fill(0);
            trendByDept.filter(d => d.dept_name === deptName).forEach(d => {
                data[(d.bulan || 1) - 1] = d.total;
            });
            return data;
        }

        function createLineChart(ctxId, label, data, color) {
            const ctx = document.getElementById(ctxId);
            if (!ctx) return;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: label,
                        data: data,
                        borderColor: color,
                        backgroundColor: color + '20', // Low opacity
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { borderDash: [5,5] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 4. CHART: ADJUSTMENT
        createLineChart('adjustChart', 'Adjustment', getDataForType('Adjust'), '#0ea5e9'); // Sky Blue

        // 5. CHART: MEMO
        createLineChart('memoChart', 'Internal Memo', getDataForType('Internal_Memo'), '#f59e0b'); // Amber

        // 6. CHART: BUNDEL
        createLineChart('bundelChart', 'Bundel', getDataForType('Bundel'), '#ef4444'); // Red
        
        // 7. CHART: CANCEL (New)
        createLineChart('cancelChart', 'Cancel', getDataForType('Cancel'), '#64748b'); // Slate / Gray

        // 8. CHART: MUTASI (Combined)
        const ctxMutasi = document.getElementById('mutasiChart');
        if (ctxMutasi) {
            const dataBillet = getDataForType('Mutasi_Billet');
            const dataProduk = getDataForType('Mutasi_Produk');
            
            new Chart(ctxMutasi, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Mutasi Billet',
                            data: dataBillet,
                            backgroundColor: '#6366f1', // Indigo
                            borderRadius: 4
                        },
                        {
                            label: 'Mutasi Produk',
                            data: dataProduk,
                            backgroundColor: '#a855f7', // Purple
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                         legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                    },
                    scales: {
                        y: { beginAtZero: true, stacked: true, ticks: { stepSize: 1 }, grid: { borderDash: [5,5] } },
                        x: { grid: { display: false }, stacked: true }
                    }
                }
            });
        }

        
        
        // ===============================================
        // NEW: HORIZONTAL BAR CHARTS FOR TOP DEPARTMENTS
        // ===============================================
        
        // Custom Plugin to draw numbers on bars (Angka Jelas)
        const dataLabelPlugin = {
            id: 'customDataLabel',
            afterDatasetsDraw(chart, args, options) {
                const { ctx } = chart;
                chart.data.datasets.forEach((dataset, i) => {
                    chart.getDatasetMeta(i).data.forEach((datapoint, index) => {
                        const value = dataset.data[index];
                        const x = datapoint.x;
                        const y = datapoint.y;
                        
                        ctx.save();
                        ctx.font = 'bold 12px sans-serif';
                        ctx.fillStyle = '#1e293b';
                        ctx.textAlign = 'center'; 
                        ctx.textBaseline = 'bottom';
                        ctx.fillText(value, x, y - 5); // Draw number 5px above the bar
                        ctx.restore();
                    });
                });
            }
        };
        
        // Helper to create gradient
        function createGradient(ctx, colorHex) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 250);
            gradient.addColorStop(0, colorHex);         // Top: Solid Color
            gradient.addColorStop(1, colorHex + '40');  // Bottom: 25% Opacity
            return gradient;
        }

        function createVerticalChart(ctxId, data, color) {
            const canvas = document.getElementById(ctxId);
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            
            // Check if empty
            if (!data || data.length === 0) {
                canvas.parentNode.innerHTML = '<div class="d-flex h-100 align-items-center justify-content-center text-muted small">Tidak ada data.</div>';
                return;
            }

            // Create Gradient
            const gradientFill = createGradient(ctx, color);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.name),
                    datasets: [{
                        label: 'Total',
                        data: data.map(d => d.total),
                        backgroundColor: gradientFill,
                        borderColor: color,
                        borderWidth: 1,
                        borderRadius: 6,       // More rounded
                        borderSkipped: false, // Rounded on all sides (optional, or 'bottom')
                        hoverBackgroundColor: color, // Solid on hover
                        maxBarThickness: 35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { 
                            enabled: true,
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1e293b',
                            bodyColor: '#1e293b',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false
                        },
                        customDataLabel: {} 
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            border: { display: false },
                            grid: { 
                                borderDash: [4, 4], 
                                color: '#f1f5f9',
                                drawBorder: false 
                            },
                            ticks: { display: false } 
                        },
                        x: { 
                            grid: { display: false },
                            ticks: { 
                                font: { size: 10, family: "'Outfit', sans-serif" },
                                autoSkip: false,
                                maxRotation: 50,
                                minRotation: 50,
                                color: '#64748b'
                            }
                        }
                    },
                    layout: {
                        padding: { top: 25, bottom: 5 } 
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    }
                },
                plugins: [dataLabelPlugin]
            });
        }

        // 9. RENDER DEPT CHARTS (Vertical + Gradient)
        createVerticalChart('deptCancelChart', deptCancel, '#64748b');   // Slate
        createVerticalChart('deptAdjustChart', deptAdjust, '#0ea5e9');   // Sky
        createVerticalChart('deptMutasiChart', deptMutasi, '#6366f1');   // Indigo
        createVerticalChart('deptBundelChart', deptBundel, '#ef4444');   // Red
        createVerticalChart('deptMemoChart',   deptMemo,   '#f59e0b');   // Amber
    });
</script>
@endpush
