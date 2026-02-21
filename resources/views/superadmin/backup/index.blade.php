@extends('layouts.app')

@section('title', 'Backup & Restore Data | IT Submission')
@section('page-title', 'Backup & Restore Data')

@push('styles')
<style>
    .backup-card {
        border: none;
        border-radius: 20px;
        background: white;
        box-shadow: 0 4px 24px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .backup-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    .backup-card-header {
        padding: 1.5rem 1.75rem 1rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .backup-card-body { padding: 1.75rem; }

    .icon-circle {
        width: 56px; height: 56px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .drop-zone {
        border: 2.5px dashed #cbd5e1;
        border-radius: 16px;
        padding: 2.5rem;
        text-align: center;
        transition: all 0.25s;
        cursor: pointer;
        background: #f8fafc;
    }
    .drop-zone.dragover, .drop-zone:hover {
        border-color: #6366f1;
        background: #eef2ff;
    }
    .drop-zone input[type="file"] { display: none; }

    .filter-row { background: #f8fafc; border-radius: 12px; padding: 1rem 1.25rem; }

    .step-badge {
        width: 28px; height: 28px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 800;
        flex-shrink: 0;
    }

    .info-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: #f1f5f9;
        border-radius: 99px;
        padding: 4px 12px;
        font-size: 0.75rem; font-weight: 600;
        color: #475569;
    }
    .progress-bar-animated { animation: progress-pulse 1.5s infinite; }
    @keyframes progress-pulse { 0%,100%{opacity:1} 50%{opacity:.7} }
</style>
@endpush

@section('content')

{{-- ── ALERTS ──────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="alert border-0 rounded-4 shadow-sm d-flex align-items-center gap-3 mb-4" style="background:#f0fdf4; border-left: 4px solid #22c55e !important;">
    <i class="bi bi-check-circle-fill text-success fs-4"></i>
    <div class="flex-grow-1">
        <div class="fw-bold text-success">Berhasil!</div>
        <div class="small text-muted">{{ session('success') }}</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert border-0 rounded-4 shadow-sm d-flex align-items-start gap-3 mb-4" style="background:#fef2f2; border-left: 4px solid #ef4444 !important;">
    <i class="bi bi-exclamation-triangle-fill text-danger fs-4 mt-1"></i>
    <div class="flex-grow-1">
        <div class="fw-bold text-danger">Terjadi Kesalahan</div>
        <ul class="mb-0 small text-muted ps-3 mt-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

@if(session('import_errors'))
<div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4">
    <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle me-2"></i>Detail Peringatan Import:</h6>
    <ul class="small mb-0 ps-3">
        @foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

{{-- ── PAGE HEADER ─────────────────────────────────────────────── --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="icon-circle" style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white;">
        <i class="bi bi-database-fill-gear"></i>
    </div>
    <div>
        <h5 class="fw-bold mb-0 text-dark">Manajemen Backup & Restore</h5>
        <p class="text-muted small mb-0">Cadangkan semua data pengajuan ke file JSON dan pulihkan kapan saja</p>
    </div>
    <a href="{{ route('superadmin.arsip.index') }}" class="btn btn-light rounded-pill ms-auto px-4 fw-bold shadow-sm">
        <i class="bi bi-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="row g-4">
    {{-- ── LEFT: EXPORT ──────────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="backup-card h-100">
            <div class="backup-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white;">
                        <i class="bi bi-cloud-download-fill"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Export / Download Backup</h5>
                        <p class="text-muted small mb-0">Unduh semua data ke file JSON terenkripsi</p>
                    </div>
                </div>
            </div>
            <div class="backup-card-body">
                {{-- Steps --}}
                <div class="d-flex flex-column gap-3 mb-4">
                    @foreach([
                        ['1', 'Pilih Rentang Tanggal', 'Opsional. Kosongkan untuk export semua data.', '#0ea5e9', 'bi-calendar3'],
                        ['2', 'Klik tombol Download', 'File JSON akan diunduh otomatis ke komputer Anda.', '#6366f1', 'bi-download'],
                        ['3', 'Simpan file dengan aman', 'Simpan file backup di tempat yang aman.', '#22c55e', 'bi-shield-check'],
                    ] as [$n, $t, $d, $c, $i])
                    <div class="d-flex align-items-start gap-3">
                        <div class="step-badge" style="background:{{ $c }}20; color:{{ $c }};">{{ $n }}</div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size:.9rem;">{{ $t }}</div>
                            <div class="text-muted small">{{ $d }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Filter --}}
                <form action="{{ route('superadmin.backup.export') }}" method="GET" id="exportForm">
                    <div class="filter-row mb-4">
                        <p class="fw-bold text-dark small mb-2"><i class="bi bi-funnel me-1 text-primary"></i>Filter Export (Opsional)</p>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted mb-1">Dari Tanggal</label>
                                <input type="date" name="from" class="form-control border-0 bg-white rounded-3" style="font-size:.88rem;">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted mb-1">Sampai Tanggal</label>
                                <input type="date" name="to" class="form-control border-0 bg-white rounded-3" style="font-size:.88rem;">
                            </div>
                        </div>
                    </div>

                    {{-- Info pills --}}
                    <div class="d-flex gap-2 flex-wrap mb-4">
                        <span class="info-pill"><i class="bi bi-filetype-json text-primary"></i> Format: JSON</span>
                        <span class="info-pill"><i class="bi bi-box-seam text-success"></i> Termasuk semua item</span>
                        <span class="info-pill"><i class="bi bi-people text-indigo-500"></i> Data relasi tersedia</span>
                    </div>

                    <button type="submit" class="btn w-100 fw-bold rounded-3 py-3 shadow-sm" 
                            style="background: linear-gradient(135deg,#0ea5e9,#0284c7); color:white; font-size:.95rem;">
                        <i class="bi bi-cloud-download-fill me-2"></i>Download Backup Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: IMPORT ─────────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="backup-card h-100">
            <div class="backup-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle" style="background: linear-gradient(135deg, #6366f1, #4f46e5); color:white;">
                        <i class="bi bi-cloud-upload-fill"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Import / Restore Backup</h5>
                        <p class="text-muted small mb-0">Pulihkan data dari file backup JSON</p>
                    </div>
                </div>
            </div>
            <div class="backup-card-body">
                {{-- Warning --}}
                <div class="d-flex gap-3 p-3 rounded-3 mb-4" style="background:#fffbeb; border:1px solid #fde68a;">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-4 flex-shrink-0 mt-1"></i>
                    <div>
                        <div class="fw-bold" style="color:#92400e; font-size:.9rem;">Perhatian Sebelum Import</div>
                        <ul class="mb-0 small mt-1 ps-3" style="color:#78350f;">
                            <li>Data yang sudah ada dengan <strong>No Registrasi sama</strong> akan <strong>di-update</strong> (bukan duplikat)</li>
                            <li>Data baru akan langsung <strong>ditambahkan</strong></li>
                            <li>User, Departemen, Manager, dan Unit harus <strong>sudah ada</strong> di sistem</li>
                        </ul>
                    </div>
                </div>

                {{-- Upload Form --}}
                <form action="{{ route('superadmin.backup.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="drop-zone mb-4" id="dropZone" onclick="document.getElementById('fileInput').click()">
                        <input type="file" name="backup_file" id="fileInput" accept=".json" onchange="handleFileSelect(this)">
                        <div id="dropZoneContent">
                            <i class="bi bi-file-earmark-arrow-up fs-1 text-muted mb-3 d-block"></i>
                            <div class="fw-bold text-dark mb-1">Klik atau seret file JSON ke sini</div>
                            <div class="small text-muted">Format .json, maksimum 50MB</div>
                        </div>
                        <div id="fileSelected" class="d-none">
                            <i class="bi bi-file-earmark-check fs-1 text-success mb-2 d-block"></i>
                            <div class="fw-bold text-dark" id="fileName">-</div>
                            <div class="small text-muted" id="fileSize">-</div>
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 fw-bold rounded-3 py-3 shadow-sm" id="importBtn"
                            style="background: linear-gradient(135deg,#6366f1,#4f46e5); color:white; font-size:.95rem;" disabled>
                        <i class="bi bi-cloud-upload-fill me-2"></i>Import & Restore Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── EDIT NO REGISTRASI SECTION ──────────────────────────────── --}}
<div class="backup-card mt-4">
    <div class="backup-card-header">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-circle" style="background: linear-gradient(135deg, #f59e0b, #d97706); color:white;">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">Edit No Registrasi</h5>
                <p class="text-muted small mb-0">Perbaiki atau ubah nomor registrasi pengajuan secara manual</p>
            </div>
        </div>
    </div>
    <div class="backup-card-body">
        <div class="alert border-0 rounded-3 d-flex gap-3 align-items-start mb-4" style="background:#f0f9ff; border-left:4px solid #0ea5e9 !important;">
            <i class="bi bi-info-circle-fill text-info fs-5 mt-1"></i>
            <div class="small text-muted">
                Cari pengajuan berdasarkan No Registrasi lama atau ID, lalu masukkan nomor registrasi baru. 
                Pastikan nomor baru unik dan belum digunakan oleh pengajuan lain.
            </div>
        </div>

        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted mb-1">Cari Pengajuan</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchNoReg" class="form-control border-0 bg-light"
                           placeholder="No Registrasi atau ID..." oninput="searchArsip(this.value)">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-muted mb-1">No Registrasi Baru</label>
                <input type="text" id="newNoReg" class="form-control border-0 bg-light" 
                       placeholder="Ketik nomor baru..." disabled>
            </div>
            <div class="col-md-2">
                <button class="btn w-100 fw-bold rounded-3 py-2" id="btnSaveNoReg"
                        style="background: linear-gradient(135deg,#f59e0b,#d97706); color:white;" 
                        disabled onclick="saveNoRegistrasi()">
                    <i class="bi bi-check-lg me-1"></i>Simpan
                </button>
            </div>
        </div>

        {{-- Search Result --}}
        <div id="searchResult" class="mt-3 d-none">
            <div class="p-3 rounded-3 border" style="background:#f8fafc;">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <div class="small text-muted fw-bold text-uppercase">Pengajuan Ditemukan</div>
                        <div class="fw-bold text-dark" id="resultName">-</div>
                        <div class="d-flex gap-2 mt-1">
                            <span class="badge bg-light text-dark border" id="resultJenis">-</span>
                            <span class="badge bg-light text-dark border" id="resultStatus">-</span>
                        </div>
                    </div>
                    <div class="ms-auto text-end">
                        <div class="small text-muted fw-bold text-uppercase">No Registrasi Saat Ini</div>
                        <div class="fw-bold font-monospace text-info" id="resultNoReg">-</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="searchNotFound" class="mt-3 d-none">
            <div class="p-3 rounded-3" style="background:#fef2f2;">
                <div class="text-danger small fw-bold"><i class="bi bi-x-circle me-1"></i>Pengajuan tidak ditemukan.</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Drag & Drop ──────────────────────────────────────────────
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.json')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('fileInput').files = dt.files;
        handleFileSelect({ files: [file] });
    } else {
        alert('Hanya file .json yang diterima.');
    }
});

function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('dropZoneContent').classList.add('d-none');
    document.getElementById('fileSelected').classList.remove('d-none');
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    document.getElementById('importBtn').disabled = false;
}

// Loading state on import submit
document.getElementById('importForm').addEventListener('submit', function() {
    const btn = document.getElementById('importBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengimpor data...';
    btn.disabled = true;
});

// ── Edit No Registrasi ───────────────────────────────────────
let selectedArsipId = null;
let searchTimeout = null;

function searchArsip(val) {
    clearTimeout(searchTimeout);
    if (val.length < 2) {
        hideResults(); return;
    }
    searchTimeout = setTimeout(() => {
        fetch(`/superadmin/arsip?q=${encodeURIComponent(val)}&per_page=1`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.text())
        .then(html => {
            // Parse JSON from the response
            // Instead, use a dedicated AJAX search
            fetchArsip(val);
        });
    }, 350);
}

function fetchArsip(q) {
    fetch(`/superadmin/arsip/search-simple?q=${encodeURIComponent(q)}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.id) {
            showResult(data);
        } else {
            showNotFound();
        }
    })
    .catch(() => showNotFound());
}

function showResult(data) {
    selectedArsipId = data.id;
    document.getElementById('resultName').textContent = (data.admin_name || 'Unknown') + ' — ' + (data.tgl_pengajuan || '-');
    document.getElementById('resultJenis').textContent = (data.jenis_pengajuan || '-').replace(/_/g,' ');
    document.getElementById('resultStatus').textContent = data.ket_process || '-';
    document.getElementById('resultNoReg').textContent = data.no_registrasi || '(kosong)';
    document.getElementById('newNoReg').value = data.no_registrasi || '';
    document.getElementById('searchResult').classList.remove('d-none');
    document.getElementById('searchNotFound').classList.add('d-none');
    document.getElementById('newNoReg').disabled = false;
    document.getElementById('btnSaveNoReg').disabled = false;
}

function showNotFound() {
    selectedArsipId = null;
    document.getElementById('searchResult').classList.add('d-none');
    document.getElementById('searchNotFound').classList.remove('d-none');
    document.getElementById('newNoReg').disabled = true;
    document.getElementById('btnSaveNoReg').disabled = true;
}

function hideResults() {
    selectedArsipId = null;
    document.getElementById('searchResult').classList.add('d-none');
    document.getElementById('searchNotFound').classList.add('d-none');
    document.getElementById('newNoReg').disabled = true;
    document.getElementById('btnSaveNoReg').disabled = true;
}

function saveNoRegistrasi() {
    if (!selectedArsipId) return;
    const newVal = document.getElementById('newNoReg').value.trim();
    if (!newVal) { alert('No Registrasi tidak boleh kosong.'); return; }
    if (!confirm(`Ubah No Registrasi menjadi "${newVal}"?`)) return;

    const btn = document.getElementById('btnSaveNoReg');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(`/superadmin/arsip/${selectedArsipId}/no-registrasi`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ no_registrasi: newVal })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('resultNoReg').textContent = data.new;
            showToast('No Registrasi berhasil diperbarui!', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan.', 'danger');
        }
    })
    .catch(() => showToast('Terjadi kesalahan koneksi.', 'danger'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Simpan';
    });
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.className = `alert alert-${type} border-0 rounded-3 shadow position-fixed fw-bold`;
    t.style.cssText = 'top:20px;right:20px;z-index:9999;min-width:300px;';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
</script>
@endpush
