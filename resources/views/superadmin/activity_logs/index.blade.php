@extends('layouts.app')

@section('title', 'Log Aktivitas Audit')
@section('page-title', 'Activity Audit Logs')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('superadmin.activity-logs.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label small fw-bold">CARI DOKUMEN</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-0" placeholder="No. Registrasi atau No. Transaksi...">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">FILTER USER (EDITOR)</label>
                <select name="user_id" class="form-select bg-light border-0 shadow-none">
                    <option value="">-- Semua Editor --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1 fw-bold shadow-sm"><i class="bi bi-funnel-fill me-2"></i>FILTER</button>
                <a href="{{ route('superadmin.activity-logs.index') }}" class="btn btn-light border shadow-sm px-3"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-shield-lock-fill text-success me-2"></i>Riwayat Perubahan Data</h6>
        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-bold" style="font-size: 0.7rem;">Total: {{ $logs->total() }} Aktivitas</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="bg-light">
                <tr style="font-size: 0.75rem;">
                    <th class="ps-4 text-muted fw-bold">WAKTU PERUBAHAN</th>
                    <th class="text-muted fw-bold">DIUBAH OLEH</th>
                    <th class="text-muted fw-bold">DOKUMEN TERKAIT</th>
                    <th class="text-muted fw-bold">DEPARTEMEN / UNIT</th>
                    <th class="text-end pe-4 text-muted fw-bold">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $log->updated_at->format('d M Y') }}</div>
                        <div class="text-muted small"><i class="bi bi-clock me-1"></i>{{ $log->updated_at->format('H:i') }} WIB</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 32px; height: 32px; font-weight: bold; font-size: 0.8rem;">
                                {{ substr($log->editor->name ?? 'S', 0, 1) }}
                            </div>
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $log->editor->name ?? 'System' }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-primary" style="font-size: 0.85rem;">{{ $log->no_registrasi }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">{{ $log->no_transaksi }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark" style="font-size: 0.8rem;">{{ $log->department->name ?? '-' }}</div>
                        <div class="text-muted" style="font-size: 0.7rem;">{{ $log->unit->name ?? '-' }}</div>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('superadmin.arsip.index', ['q' => $log->no_registrasi]) }}" class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-primary" style="font-size: 0.7rem;">
                            <i class="bi bi-eye-fill me-1"></i>Detail Arsip
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-shield-slash display-4 opacity-25"></i>
                        <p class="mt-2 small">Belum ada riwayat aktivitas yang tercatat.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
