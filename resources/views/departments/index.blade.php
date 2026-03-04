@extends('layouts.app')

@section('title', 'Master Data Departemen')
@section('page-title', '🏢 Master Departemen')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body, .card, .table, button, input, select { font-family: 'Outfit', sans-serif !important; }

    /* ── STAT CARDS ── */
    .stat-card {
        border: none; border-radius: 20px; overflow: hidden; position: relative;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.13); }
    .stat-card .mesh { position: absolute; inset: 0; z-index: 1;
        background: radial-gradient(circle at 10% 10%, rgba(255,255,255,0.18) 0%, transparent 60%),
                    radial-gradient(circle at 90% 90%, rgba(255,255,255,0.1) 0%, transparent 50%); }
    .stat-card .card-body { z-index: 2; position: relative; }
    .stat-icon { width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    .stat-value { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; line-height: 1; }
    .stat-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75; }

    /* ── TABLE CARD ── */
    .table-card { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.05); }
    .table-card .card-header-section {
        padding: 1.5rem 1.75rem; background: white;
        border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center;
    }
    .section-title { font-size: 1.05rem; font-weight: 800; color: #1e293b; margin: 0; }
    .section-sub { font-size: 0.78rem; color: #94a3b8; margin: 2px 0 0; font-weight: 500; }

    /* ── TABLE ── */
    .table thead th {
        background: #f8fafc; color: #64748b; font-size: 0.72rem;
        font-weight: 800; text-transform: uppercase; letter-spacing: 0.07em;
        padding: 1rem 1.25rem; border-bottom: 2px solid #f1f5f9; white-space: nowrap;
    }
    .table tbody tr { border-bottom: 1px solid #f8fafc; transition: background 0.15s; }
    .table tbody tr:hover { background: #fafbff; }
    .table tbody td { padding: 1rem 1.25rem; vertical-align: middle; color: #334155; font-weight: 500; }

    /* ── AVATAR / ICON ── */
    .item-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center;
        justify-content: center; font-size: 1.05rem; flex-shrink: 0; }

    /* ── ACTION BUTTONS ── */
    .btn-act {
        width: 36px; height: 36px; border-radius: 10px; border: none; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.85rem; transition: all 0.2s ease; position: relative;
    }
    .btn-act-toggle-off { background: #fef2f2; color: #dc2626; }
    .btn-act-toggle-off:hover { background: #fee2e2; transform: scale(1.1); box-shadow: 0 4px 8px rgba(220,38,38,0.2); }
    .btn-act-toggle-on  { background: #f0fdf4; color: #16a34a; }
    .btn-act-toggle-on:hover  { background: #dcfce7; transform: scale(1.1); box-shadow: 0 4px 8px rgba(22,163,74,0.2); }
    .btn-act-edit   { background: #fffbeb; color: #d97706; }
    .btn-act-edit:hover   { background: #fef9c3; transform: scale(1.1); box-shadow: 0 4px 8px rgba(217,119,6,0.2); }
    .btn-act-delete { background: #fef2f2; color: #dc2626; }
    .btn-act-delete:hover { background: #fee2e2; transform: scale(1.1); box-shadow: 0 4px 8px rgba(220,38,38,0.2); }

    /* ── ADD BUTTON ── */
    .btn-add {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: white; border: none; border-radius: 12px; padding: 0.6rem 1.4rem;
        font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(79,70,229,0.3); transition: all 0.2s ease;
    }
    .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(79,70,229,0.4); color: white; }

    /* ── STATUS BADGE ── */
    .badge-status { font-size: 0.72rem; font-weight: 700; border-radius: 30px; padding: 0.3rem 0.8rem; letter-spacing: 0.03em; }

    /* ── MODAL ── */
    .modal-content { border: none; border-radius: 20px; overflow: hidden; }
    .modal-header-custom {
        padding: 1.25rem 1.5rem; margin: 10px; border-radius: 14px; border: none;
        display: flex; align-items: center; gap: 0.75rem;
    }
    .modal-body { padding: 1.5rem; }
    .form-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.4rem; }
    .form-control, .form-select {
        border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.65rem 0.9rem;
        font-family: 'Outfit', sans-serif; font-size: 0.9rem; transition: border-color 0.2s, box-shadow 0.2s;
        background: #f8fafc;
    }
    .form-control:focus, .form-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); background: white; }
    .btn-cancel { background: #f1f5f9; color: #64748b; border: none; border-radius: 10px; padding: 0.6rem 1.4rem; font-weight: 600; font-size: 0.88rem; transition: all 0.2s; }
    .btn-cancel:hover { background: #e2e8f0; color: #334155; }
    .btn-save   { border: none; border-radius: 10px; padding: 0.6rem 1.4rem; font-weight: 700; font-size: 0.88rem; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(0,0,0,0.15); }
</style>
@endpush

@section('content')

{{-- STATS --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg, #6366f1, #4338ca);">
            <div class="mesh"></div>
            <div class="card-body p-4 d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-icon"><i class="bi bi-building"></i></div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill fw-semibold" style="font-size:0.7rem;">Total</span>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($totalDept) }}</div>
                    <div class="stat-label mt-1">Departemen Terdaftar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg, #10b981, #059669);">
            <div class="mesh"></div>
            <div class="card-body p-4 d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill fw-semibold" style="font-size:0.7rem;">Staff</span>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($totalUser) }}</div>
                    <div class="stat-label mt-1">User Terhubung</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <div class="mesh"></div>
            <div class="card-body p-4 d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill fw-semibold" style="font-size:0.7rem;">Latest</span>
                </div>
                <div>
                    <div class="fw-bold text-truncate" style="font-size:1.1rem; letter-spacing:-0.3px;">{{ $latestDept ?: '-' }}</div>
                    <div class="stat-label mt-1">Terakhir Ditambahkan</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLE SECTION --}}
<div class="card table-card">
    <div class="card-header-section">
        <div>
            <p class="section-title">Daftar Departemen</p>
            <p class="section-sub">Manajemen struktur departemen organisasi</p>
        </div>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalCreate">
            <i class="bi bi-plus-lg"></i> Tambah Departemen
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="60">#</th>
                        <th>Nama Departemen</th>
                        <th>Status</th>
                        <th>Tgl. Registrasi</th>
                        <th class="text-center pe-4" width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $d)
                    <tr class="{{ !$d->is_active ? 'opacity-60' : '' }}">
                        <td class="ps-4 fw-bold text-secondary">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="item-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-building-fill"></i>
                                </div>
                                <span class="fw-bold {{ !$d->is_active ? 'text-decoration-line-through text-muted' : 'text-dark' }}">{{ $d->name }}</span>
                            </div>
                        </td>
                        <td>
                            @if($d->is_active)
                                <span class="badge badge-status bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>
                            @else
                                <span class="badge badge-status bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="bi bi-x-circle-fill me-1"></i>Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:0.85rem;">
                            <i class="bi bi-calendar3 me-1 opacity-50"></i>{{ $d->created_at?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <form action="{{ route('superadmin.departments.toggle', $d->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="{{ $d->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        class="btn-act {{ $d->is_active ? 'btn-act-toggle-off' : 'btn-act-toggle-on' }}">
                                        <i class="bi {{ $d->is_active ? 'bi-slash-circle-fill' : 'bi-check-circle-fill' }}"></i>
                                    </button>
                                </form>
                                <button class="btn-act btn-act-edit" title="Edit" onclick="editDept('{{ $d->id }}', '{{ addslashes($d->name) }}')">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('superadmin.departments.destroy', $d->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus departemen {{ addslashes($d->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-act btn-act-delete" title="Hapus">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="opacity-20 mb-3"><i class="bi bi-folder-x display-1"></i></div>
                            <h6 class="fw-bold text-muted">Belum ada data departemen</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL CREATE --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom" style="background: linear-gradient(135deg,#6366f1,#4338ca);">
                <div class="bg-white bg-opacity-20 rounded-3 p-2"><i class="bi bi-building-add text-white fs-5"></i></div>
                <h5 class="modal-title fw-bold text-white mb-0">Tambah Departemen</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('superadmin.departments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Departemen</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Extrusion, Purchasing..." required autofocus>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-save text-white" style="background: linear-gradient(135deg,#6366f1,#4338ca);">
                        <i class="bi bi-check-lg me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
                <div class="bg-white bg-opacity-20 rounded-3 p-2"><i class="bi bi-pencil-square text-white fs-5"></i></div>
                <h5 class="modal-title fw-bold text-white mb-0">Edit Departemen</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Departemen</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-save text-white" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="bi bi-check-lg me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function editDept(id, name) {
        let url = "{{ route('superadmin.departments.update', ':id') }}".replace(':id', id);
        $('#formEdit').attr('action', url);
        $('#editName').val(name);
        $('#modalEdit').modal('show');
    }
</script>
@endpush
