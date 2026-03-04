@extends('layouts.app')

@section('title', 'Master Data Manager')
@section('page-title', '🧑‍💼 Master Manager')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body, .card, .table, button, input, select { font-family: 'Outfit', sans-serif !important; }
    /* ── STAT CARDS (Dashboard Style) ── */
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
    .stat-overlay-icon {
        position: absolute;
        right: -15px;
        bottom: -15px;
        font-size: 7rem;
        opacity: 0.15;
        transform: rotate(-10deg);
        color: white;
    }
    .table-card { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.05); }
    .table-card .card-header-section { padding: 1.5rem 1.75rem; background: white; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .section-title { font-size: 1.05rem; font-weight: 800; color: #1e293b; margin: 0; }
    .section-sub { font-size: 0.78rem; color: #94a3b8; margin: 2px 0 0; font-weight: 500; }
    .table thead th { background: #f8fafc; color: #64748b; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.07em; padding: 1rem 1.25rem; border-bottom: 2px solid #f1f5f9; }
    .table tbody tr { border-bottom: 1px solid #f8fafc; transition: background 0.15s; }
    .table tbody tr:hover { background: #fafbff; }
    .table tbody td { padding: 1rem 1.25rem; vertical-align: middle; color: #334155; font-weight: 500; }
    .avatar-circle { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; flex-shrink: 0; }
    .btn-act { width: 36px; height: 36px; border-radius: 10px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; transition: all 0.2s ease; }
    .btn-act-toggle-off { background: #fef2f2; color: #dc2626; }
    .btn-act-toggle-off:hover { background: #fee2e2; transform: scale(1.1); box-shadow: 0 4px 8px rgba(220,38,38,0.2); }
    .btn-act-toggle-on { background: #f0fdf4; color: #16a34a; }
    .btn-act-toggle-on:hover { background: #dcfce7; transform: scale(1.1); box-shadow: 0 4px 8px rgba(22,163,74,0.2); }
    .btn-act-edit { background: #fffbeb; color: #d97706; }
    .btn-act-edit:hover { background: #fef9c3; transform: scale(1.1); box-shadow: 0 4px 8px rgba(217,119,6,0.2); }
    .btn-act-delete { background: #fef2f2; color: #dc2626; }
    .btn-act-delete:hover { background: #fee2e2; transform: scale(1.1); box-shadow: 0 4px 8px rgba(220,38,38,0.2); }
    .btn-add { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: white; border: none; border-radius: 12px; padding: 0.6rem 1.4rem; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(79,70,229,0.3); transition: all 0.2s ease; }
    .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(79,70,229,0.4); color: white; }
    .badge-status { font-size: 0.72rem; font-weight: 700; border-radius: 30px; padding: 0.3rem 0.8rem; letter-spacing: 0.03em; }
    .modal-content { border: none; border-radius: 20px; overflow: hidden; }
    .modal-header-custom { padding: 1.25rem 1.5rem; margin: 10px; border-radius: 14px; border: none; display: flex; align-items: center; gap: 0.75rem; }
    .modal-body { padding: 1.5rem; }
    .form-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.4rem; }
    .form-control, .form-select { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.65rem 0.9rem; font-family: 'Outfit', sans-serif; font-size: 0.9rem; transition: border-color 0.2s, box-shadow 0.2s; background: #f8fafc; }
    .form-control:focus, .form-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); background: white; }
    .btn-cancel { background: #f1f5f9; color: #64748b; border: none; border-radius: 10px; padding: 0.6rem 1.4rem; font-weight: 600; font-size: 0.88rem; transition: all 0.2s; }
    .btn-cancel:hover { background: #e2e8f0; }
    .btn-save { border: none; border-radius: 10px; padding: 0.6rem 1.4rem; font-weight: 700; font-size: 0.88rem; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(0,0,0,0.15); }
</style>
@endpush

@section('content')

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card-stat-vibrant h-100 p-4" style="background: linear-gradient(135deg, #4f46e5, #3730a3);">
            <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Manager Terdaftar</h6>
            <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($totalManager) }}</h2>
            <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-person-badge-fill me-1"></i> TOTAL DATA</div>
            <i class="bi bi-person-badge-fill stat-overlay-icon"></i>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-stat-vibrant h-100 p-4" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Terakhir Ditambahkan</h6>
            <h2 class="mb-0 fw-bold text-white text-truncate" style="font-size:2.2rem; line-height:1.2; padding-top:4px;">{{ $latestManager ?: '-' }}</h2>
            <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-stars me-1"></i> LATEST UPDATE</div>
            <i class="bi bi-stars stat-overlay-icon"></i>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header-section">
        <div>
            <p class="section-title">Daftar Manager</p>
            <p class="section-sub">Manajemen data pimpinan dan penanggung jawab</p>
        </div>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalCreate">
            <i class="bi bi-person-plus-fill"></i> Tambah Manager
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="60">#</th>
                        <th>Nama Manager</th>
                        <th>Status</th>
                        <th>Tgl. Registrasi</th>
                        <th class="text-center pe-4" width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($managers as $m)
                    <tr class="{{ !$m->is_active ? 'opacity-60' : '' }}">
                        <td class="ps-4 fw-bold text-secondary">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle bg-indigo-soft text-primary" style="background: rgba(99,102,241,0.1);">
                                    {{ strtoupper(substr($m->name, 0, 1)) }}
                                </div>
                                <span class="fw-bold {{ !$m->is_active ? 'text-decoration-line-through text-muted' : 'text-dark' }}">{{ $m->name }}</span>
                            </div>
                        </td>
                        <td>
                            @if($m->is_active)
                                <span class="badge badge-status bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>
                            @else
                                <span class="badge badge-status bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="bi bi-x-circle-fill me-1"></i>Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:0.85rem;">
                            <i class="bi bi-calendar3 me-1 opacity-50"></i>{{ $m->created_at?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <form action="{{ route('superadmin.managers.toggle', $m->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="{{ $m->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        class="btn-act {{ $m->is_active ? 'btn-act-toggle-off' : 'btn-act-toggle-on' }}">
                                        <i class="bi {{ $m->is_active ? 'bi-slash-circle-fill' : 'bi-check-circle-fill' }}"></i>
                                    </button>
                                </form>
                                <button class="btn-act btn-act-edit" title="Edit" onclick="editManager('{{ $m->id }}', '{{ addslashes($m->name) }}')">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('superadmin.managers.destroy', $m->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus manager {{ addslashes($m->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-act btn-act-delete" title="Hapus"><i class="bi bi-trash3-fill"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="opacity-20 mb-3"><i class="bi bi-person-x display-1"></i></div>
                            <h6 class="fw-bold text-muted">Belum ada data manager</h6>
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
            <div class="modal-header-custom" style="background: linear-gradient(135deg,#4f46e5,#3730a3);">
                <div class="bg-white bg-opacity-20 rounded-3 p-2"><i class="bi bi-person-plus-fill text-white fs-5"></i></div>
                <h5 class="modal-title fw-bold text-white mb-0">Tambah Manager Baru</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('superadmin.managers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap Manager</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso..." required autofocus>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-save text-white" style="background:linear-gradient(135deg,#4f46e5,#3730a3);">
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
                <h5 class="modal-title fw-bold text-white mb-0">Edit Data Manager</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap Manager</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-save text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
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
    function editManager(id, name) {
        let url = "{{ route('superadmin.managers.update', ':id') }}".replace(':id', id);
        $('#formEdit').attr('action', url);
        $('#editName').val(name);
        $('#modalEdit').modal('show');
    }
</script>
@endpush
