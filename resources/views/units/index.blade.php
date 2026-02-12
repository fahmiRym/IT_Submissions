@extends('layouts.app')

@section('title', 'Master Data Unit')
@section('page-title', '📦 Master Unit')

@push('styles')
<style>
    /* Premium Stats Cards */
    .stat-card {
        border-radius: 20px;
        border: none;
        overflow: hidden;
        position: relative;
        transition: transform 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .mesh-gradient {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: radial-gradient(at 0% 0%, rgba(255,255,255,0.15) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, rgba(255,255,255,0.1) 0px, transparent 50%);
        z-index: 1;
    }
    
    /* Table Styling */
    .table-card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .table thead th {
        background-color: #f8fafc;
        padding: 1.25rem 1rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 800;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }
    .table tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
    }
    
    /* Action Buttons */
    .btn-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        transition: all 0.2s;
    }
    .btn-edit { background: #fefce8; color: #854d0e; }
    .btn-edit:hover { background: #fef9c3; color: #713f12; transform: scale(1.1); }
    .btn-delete { background: #fef2f2; color: #991b1b; }
    .btn-delete:hover { background: #fee2e2; color: #7f1d1d; transform: scale(1.1); }
</style>
@endpush

@section('content')

{{-- STATS SECTION --}}
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="mesh-gradient"></div>
            <div class="card-body p-4 position-relative" style="z-index: 2;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill">Total Unit</span>
                </div>
                <h2 class="fw-bold mb-1">{{ number_format($totalUnit) }}</h2>
                <p class="mb-0 small opacity-75 text-uppercase fw-bold">Unit Terdaftar</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
            <div class="mesh-gradient"></div>
            <div class="card-body p-4 position-relative" style="z-index: 2;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="bi bi-patch-check fs-4"></i>
                    </div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill">Latest</span>
                </div>
                <h2 class="fw-bold mb-1 text-truncate">{{ $latestUnit }}</h2>
                <p class="mb-0 small opacity-75 text-uppercase fw-bold">Terakhir Ditambahkan</p>
            </div>
        </div>
    </div>
</div>

{{-- ACTION & SEARCH --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Daftar Unit Kerja</h5>
        <p class="text-muted small mb-0">Manajemen data unit operasional sistem</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="bi bi-plus-lg me-2"></i> Tambah Unit
    </button>
</div>

{{-- DATA TABLE --}}
<div class="card table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="80">#</th>
                        <th>Nama Unit</th>
                        <th>Tanggal Registrasi</th>
                        <th class="text-center" width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $u)
                    <tr>
                        <td class="ps-4 text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 me-3">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="fw-bold text-dark">{{ $u->name }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-semibold text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn-icon btn-edit" onclick="editUnit('{{ $u->id }}', '{{ $u->name }}')" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('superadmin.units.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus unit ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-icon btn-delete" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="opacity-20 mb-3"><i class="bi bi-box-seam display-1"></i></div>
                            <h6 class="fw-bold text-muted">Belum ada data unit</h6>
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
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3 mt-2 mx-2 rounded-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Unit Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('superadmin.units.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Nama Unit</label>
                        <input type="text" name="name" class="form-control bg-light border-0 py-2" placeholder="Masukkan nama unit..." required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-warning text-dark border-0 py-3 mt-2 mx-2 rounded-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Nama Unit</label>
                        <input type="text" name="name" id="editName" class="form-control bg-light border-0 py-2" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function editUnit(id, name) {
        let url = "{{ route('superadmin.units.update', ':id') }}";
        url = url.replace(':id', id);
        $('#formEdit').attr('action', url);
        $('#editName').val(name);
        $('#modalEdit').modal('show');
    }
</script>
@endpush
