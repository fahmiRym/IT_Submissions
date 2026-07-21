@extends('layouts.app')

@section('title', 'Master Harga')
@section('page-title', '💰 Master Harga Barang')

@push('styles')
<style>
    body, .card, .table, button, input, select { font-family: 'Outfit', sans-serif !important; }
    .stat-box { background:white; border:1px solid #f1f5f9; border-radius:14px; padding:1rem 1.1rem; display:flex; align-items:center; gap:12px; transition:all .25s; }
    .stat-box:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(15,23,42,0.06); }
    .stat-icon { width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
    .stat-val  { font-size:1.4rem; font-weight:800; line-height:1; color:#0f172a; }
    .stat-lbl  { font-size:0.65rem; letter-spacing:0.1em; font-weight:800; color:#94a3b8; text-transform:uppercase; margin-top:3px; }

    .table-card { border:none; border-radius:20px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.05); }
    .card-header-section { padding:1.5rem 1.75rem; background:white; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; }
    .section-title { font-size:1.05rem; font-weight:800; color:#1e293b; margin:0; }
    .section-sub   { font-size:0.78rem; color:#94a3b8; margin:2px 0 0; font-weight:500; }

    .table thead th { background:#f8fafc; color:#64748b; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.07em; padding:0.9rem 1.25rem; border-bottom:2px solid #f1f5f9; }
    .table tbody tr { border-bottom:1px solid #f8fafc; transition:background 0.15s; }
    .table tbody tr:hover { background:#fafbff; }
    .table tbody td { padding:0.75rem 1.25rem; vertical-align:middle; color:#334155; font-weight:500; }

    .kode-pill { background:#eef2ff; color:#3730a3; padding:3px 10px; border-radius:8px; font-family:'JetBrains Mono',monospace; font-size:0.78rem; font-weight:800; letter-spacing:0.5px; }
    .harga-input { border:1.5px solid #e2e8f0; border-radius:10px; padding:0.4rem 0.7rem; width:160px; text-align:right; font-family:'JetBrains Mono',monospace; font-weight:700; color:#065f46; background:#f0fdf4; }
    .harga-input:focus { outline:none; border-color:#10b981; background:white; box-shadow:0 0 0 3px rgba(16,185,129,0.12); }

    .btn-act { width:34px; height:34px; border-radius:10px; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem; transition:all 0.2s; }
    .btn-save-row { background:#dcfce7; color:#15803d; }
    .btn-save-row:hover { background:#bbf7d0; transform:scale(1.08); }
    .btn-delete-row { background:#fee2e2; color:#dc2626; }
    .btn-delete-row:hover { background:#fecaca; transform:scale(1.08); }

    .btn-add { background:linear-gradient(135deg,#10b981,#059669); color:white; border:none; border-radius:12px; padding:0.55rem 1.2rem; font-weight:700; font-size:0.85rem; display:inline-flex; align-items:center; gap:0.5rem; box-shadow:0 4px 12px rgba(16,185,129,0.3); transition:all 0.2s; }
    .btn-add:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(16,185,129,0.4); color:white; }

    .form-control, .form-select { border:1.5px solid #e2e8f0; border-radius:10px; padding:0.55rem 0.85rem; font-size:0.9rem; background:#f8fafc; }
    .form-control:focus, .form-select:focus { border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,0.12); background:white; }

    .modal-content { border:none; border-radius:18px; overflow:hidden; }
    .modal-header-custom { padding:1.1rem 1.4rem; background:linear-gradient(135deg,#10b981,#059669); color:white; }

    .search-bar { display:flex; gap:8px; align-items:center; }
    .search-bar input { border:1.5px solid #e2e8f0; border-radius:10px; padding:0.5rem 0.9rem; font-size:0.88rem; min-width:240px; }
    .search-bar input:focus { outline:none; border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,0.12); }

    .empty-state { padding:3rem 1rem; text-align:center; color:#94a3b8; }
</style>
@endpush

@section('content')

{{-- Mini stats --}}
<div class="row g-2 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-box">
            <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="bi bi-cash-stack"></i></div>
            <div><div class="stat-val text-success">{{ number_format($stats['total_master']) }}</div><div class="stat-lbl">Total Master</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-box">
            <div class="stat-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="bi bi-box-seam"></i></div>
            <div><div class="stat-val text-primary">{{ number_format($stats['used_kodes']) }}</div><div class="stat-lbl">Kode Dipakai</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-box">
            <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-exclamation-circle"></i></div>
            <div><div class="stat-val text-danger">{{ number_format($stats['missing']) }}</div><div class="stat-lbl">Belum Diberi Harga</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-box">
            <div class="stat-icon" style="background:#fef3c7;color:#92400e;"><i class="bi bi-coin"></i></div>
            <div><div class="stat-val" style="color:#92400e;font-size:1.1rem;">Rp {{ number_format($stats['total_value'], 0, ',', '.') }}</div><div class="stat-lbl">Akumulasi Master</div></div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert border-0 rounded-3 d-flex align-items-start gap-2 small mb-3" style="background:#dcfce7;color:#15803d;">
        <i class="bi bi-check-circle-fill mt-1"></i><div>{{ session('success') }}</div>
    </div>
@endif
@if($errors->any())
    <div class="alert border-0 rounded-3 d-flex align-items-start gap-2 small mb-3" style="background:#fee2e2;color:#991b1b;">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i><div>{{ $errors->first() }}</div>
    </div>
@endif

<div class="card table-card">
    <div class="card-header-section">
        <div>
            <p class="section-title">Daftar Harga Barang</p>
            <p class="section-sub">Master harga jadi acuan nilai (Rp) saat pengajuan ditutup. History tidak ikut bergeser.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" class="search-bar">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode / nama barang...">
                <button class="btn btn-light border" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalCreatePrice">
                <i class="bi bi-plus-circle-fill"></i> Tambah Harga
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="60">#</th>
                        <th>Kode Barang</th>
                        <th>Nama / Keterangan</th>
                        <th width="100">Satuan</th>
                        <th class="text-end" width="200">Harga / Unit</th>
                        <th class="text-center" width="160">Update Terakhir</th>
                        <th class="text-center pe-4" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($prices as $p)
                    <tr>
                        <form action="{{ route('admin.prices.update', $p->id) }}" method="POST">
                            @csrf @method('PUT')
                            <td class="ps-4 fw-bold text-secondary">{{ $loop->iteration + ($prices->currentPage()-1)*$prices->perPage() }}</td>
                            <td><span class="kode-pill">{{ $p->kode_barang }}</span></td>
                            <td>
                                <input type="text" name="nama_barang" value="{{ $p->nama_barang }}" class="form-control form-control-sm" placeholder="Nama barang (opsional)">
                                <input type="text" name="keterangan" value="{{ $p->keterangan }}" class="form-control form-control-sm mt-1" placeholder="Keterangan (opsional)" style="font-size:0.78rem;">
                            </td>
                            <td><input type="text" name="satuan" value="{{ $p->satuan }}" class="form-control form-control-sm text-center" placeholder="PCS/KG/M"></td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <span class="text-muted small">Rp</span>
                                    <input type="number" step="0.01" min="0" name="harga" value="{{ $p->harga }}" class="harga-input" required>
                                </div>
                            </td>
                            <td class="text-center small text-muted">
                                {{ $p->updated_at->format('d/m/Y H:i') }}<br>
                                <span style="font-size:0.7rem;">{{ optional($p->updatedBy)->name ?? '—' }}</span>
                            </td>
                            <td class="text-center pe-4">
                                <button type="submit" class="btn-act btn-save-row" title="Simpan"><i class="bi bi-check-lg"></i></button>
                        </form>
                                <form action="{{ route('admin.prices.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus harga untuk {{ $p->kode_barang }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-act btn-delete-row" title="Hapus"><i class="bi bi-trash3-fill"></i></button>
                                </form>
                            </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="bi bi-cash-stack display-1 opacity-25"></i>
                            <h6 class="fw-bold mt-3">Belum ada master harga</h6>
                            <p class="small">Klik "Tambah Harga" untuk mulai mengisi.</p>
                        </div>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($prices->hasPages())
        <div class="card-footer bg-white border-top px-4 py-3">{{ $prices->links() }}</div>
    @endif
</div>

{{-- MODAL CREATE --}}
<div class="modal fade" id="modalCreatePrice" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom d-flex align-items-center gap-2">
                <i class="bi bi-cash-coin fs-5"></i>
                <h5 class="modal-title fw-bold mb-0">Tambah Harga Master</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.prices.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Kode Barang</label>
                            <input type="text" name="kode_barang" class="form-control" placeholder="Contoh: KP45646" required style="font-family:monospace;font-weight:700;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Satuan</label>
                            <input type="text" name="satuan" class="form-control" placeholder="PCS / KG / M">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Nama Barang <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="text" name="nama_barang" class="form-control" placeholder="Deskripsi singkat">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Harga / Unit</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" min="0" name="harga" class="form-control" placeholder="0.00" required style="font-family:monospace;font-weight:700;">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan internal (opsional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-add">
                        <i class="bi bi-check-circle-fill"></i> Simpan Harga
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
