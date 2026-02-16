@extends('layouts.app')

@section('title', 'IT Submission Report')
@section('page-title', '📄 IT Submission Report')

@push('styles')
<style>
    /* Modern Color Palette & Gradients */
    .bg-soft-light { background-color: #f8fafc; }
    .text-soft-dark { color: #334155 !important; } /* Slate 700 */
    .text-soft-muted { color: #64748b !important; } /* Slate 500 */
    
    .gradient-filter { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); }
    .gradient-amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }
    .gradient-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
    
    /* Card & Interactivity */
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .card-modern:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
    
    /* Table Styling */
    .table-modern thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
    }
    .table-modern tbody tr { transition: background-color 0.2s ease; border-bottom: 1px solid #f1f5f9; }
    .table-modern tbody tr:hover { background-color: #f8fafc; }
    .table-modern td { 
        vertical-align: middle; 
        padding: 0.75rem 1rem; /* Reduced padding for neater look */
        color: #334155; 
        font-size: 0.9rem; 
    }
    .table-modern td:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
    .table-modern td:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }

    /* Unified Scan Thumbnail / Badge */
    .scan-box {
        width: 40px; 
        height: 40px; 
        border-radius: 8px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        margin: 0 auto;
        border: 1px solid #e2e8f0;
        background-color: white;
        transition: all 0.2s ease;
        cursor: pointer;
        overflow: hidden;
        position: relative;
    }
    .scan-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }
    .scan-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .scan-box.is-pdf {
        background-color: #fff1f2; /* Rose 50 */
        border-color: #fecaca;
        color: #e11d48;
    }
    .scan-box.is-pdf:hover {
        background-color: #ffe4e6;
        border-color: #f43f5e;
    }
    .scan-box.is-empty {
        background-color: #f8fafc;
        border-color: #e2e8f0;
        border-style: dashed;
        color: #cbd5e1;
        cursor: default;
    }
    .scan-box.is-empty:hover {
        transform: none;
        box-shadow: none;
        border-color: #cbd5e1;
    }
    
    /* Custom Input Group */
    .input-group-text { background-color: #f8fafc; color: #64748b; border-color: #e2e8f0; }
    .form-control, .form-select { border-color: #e2e8f0; color: #334155; font-size: 0.9rem; }
    .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .form-label { color: #64748b; font-size: 0.75rem; letter-spacing: 0.03em; margin-bottom: 0.4rem; }

    /* Pagination */
    .pagination .page-link { color: #64748b; border: none; margin: 0 2px; border-radius: 6px; }
    .pagination .page-item.active .page-link { background-color: #0f172a; color: white; }
</style>
@endpush

@section('content')

{{-- FILTER SECTION --}}
<div class="card card-modern mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <h6 class="fw-bold text-soft-dark m-0 d-flex align-items-center">
            <i class="bi bi-sliders2 me-2 text-primary"></i> Filter Data
        </h6>
    </div>
    <div class="card-body px-4 pb-4 pt-3">
        <form action="{{ route('superadmin.laporan.index') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label fw-bold">DARI TANGGAL</label>
                <div class="input-group input-group-sm shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-calendar"></i></span>
                    <input type="date" name="from" class="form-control border-start-0 ps-0" value="{{ request('from') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">SAMPAI TANGGAL</label>
                <div class="input-group input-group-sm shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-calendar-check"></i></span>
                    <input type="date" name="to" class="form-control border-start-0 ps-0" value="{{ request('to') }}">
                </div>
            </div>
             <div class="col-md-2">
                <label class="form-label fw-bold">PENGAJU (STAFF)</label>
                 <div class="input-group input-group-sm shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-person-badge"></i></span>
                    <select name="admin_id" class="form-select border-start-0 ps-0">
                        <option value="">Semua Pengaju</option>
                        @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
             <div class="col-md-2">
                <label class="form-label fw-bold">PEMOHON</label>
                <div class="input-group input-group-sm shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-person-circle"></i></span>
                    <input type="text" name="pemohon" class="form-control border-start-0 ps-0" placeholder="Nama..." value="{{ request('pemohon') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">DEPT. PEMOHON</label>
                 <div class="input-group input-group-sm shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-building"></i></span>
                    <select name="department_id" class="form-select border-start-0 ps-0">
                        <option value="">Semua Dept</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">OPSI & ACTION</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill shadow-sm rounded-3">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                    <button type="submit" formaction="{{ route('superadmin.laporan.pdf') }}" formtarget="_blank" class="btn btn-danger btn-sm flex-fill shadow-sm rounded-3">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </button>
                    <a href="{{ route('superadmin.laporan.index') }}" class="btn btn-light btn-sm border shadow-sm rounded-3" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- STATISTICS ROW --}}
<div class="row g-4 mb-4">
    <!-- Top Admins -->
    <div class="col-md-6">
        <div class="card card-modern h-100 overflow-hidden">
            <div class="card-header gradient-amber border-0 pt-3 px-4 pb-3">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3 shadow-sm">
                         <i class="bi bi-trophy-fill fs-5 text-white"></i>
                    </div>
                    <div>
                        <h6 class="m-0 fw-bold text-white">Top 5 Staff Pengaju</h6>
                        <small class="text-white text-opacity-75">Statistik Pengaju Terbanyak</small>
                    </div>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($byUser as $index => $stat)
                <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-0" style="{{ $index % 2 == 0 ? 'background: #fafafa;' : '' }}">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-warning text-white me-3 rounded-circle shadow-sm" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">{{ $index + 1 }}</span>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-soft-dark">{{ $stat->admin->name ?? 'Unknown' }}</span>
                        </div>
                    </div>
                    <span class="badge bg-amber-soft text-warning fw-bold px-3 py-2 rounded-pill bg-opacity-10 border border-warning" style="background: rgba(245, 158, 11, 0.1);">
                        {{ $stat->total }} Pengajuan
                    </span>
                </li>
                @empty
                <li class="list-group-item text-center text-muted py-4">Belum ada data.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Top Departments -->
    <div class="col-md-6">
        <div class="card card-modern h-100 overflow-hidden">
            <div class="card-header gradient-emerald border-0 pt-3 px-4 pb-3">
                 <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3 shadow-sm">
                         <i class="bi bi-buildings-fill fs-5 text-white"></i>
                    </div>
                    <div>
                        <h6 class="m-0 fw-bold text-white">Top 5 Departemen</h6>
                         <small class="text-white text-opacity-75">Statistik Asal Pengajuan</small>
                    </div>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($byDepartment as $index => $stat)
                 <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-0" style="{{ $index % 2 == 0 ? 'background: #fafafa;' : '' }}">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success text-white me-3 rounded-circle shadow-sm" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">{{ $index + 1 }}</span>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-soft-dark">{{ $stat->department->name ?? 'Unknown' }}</span>
                        </div>
                    </div>
                    <span class="badge bg-emerald-soft text-success fw-bold px-3 py-2 rounded-pill bg-opacity-10 border border-success" style="background: rgba(16, 185, 129, 0.1);">
                        {{ $stat->total }} Pengajuan
                    </span>
                </li>
                @empty
                <li class="list-group-item text-center text-muted py-4">Belum ada data.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- DATA TABLE --}}
<div class="card card-modern overflow-hidden">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold text-soft-dark mb-0 fs-5">
                <i class="bi bi-card-checklist me-2 text-primary"></i>Data Arsip
            </h6>
            <p class="text-soft-muted small mb-0 mt-1">Menampilkan {{ $arsips->count() }} dokumen terbaru</p>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-4 text-center" style="width: 50px;">No</th>
                        <th class="px-4">Detail Registrasi</th>
                        <th class="px-4">Pengaju (Staff)</th>
                        <th class="px-4">Pemohon (User)</th>
                        <th class="px-4 text-center">Bukti</th>
                        <th class="px-4 text-center">Status Fisik</th>
                        <th class="px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arsips as $arsip)
                    <tr>
                        <td class="px-4 text-center fw-bold text-secondary">{{ $loop->iteration }}</td>
                        
                        <!-- DETAIL REGISTRASI -->
                        <td class="px-4">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-soft-dark mb-1" style="font-size: 0.95rem;">
                                    {{ $arsip->no_registrasi ?? '-' }}
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                     <span class="badge bg-light text-secondary border fw-normal px-2 py-1 rounded-2" style="font-size: 0.7rem;">
                                        <i class="bi bi-tag-fill me-1 opacity-50"></i>{{ strtoupper(str_replace('_', ' ', $arsip->jenis_pengajuan)) }}
                                    </span>
                                    <span class="text-soft-muted small" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock me-1"></i>{{ $arsip->tgl_pengajuan ? $arsip->tgl_pengajuan->format('d M Y') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <!-- PENGAJU (STAFF) -->
                         <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-soft-dark" style="font-size: 0.9rem;">{{ $arsip->admin->name ?? 'System' }}</span>
                                    <span class="text-soft-muted small" style="font-size: 0.75rem;">{{ $arsip->admin->department->name ?? '-' }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- PEMOHON -->
                        <td class="px-4">
                             <div class="d-flex flex-column">
                                <span class="fw-semibold text-soft-dark">{{ $arsip->pemohon ?? '-' }}</span>
                                <div class="text-soft-muted small mt-1">
                                    <i class="bi bi-building me-1 opacity-50"></i>{{ $arsip->department->name ?? '-' }}
                                </div>
                                <div class="text-soft-muted small fst-italic">
                                    Manager: {{ $arsip->manager->name ?? '-' }}
                                </div>
                            </div>
                        </td>

                        <!-- BUKTI SCAN -->
                        <td class="px-4 text-center">
                            @if($arsip->bukti_scan)
                                @php $ext = pathinfo($arsip->bukti_scan, PATHINFO_EXTENSION); @endphp
                                @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp']))
                                    <div class="scan-box shadow-sm" 
                                         onclick="showBukti('{{ route('preview.file', $arsip->bukti_scan) }}')"
                                         title="Lihat Gambar">
                                        <img src="{{ route('preview.file', $arsip->bukti_scan) }}" alt="Bukti">
                                    </div>
                                @else
                                    <div class="scan-box is-pdf shadow-sm"
                                         onclick="showBukti('{{ route('preview.file', $arsip->bukti_scan) }}')"
                                         title="Lihat Dokumen PDF">
                                        <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                                    </div>
                                @endif
                            @else
                                <div class="scan-box is-empty" title="Tidak ada lampiran">
                                    <i class="bi bi-dash-lg text-muted"></i>
                                </div>
                            @endif
                        </td>

                        <!-- STATUS FISIK -->
                         <td class="px-4 text-center">
                            @if($arsip->arsip == 'Done')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 border border-success border-opacity-25">
                                    <i class="bi bi-check-circle-fill me-1"></i>Arsip OK
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 border border-secondary border-opacity-25">
                                    <i class="bi bi-hourglass-split me-1"></i>Pending
                                </span>
                            @endif
                        </td>

                        <!-- STATUS TRANSAKSI -->
                        <td class="px-4 text-center">
                            @php
                                $statusClass = 'bg-secondary bg-opacity-10 text-secondary border-secondary';
                                $icon = 'bi-dash-circle';
                                if($arsip->status == 'Done') {
                                    $statusClass = 'bg-success bg-opacity-10 text-success border-success';
                                    $icon = 'bi-check-all';
                                } elseif($arsip->status == 'Process') {
                                    $statusClass = 'bg-warning bg-opacity-10 text-warning border-warning';
                                    $icon = 'bi-arrow-repeat';
                                } elseif($arsip->status == 'Reject') {
                                    $statusClass = 'bg-danger bg-opacity-10 text-danger border-danger';
                                    $icon = 'bi-x-circle';
                                }
                            @endphp
                            <span class="badge {{ $statusClass }} rounded-pill px-3 py-2 border border-opacity-25 shadow-sm">
                                <i class="bi {{ $icon }} me-1"></i>{{ $arsip->status ?? 'Pending' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-light rounded-circle p-4 mb-3">
                                    <i class="bi bi-search fs-1 text-muted opacity-50"></i>
                                </div>
                                <h6 class="text-secondary fw-bold">Data Tidak Ditemukan</h6>
                                <span class="text-muted small">Coba ubah filter pencarian Anda.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($arsips, 'links'))
    <div class="card-footer bg-white border-0 py-4 px-4">
        {{ $arsips->withQueryString()->links() }}
    </div>
    @endif
</div>

@include('superadmin.arsip._view')

@endsection
