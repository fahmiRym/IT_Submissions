@extends('layouts.app')

@section('title', 'Dibagikan ke Saya')
@section('page-title', '📥 Dibagikan ke Saya')

@push('styles')
<style>
    body, .card, .table, button, input, select { font-family: 'Outfit', sans-serif !important; }
    .info-banner {
        background: linear-gradient(135deg, #ecfeff, #dbeafe);
        border: 1px solid #67e8f9;
        border-radius: 16px;
        padding: 14px 18px;
        color: #155e75;
        font-size: 0.85rem;
        display: flex; align-items: start; gap: 12px;
        margin-bottom: 1.25rem;
    }
    .info-banner i { font-size: 1.15rem; color: #0891b2; flex-shrink: 0; margin-top: 2px; }
    .table-card { border:none; border-radius:20px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.05); }
    .card-header-section { padding:1.25rem 1.5rem; background:white; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; }
    .section-title { font-size:1.05rem; font-weight:800; color:#1e293b; margin:0; }
    .section-sub   { font-size:0.78rem; color:#94a3b8; margin:2px 0 0; font-weight:500; }
    .table thead th { background:#f8fafc; color:#64748b; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.07em; padding:0.85rem 1.1rem; border-bottom:2px solid #f1f5f9; }
    .table tbody td { padding:0.85rem 1.1rem; vertical-align:middle; color:#334155; font-weight:500; }
    .table tbody tr { border-bottom:1px solid #f8fafc; transition:background 0.15s; }
    .table tbody tr:hover { background:#fafbff; }
    .jenis-badge {
        display:inline-flex; align-items:center; gap:5px;
        font-size:0.7rem; font-weight:800; padding:3px 9px; border-radius:8px;
        letter-spacing:0.04em;
    }
    .no-reg-pill { background:#eef2ff; color:#3730a3; font-family:'JetBrains Mono',monospace; padding:3px 9px; border-radius:7px; font-size:0.78rem; font-weight:800; }
    .meta-line { font-size:0.7rem; color:#94a3b8; margin-top:2px; }
    .shared-note { font-size:0.72rem; color:#7c3aed; background:#f5f3ff; border:1px dashed #c4b5fd; padding:3px 8px; border-radius:7px; display:inline-block; }
    .btn-mini-act { background:#eef2ff; color:#4338ca; border:none; padding:6px 12px; border-radius:9px; font-size:0.75rem; font-weight:700; transition:all 0.2s; display:inline-flex; align-items:center; gap:5px; text-decoration:none; }
    .btn-mini-act:hover { background:#e0e7ff; transform:scale(1.04); color:#3730a3; }
    .empty-state { padding:3rem 1rem; text-align:center; color:#94a3b8; }
</style>
@endpush

@section('content')

<div class="info-banner">
    <i class="bi bi-inbox-fill"></i>
    <div>
        <b>Inbox berbagi</b> — daftar pengajuan yang dibagikan ke Anda secara manual oleh pemohon/superadmin.
        Akses ini ada di luar baseline jenis pengajuan Anda. Total <b>{{ $arsips->total() }}</b> arsip dibagikan.
    </div>
</div>

@if(session('success'))
    <div class="alert border-0 rounded-3 d-flex align-items-start gap-2 small mb-3" style="background:#dcfce7;color:#15803d;">
        <i class="bi bi-check-circle-fill mt-1"></i><div>{{ session('success') }}</div>
    </div>
@endif

<div class="card table-card">
    <div class="card-header-section">
        <div>
            <p class="section-title">Pengajuan Dibagikan ke Saya</p>
            <p class="section-sub">Diurutkan dari yang terbaru dibagikan</p>
        </div>
        <form method="GET" class="d-flex gap-2 align-items-center">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari no_reg / no_doc / ket..." class="form-control" style="min-width:240px;">
            <button class="btn btn-light border" type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="60">#</th>
                        <th>No Registrasi</th>
                        <th>Jenis</th>
                        <th>Pemohon / Dept</th>
                        <th>Dibagikan</th>
                        <th class="text-end pe-4" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($arsips as $a)
                    @php
                        $jc = match($a->jenis_pengajuan) {
                            'Cancel'        => ['bg'=>'#fef2f2','text'=>'#991b1b','icon'=>'bi-trash3-fill'],
                            'Adjust'        => ['bg'=>'#ecfeff','text'=>'#155e75','icon'=>'bi-sliders2-vertical'],
                            'Mutasi_Billet' => ['bg'=>'#eef2ff','text'=>'#3730a3','icon'=>'bi-arrow-repeat'],
                            'Mutasi_Produk' => ['bg'=>'#ecfdf5','text'=>'#065f46','icon'=>'bi-box-fill'],
                            'Internal_Memo' => ['bg'=>'#fffbeb','text'=>'#92400e','icon'=>'bi-file-earmark-richtext-fill'],
                            'Bundel'        => ['bg'=>'#fdf2f8','text'=>'#9d174d','icon'=>'bi-collection-fill'],
                            default         => ['bg'=>'#f1f5f9','text'=>'#475569','icon'=>'bi-file'],
                        };
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold text-secondary">{{ $loop->iteration + ($arsips->currentPage()-1)*$arsips->perPage() }}</td>
                        <td>
                            <span class="no-reg-pill">{{ $a->no_registrasi }}</span>
                            <div class="meta-line">{{ \Carbon\Carbon::parse($a->tgl_pengajuan)->format('d M Y') }}</div>
                        </td>
                        <td>
                            <span class="jenis-badge" style="background:{{ $jc['bg'] }};color:{{ $jc['text'] }};">
                                <i class="{{ $jc['icon'] }}"></i> {{ str_replace('_',' ', $a->jenis_pengajuan) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold" style="font-size:0.85rem;">{{ optional($a->admin)->name ?? '—' }}</div>
                            <div class="meta-line">
                                @if($a->department) <i class="bi bi-building"></i> {{ $a->department->name }} @endif
                                @if($a->unit) · <i class="bi bi-grid"></i> {{ $a->unit->name }} @endif
                            </div>
                        </td>
                        <td>
                            @if($a->share_target_type === 'role')
                                <span class="shared-note" style="background:#ecfeff;border-color:#67e8f9;color:#155e75;">
                                    <i class="bi bi-people-fill me-1"></i>Via role
                                </span>
                            @endif
                            @if($a->share_note)
                                <span class="shared-note"><i class="bi bi-chat-left-quote me-1"></i>{{ $a->share_note }}</span>
                            @endif
                            <div class="meta-line">
                                <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($a->share_created_at ?? $a->created_at)->diffForHumans() }}
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.arsip.index', ['q' => $a->no_registrasi]) }}" class="btn-mini-act">
                                <i class="bi bi-box-arrow-up-right"></i> Buka
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-inbox display-1 opacity-25"></i>
                            <h6 class="fw-bold mt-3">Inbox kosong</h6>
                            <p class="small">Belum ada pengajuan yang dibagikan ke Anda.</p>
                        </div>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($arsips->hasPages())
        <div class="card-footer bg-white border-top px-4 py-3">{{ $arsips->links() }}</div>
    @endif
</div>

@endsection
