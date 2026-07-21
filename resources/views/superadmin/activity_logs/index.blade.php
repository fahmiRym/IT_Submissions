@extends('layouts.app')

@section('title', 'Log Audit Aktivitas')
@section('page-title', 'Detailed Audit Logs')

@section('content')

    {{-- ── STATS BAR ─────────────────────────────────────────────── --}}
    @php
        $totalLogs = $logs->total();
        $totalCreated = method_exists($logs, 'where') ? null : null;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 p-3 shadow-sm h-100"
                 style="background:linear-gradient(135deg,#6366f1,#4f46e5); color:white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.1em;">Total Logs</div>
                        <h3 class="fw-extrabold mb-0 mt-1">{{ number_format($totalLogs) }}</h3>
                    </div>
                    <i class="bi bi-journal-text" style="font-size:2rem; opacity:0.4;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 p-3 shadow-sm h-100"
                 style="background:linear-gradient(135deg,#10b981,#059669); color:white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.1em;">Total Editor</div>
                        <h3 class="fw-extrabold mb-0 mt-1">{{ count($users) }}</h3>
                    </div>
                    <i class="bi bi-people-fill" style="font-size:2rem; opacity:0.4;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 p-3 shadow-sm h-100"
                 style="background:linear-gradient(135deg,#f59e0b,#d97706); color:white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.1em;">Showing</div>
                        <h3 class="fw-extrabold mb-0 mt-1">{{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-eye-fill" style="font-size:2rem; opacity:0.4;"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 p-3 shadow-sm h-100"
                 style="background:linear-gradient(135deg,#ec4899,#db2777); color:white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.1em;">Page</div>
                        <h3 class="fw-extrabold mb-0 mt-1">{{ $logs->currentPage() }}/{{ $logs->lastPage() }}</h3>
                    </div>
                    <i class="bi bi-bookmark-fill" style="font-size:2rem; opacity:0.4;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-funnel-fill text-primary me-2"></i>Filter Logs</h6>
            <form action="{{ route('superadmin.activity-logs.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-2" style="letter-spacing:0.05em;">
                        <i class="bi bi-search me-1 text-primary"></i>Cari Dokumen
                    </label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute" style="left:14px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="form-control form-control-lg border-0 shadow-sm ps-5 rounded-3"
                               placeholder="No. Registrasi atau No. Transaksi..."
                               style="background:#f8fafc; font-size:0.92rem;">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-2" style="letter-spacing:0.05em;">
                        <i class="bi bi-person-circle me-1 text-success"></i>Filter Editor
                    </label>
                    <select name="user_id" class="form-select form-select-lg border-0 shadow-sm rounded-3"
                            style="background:#f8fafc; font-size:0.92rem;">
                        <option value="">-- Semua Editor --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-lg flex-grow-1 fw-bold shadow-sm rounded-3 text-white"
                            style="background:linear-gradient(135deg,#4f46e5,#6366f1); border:none;">
                        <i class="bi bi-funnel-fill me-1"></i>FILTER
                    </button>
                    <a href="{{ route('superadmin.activity-logs.index') }}"
                        class="btn btn-lg btn-light border shadow-sm px-3 rounded-3" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-bold text-primary-dark"><i class="bi bi-shield-lock-fill text-success me-2"></i>Riwayat Log
                Aktivitas</h6>
            @include('partials._per_page_select', ['id' => 'perPageLogs', 'default' => 20])
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-bold" style="font-size: 0.7rem;">Total:
                {{ $logs->total() }} Aktivitas</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr style="font-size: 0.75rem;">
                        <th class="ps-4 text-muted fw-bold">WAKTU & USER</th>
                        <th class="text-muted fw-bold">DOKUMEN</th>
                        <th class="text-muted fw-bold">AKSI</th>
                        <th class="text-muted fw-bold">DETAIL PERUBAHAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-bottom">
                            <td class="ps-4 py-3" style="min-width: 200px;">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 fw-bold me-2"
                                        style="font-size: 0.7rem;">
                                        {{ $log->created_at->format('H:i') }} WIB
                                    </div>
                                    <span class="text-muted small fw-semibold">{{ $log->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="d-flex align-items-center mt-2">
                                    <div class="bg-success rounded-circle me-2 d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                        style="width: 24px; height: 24px; font-size: 0.7rem;">
                                        {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $log->user->name ?? 'System' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-dark" style="font-size: 0.85rem;">
                                    {{ $log->arsip->no_registrasi ?? 'Deleted Doc' }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $log->arsip->no_transaksi ?? '-' }}</div>
                                <div class="mt-1">
                                    <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.65rem;">
                                        {{ $log->arsip->department->code ?? '-' }} / {{ $log->arsip->unit->code ?? '-' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match ($log->action) {
                                        'created' => 'bg-success',
                                        'updated' => 'bg-info',
                                        'deleted' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} text-white rounded-pill px-3 py-1 shadow-sm"
                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    {{ strtoupper($log->action) }}
                                </span>
                            </td>
                            <td class="pe-4" style="max-width: 400px;">
                                @if($log->action == 'updated')
                                    <div class="p-2 rounded bg-light border border-opacity-10" style="font-size: 0.75rem;">
                                        @foreach($log->new_values as $key => $newValue)
                                            @php 
                                                                            $oldValue = $log->old_values[$key] ?? 'null';
                                                // Format values if they are arrays or objects
                                                if (is_array($oldValue))
                                                    $oldValue = json_encode($oldValue);
                                                if (is_array($newValue))
                                                    $newValue = json_encode($newValue);
                                            @endphp
                                            <div class="mb-2">
                                                    <span class="fw-bold text-primary">{{ strtoupper(str_replace('_', ' ', $key)) }}:</span><br>
                                                    <span class="text-danger text-decoration-line-through opacity-75">{{ $oldValue ?: '(empty)' }}</span>
                                                        <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                                    <span class="text-success fw-bold">{{ $newValue ?: '(empty)' }}</span>
                                                    </div>
                                        @endforeach
                                        </div>
                                @elseif($log->action == 'created')
                                    <span class="text-muted italic small">Data baru ditambahkan ke sistem.</span>
                                @elseif($log->action == 'deleted')
                                    <span class="text-danger small fw-bold">Data telah dihapus permanent.</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-slash display-4 opacity-25"></i>
                                <p class="mt-2 small">Belum ada riwayat aktivitas mendalam yang tercatat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white border-top py-3 px-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <small class="text-muted">
                        Showing <b>{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}</b>
                        of <b>{{ $logs->total() }}</b> entries
                    </small>
                    {{ $logs->links() }}
                </div>
            </div>
        @endif
    </div>

    <style>
        .text-primary-dark { color: #0f172a; }
        .table-responsive { scrollbar-width: thin; }
        /* Pagination polish (Bootstrap 5) */
        .pagination { margin: 0; gap: 2px; }
        .pagination .page-link {
            border: 1px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            border-radius: 8px !important;
            padding: 0.4rem 0.7rem;
            font-size: 0.85rem;
            min-width: 36px;
            text-align: center;
        }
        .pagination .page-link:hover {
            background: #eef2ff;
            color: #4f46e5;
            border-color: rgba(99, 102, 241, 0.3);
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border-color: #4f46e5;
            color: #fff;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        }
        .pagination .page-item.disabled .page-link {
            background: #f8fafc;
            color: #cbd5e1;
            border-color: #e2e8f0;
        }
    </style>
@endsection
