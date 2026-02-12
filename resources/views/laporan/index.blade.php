@extends('layouts.app')

@section('title', 'Laporan Pengajuan')
@section('page-title', '📄 Laporan Pengajuan')

@push('styles')
<style>
    .gradient-filter { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); }
    .gradient-amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }
    .gradient-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
    .card-hover:hover { transform: translateY(-5px); transition: all 0.3s ease; }
    .table-hover tbody tr:hover { background-color: #f1f5f9; }
</style>
@endpush

@section('content')

{{-- FILTER SECTION --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="fw-bold text-dark m-0"><i class="bi bi-funnel-fill me-2 text-primary"></i>Filter Data</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('superadmin.laporan.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted fw-bold">DARI TANGGAL</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar"></i></span>
                    <input type="date" name="from" class="form-control border-start-0 ps-0" value="{{ request('from') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted fw-bold">SAMPAI TANGGAL</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-check"></i></span>
                    <input type="date" name="to" class="form-control border-start-0 ps-0" value="{{ request('to') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted fw-bold">DEPARTEMEN</label>
                 <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-building"></i></span>
                    <select name="department_id" class="form-select border-start-0 ps-0">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-fill shadow-sm">
                    <i class="bi bi-search me-1"></i> Terapkan
                </button>
                <button type="submit" formaction="{{ route('superadmin.laporan.pdf') }}" formtarget="_blank" class="btn btn-danger flex-fill shadow-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                </button>
                <a href="{{ route('superadmin.laporan.index') }}" class="btn btn-light border flex-fill shadow-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- STATISTICS ROW --}}
<div class="row g-4 mb-4">
    <!-- Top Admins -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 card-hover" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header gradient-amber border-0 pt-3 px-4 pb-3">
                <div class="d-flex align-items-center mb-0">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                         <i class="bi bi-trophy-fill fs-5 text-white"></i>
                    </div>
                    <h6 class="m-0 fw-bold text-white">5 Pengaju Terbanyak</h6>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($byUser as $index => $stat)
                <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-bottom-0 border-top-0" style="{{ $index % 2 == 0 ? 'background: #fafafa;' : '' }}">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-warning text-dark me-3 rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">{{ $index + 1 }}</span>
                        <span class="fw-semibold text-dark">{{ $stat->admin->name ?? 'Unknown' }}</span>
                    </div>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill border border-warning">
                        {{ $stat->total }} Arsip
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
        <div class="card border-0 shadow-sm h-100 card-hover" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header gradient-emerald border-0 pt-3 px-4 pb-3">
                 <div class="d-flex align-items-center mb-0">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                         <i class="bi bi-buildings-fill fs-5 text-white"></i>
                    </div>
                    <h6 class="m-0 fw-bold text-white">Top 5 Departemen Aktif</h6>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($byDepartment as $index => $stat)
                 <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-bottom-0 border-top-0" style="{{ $index % 2 == 0 ? 'background: #fafafa;' : '' }}">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success text-white me-3 rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">{{ $index + 1 }}</span>
                        <span class="fw-semibold text-dark">{{ $stat->department->name ?? 'Unknown' }}</span>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill border border-success">
                        {{ $stat->total }} Arsip
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
<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold text-dark mb-0 fs-5"><i class="bi bi-table me-2 text-primary"></i>Data Arsip Terdata</h6>
            <p class="text-muted small mb-0 mt-1">Total: {{ $arsips->count() }} dokumen ditemukan</p>
        </div>
        <div>
             {{-- Optional: Export Button can go here --}}
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-nowrap" style="font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">
                        <th class="px-4 py-3 text-secondary">#</th>
                        <th class="px-4 py-3 text-secondary">No Registrasi</th>
                        <th class="px-4 py-3 text-secondary">Tanggal</th>
                        <th class="px-4 py-3 text-secondary">Pengirim</th>
                        <th class="px-4 py-3 text-secondary">Departemen</th>
                        <th class="px-4 py-3 text-secondary">Status Fisik</th>
                        <th class="px-4 py-3 text-secondary">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arsips as $arsip)
                    <tr>
                        <td class="px-4">{{ $loop->iteration }}</td>
                        <td class="px-4">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark">{{ $arsip->no_registrasi ?? '-' }}</span>
                                <span class="small text-muted" style="font-size: 0.75rem;">Type: {{ str_replace('_', ' ', $arsip->jenis_pengajuan) }}</span>
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-calendar-event me-2"></i>
                                {{ $arsip->tgl_pengajuan ? $arsip->tgl_pengajuan->format('d M Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-4">
                            <span class="fw-semibold text-dark">{{ $arsip->manager->name ?? '-' }}</span>
                            <div class="small text-muted">Unit: {{ $arsip->unit->name ?? '-' }}</div>
                        </td>
                        <td class="px-4">
                            <span class="badge bg-light text-dark border">{{ $arsip->department->name ?? '-' }}</span>
                        </td>
                         <td class="px-4">
                            @if($arsip->arsip == 'Done')
                                <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle me-1"></i>Arsip OK</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-dash-circle me-1"></i>Pending</span>
                            @endif
                        </td>
                        <td class="px-4">
                            @php
                                $statusColor = 'secondary';
                                if($arsip->status == 'Done') $statusColor = 'success';
                                elseif($arsip->status == 'Process') $statusColor = 'warning';
                                elseif($arsip->status == 'Reject') $statusColor = 'danger';
                            @endphp
                            <span class="badge bg-{{ $statusColor }} rounded-pill px-3">
                                {{ $arsip->status ?? 'Pending' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <i class="bi bi-inbox fs-1 text-muted opacity-25 mb-3"></i>
                                <span class="text-muted">Tidak ada data yang sesuai filter.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($arsips, 'links'))
    <div class="card-footer bg-white border-0 py-3">
        {{ $arsips->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
