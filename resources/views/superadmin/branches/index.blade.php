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
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold btnManageDepts"
                                    data-branch-id="{{ $b->id }}"
                                    data-branch-name="{{ $b->name }}"
                                    data-branch-code="{{ $b->code }}"
                                    data-branch-kota="{{ $b->kota }}"
                                    style="font-size:0.72rem;"
                                    title="Kelola departemen di cabang ini (checklist)">
                                <i class="bi bi-check2-square me-1"></i>{{ $b->departments_count }} · Kelola
                            </button>
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
                                    style="width:32px; height:32px;" title="Edit info cabang">
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

    {{-- ORPHAN DEPTS WARNING (kalau ada dept tanpa cabang) --}}
    @if(!empty($orphanDepts) && count($orphanDepts) > 0)
        <div class="card border-0 shadow-sm rounded-4 mt-3" style="border-left: 4px solid #f59e0b !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                    <div class="flex-fill">
                        <div class="fw-bold text-dark mb-1">
                            {{ count($orphanDepts) }} Departemen belum di-assign ke cabang manapun
                        </div>
                        <small class="text-muted d-block mb-2">
                            Dokumen dari dept ini akan pakai <strong>fallback global</strong> ({{ \App\Models\Setting::get('kota_ba', 'PASURUAN') }}).
                            Klik Edit di menu <a href="{{ route('superadmin.departments.index') }}" class="fw-bold text-primary">Departemen</a> untuk assign.
                        </small>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($orphanDepts as $od)
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 fw-semibold" style="font-size:0.7rem;">
                                    {{ $od->name }}
                                    @if(!$od->is_active) <i class="bi bi-pause-fill ms-1" title="Nonaktif"></i> @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL KELOLA DEPARTEMEN (bulk assign via checklist) --}}
    <div class="modal fade" id="modalManageDepts" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form id="formManageDepts" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white border-0">
                        <div>
                            <h5 class="modal-title fw-bold mb-0">
                                <i class="bi bi-check2-square me-2"></i>Kelola Departemen — <span id="mgrBranchName">—</span>
                            </h5>
                            <small class="opacity-75">
                                Kode: <span class="font-monospace fw-bold" id="mgrBranchCode">—</span>
                                · Kota: <span class="fw-bold" id="mgrBranchKota">—</span>
                            </small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-25 py-2 px-3 mb-3" style="font-size:0.8rem;">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Centang dept yg mau <strong>dimasukkan ke cabang ini</strong>. Simpan → dept ter-centang otomatis pindah ke sini,
                            dept yg tidak dicentang (tapi sebelumnya di sini) akan jadi <strong>orphan</strong> (fallback global).
                        </div>

                        {{-- Toolbar: search + bulk action --}}
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-3 p-2 bg-light rounded-3">
                            <div class="position-relative flex-grow-1" style="min-width:200px;">
                                <i class="bi bi-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
                                <input type="text" id="mgrDeptSearch" class="form-control form-control-sm ps-4" placeholder="Cari dept...">
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="mgrBtnCheckAll">
                                <i class="bi bi-check-all me-1"></i>Centang Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" id="mgrBtnCheckNone">
                                <i class="bi bi-x-lg me-1"></i>Kosongkan
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning fw-bold" id="mgrBtnCheckOrphan">
                                <i class="bi bi-exclamation-circle me-1"></i>Centang Orphan Saja
                            </button>
                            <span class="ms-auto badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 fw-bold">
                                <span id="mgrSelectedCount">0</span> terpilih
                            </span>
                        </div>

                        {{-- List depts --}}
                        <div class="row row-cols-1 row-cols-md-2 g-2" id="mgrDeptList">
                            @foreach($allDepts as $d)
                                <div class="col mgr-dept-item"
                                     data-name-lower="{{ strtolower($d->name) }}"
                                     data-current-branch-id="{{ $d->branch_id ?? '' }}"
                                     data-current-branch-name="{{ $d->branch?->name ?? '' }}"
                                     data-current-branch-code="{{ $d->branch?->code ?? '' }}"
                                     data-is-orphan="{{ is_null($d->branch_id) ? '1' : '0' }}"
                                     data-is-active="{{ $d->is_active ? '1' : '0' }}">
                                    <label class="d-flex align-items-center gap-2 p-2 rounded-3 border h-100 mgr-dept-label"
                                           style="cursor:pointer; transition:all .15s ease;">
                                        <input type="checkbox" name="dept_ids[]" value="{{ $d->id }}"
                                               class="form-check-input mt-0 mgr-dept-cb" style="flex-shrink:0; transform:scale(1.1);">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-dark text-truncate {{ !$d->is_active ? 'text-muted text-decoration-line-through' : '' }}"
                                                      style="font-size:0.85rem;">{{ $d->name }}</span>
                                                @if(!$d->is_active)
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border" style="font-size:0.55rem;">OFF</span>
                                                @endif
                                            </div>
                                            <div class="mt-1 mgr-branch-label">
                                                @if($d->branch_id)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fw-bold" style="font-size:0.6rem;">
                                                        <i class="bi bi-buildings-fill me-1"></i>{{ $d->branch->code }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 fw-bold" style="font-size:0.6rem;">
                                                        <i class="bi bi-exclamation-circle me-1"></i>ORPHAN
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div id="mgrEmptyState" class="text-center text-muted py-4 d-none">
                            <i class="bi bi-search fs-2 opacity-50"></i>
                            <p class="mt-2 mb-0 small">Tidak ada dept yang cocok dengan pencarian.</p>
                        </div>
                    </div>

                    <div class="modal-footer border-0 d-flex justify-content-between">
                        <small class="text-muted">
                            <i class="bi bi-lightbulb-fill text-warning me-1"></i>
                            Total <strong>{{ count($allDepts) }}</strong> departemen tersedia
                        </small>
                        <div>
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4">
                                <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .mgr-dept-label:hover { border-color:#6366f1 !important; background:#f5f3ff; }
        .mgr-dept-cb:checked ~ .flex-grow-1 .fw-bold { color:#4338ca !important; }
        .col.mgr-dept-item.d-none { display:none !important; }
    </style>

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

    // ============ MODAL KELOLA DEPARTEMEN (bulk assign) ============
    const mgrCheckboxes = () => document.querySelectorAll('.mgr-dept-cb');
    const mgrItems      = () => document.querySelectorAll('.mgr-dept-item');
    const mgrCountLabel = document.getElementById('mgrSelectedCount');

    function updateMgrCount() {
        const n = document.querySelectorAll('.mgr-dept-cb:checked').length;
        mgrCountLabel.textContent = n;
    }

    document.querySelectorAll('.btnManageDepts').forEach(btn => {
        btn.addEventListener('click', function () {
            const id   = this.dataset.branchId;
            const name = this.dataset.branchName;
            const code = this.dataset.branchCode;
            const kota = this.dataset.branchKota;

            document.getElementById('mgrBranchName').textContent = name;
            document.getElementById('mgrBranchCode').textContent = code;
            document.getElementById('mgrBranchKota').textContent = kota;
            document.getElementById('formManageDepts').action = "{{ url('superadmin/branches') }}/" + id + "/sync-depts";

            // Reset UI + pre-check depts yg sudah masuk branch ini
            document.getElementById('mgrDeptSearch').value = '';
            mgrItems().forEach(el => el.classList.remove('d-none'));
            document.getElementById('mgrEmptyState').classList.add('d-none');
            mgrCheckboxes().forEach(cb => {
                const item = cb.closest('.mgr-dept-item');
                cb.checked = (item.dataset.currentBranchId === id);
            });
            updateMgrCount();

            new bootstrap.Modal(document.getElementById('modalManageDepts')).show();
        });
    });

    // Search filter (client-side)
    document.getElementById('mgrDeptSearch')?.addEventListener('input', function () {
        const kw = this.value.trim().toLowerCase();
        let visible = 0;
        mgrItems().forEach(el => {
            const match = !kw || el.dataset.nameLower.includes(kw);
            el.classList.toggle('d-none', !match);
            if (match) visible++;
        });
        document.getElementById('mgrEmptyState').classList.toggle('d-none', visible > 0);
    });

    // Bulk actions
    document.getElementById('mgrBtnCheckAll')?.addEventListener('click', () => {
        mgrItems().forEach(el => {
            if (!el.classList.contains('d-none')) el.querySelector('.mgr-dept-cb').checked = true;
        });
        updateMgrCount();
    });
    document.getElementById('mgrBtnCheckNone')?.addEventListener('click', () => {
        mgrItems().forEach(el => {
            if (!el.classList.contains('d-none')) el.querySelector('.mgr-dept-cb').checked = false;
        });
        updateMgrCount();
    });
    document.getElementById('mgrBtnCheckOrphan')?.addEventListener('click', () => {
        mgrItems().forEach(el => {
            if (el.classList.contains('d-none')) return;
            el.querySelector('.mgr-dept-cb').checked = (el.dataset.isOrphan === '1');
        });
        updateMgrCount();
    });

    // Live count on any checkbox change
    document.addEventListener('change', e => {
        if (e.target.classList.contains('mgr-dept-cb')) updateMgrCount();
    });
</script>
@endpush
