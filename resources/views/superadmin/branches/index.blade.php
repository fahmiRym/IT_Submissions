@extends('layouts.app')

@section('title', 'Cabang / Branch')
@section('page-title', 'Master Cabang / Branch')

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-buildings-fill text-danger me-2"></i>Master Cabang</h5>
                    <small class="text-muted">Kelola daftar cabang / plant. Kota di sini dipakai stamp dokumen (contoh: "PASURUAN, 06 Maret 2026")</small>
                </div>
                <button type="button" class="btn btn-danger fw-bold shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalBranchCreate">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Cabang
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">
                        <th class="ps-4 text-muted fw-bold" style="width:50px;">#</th>
                        <th class="text-muted fw-bold">NAMA CABANG</th>
                        <th class="text-muted fw-bold" style="width:100px;">KODE</th>
                        <th class="text-muted fw-bold">KOTA</th>
                        <th class="text-muted fw-bold">ALAMAT</th>
                        <th class="text-center text-muted fw-bold" style="width:80px;">DEPT</th>
                        <th class="text-center text-muted fw-bold" style="width:80px;">STATUS</th>
                        <th class="text-end pe-4 text-muted fw-bold" style="width:120px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $i => $b)
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $b->name }}</div>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 font-monospace fw-bold" style="font-size:0.72rem;">
                                {{ $b->code }}
                            </span>
                        </td>
                        <td class="fw-bold text-primary">{{ $b->kota }}</td>
                        <td class="small text-muted">{{ $b->alamat ?: '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border rounded-pill fw-bold" style="font-size:0.72rem;">
                                {{ $b->departments_count }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($b->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill" style="font-size:0.65rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>AKTIF
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill" style="font-size:0.65rem;">
                                    NONAKTIF
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle p-1 me-1 btnEditBranch"
                                    data-branch="{{ json_encode($b) }}"
                                    style="width:32px; height:32px;" title="Edit">
                                <i class="bi bi-pencil-fill small"></i>
                            </button>
                            <form action="{{ route('superadmin.branches.destroy', $b->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus cabang {{ $b->name }}? Departemen yang ter-link akan lepas ke NULL.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width:32px; height:32px;" title="Hapus">
                                    <i class="bi bi-trash-fill small"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-buildings display-4 opacity-25"></i>
                            <p class="mt-2 small">Belum ada cabang. Klik "Tambah Cabang" untuk mulai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL CREATE --}}
    <div class="modal fade" id="modalBranchCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form action="{{ route('superadmin.branches.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-buildings-fill me-2"></i>Tambah Cabang</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-secondary">NAMA CABANG *</label>
                                <input type="text" name="name" class="form-control" required placeholder="Contoh: Kantor Cabang Jatake" maxlength="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">KODE *</label>
                                <input type="text" name="code" class="form-control font-monospace text-uppercase" required placeholder="JTK" maxlength="20" style="letter-spacing:2px;">
                                <small class="text-muted" style="font-size:0.68rem;">Huruf besar, angka, dash/underscore</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">KOTA (untuk stamp dokumen) *</label>
                                <input type="text" name="kota" class="form-control text-uppercase" required placeholder="TANGERANG" maxlength="60">
                                <small class="text-muted" style="font-size:0.68rem;">Muncul di dokumen: "TANGERANG, 06 Maret 2026"</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">STATUS</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="createActive" style="transform:scale(1.3);">
                                    <label class="form-check-label fw-semibold" for="createActive">Aktif</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">ALAMAT (Opsional)</label>
                                <textarea name="alamat" class="form-control" rows="2" maxlength="255" placeholder="Alamat lengkap cabang (untuk referensi internal)"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4">
                            <i class="bi bi-check-lg me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT (populated via JS) --}}
    <div class="modal fade" id="modalBranchEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form id="formBranchEdit" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-pencil-fill me-2"></i>Edit Cabang</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-secondary">NAMA CABANG *</label>
                                <input type="text" name="name" id="editName" class="form-control" required maxlength="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">KODE *</label>
                                <input type="text" name="code" id="editCode" class="form-control font-monospace text-uppercase" required maxlength="20" style="letter-spacing:2px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">KOTA (untuk stamp dokumen) *</label>
                                <input type="text" name="kota" id="editKota" class="form-control text-uppercase" required maxlength="60">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">STATUS</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActive" style="transform:scale(1.3);">
                                    <label class="form-check-label fw-semibold" for="editActive">Aktif</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">ALAMAT (Opsional)</label>
                                <textarea name="alamat" id="editAlamat" class="form-control" rows="2" maxlength="255"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4">
                            <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.btnEditBranch').forEach(btn => {
        btn.addEventListener('click', function () {
            const b = JSON.parse(this.dataset.branch);
            document.getElementById('editName').value   = b.name || '';
            document.getElementById('editCode').value   = b.code || '';
            document.getElementById('editKota').value   = b.kota || '';
            document.getElementById('editAlamat').value = b.alamat || '';
            document.getElementById('editActive').checked = !!b.is_active;
            document.getElementById('formBranchEdit').action = "{{ url('superadmin/branches') }}/" + b.id;
            new bootstrap.Modal(document.getElementById('modalBranchEdit')).show();
        });
    });
</script>
@endpush
