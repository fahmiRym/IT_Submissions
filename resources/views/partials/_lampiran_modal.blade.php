{{-- Modal upload lampiran multi-PDF + list existing.
     Pakai: @include('partials._lampiran_modal', [
       'uploadRouteName' => 'admin.arsip.upload-lampiran',
       'deleteRouteName' => 'admin.arsip.delete-lampiran',
       'listRouteName'   => 'admin.arsip.list-lampiran',
     ]) --}}
@php
    $uploadRouteName = $uploadRouteName ?? 'admin.arsip.upload-lampiran';
    $deleteRouteName = $deleteRouteName ?? 'admin.arsip.delete-lampiran';
    $listRouteName   = $listRouteName ?? 'admin.arsip.list-lampiran';
@endphp

<div class="modal fade" id="modalLampiran" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 text-white"
                 style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                    <div class="bg-white bg-opacity-25 rounded-3 p-2 flex-shrink-0" style="backdrop-filter: blur(8px);">
                        <i class="bi bi-paperclip fs-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h5 class="modal-title fw-bold mb-0">Kelola Lampiran PDF</h5>
                        <small class="text-white-50 d-block text-truncate" id="lampiranArsipInfo">Pilih file PDF untuk pengajuan ini</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                {{-- ===== EXISTING LAMPIRAN LIST ===== --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-folder-fill text-primary"></i>
                        <h6 class="fw-bold mb-0 small">Lampiran Tersimpan</h6>
                        <span class="badge bg-primary-subtle text-primary ms-1" id="lampiranCountBadge" style="font-size:0.65rem;">0</span>
                    </div>
                    <div id="lampiranListWrap" class="lampiran-list">
                        <div class="text-center text-muted py-4 lampiran-empty">
                            <i class="bi bi-inbox fs-3 opacity-50"></i>
                            <div class="small mt-2">Belum ada lampiran</div>
                        </div>
                    </div>
                </div>

                {{-- ===== UPLOAD FORM ===== --}}
                <form id="formUploadLampiran" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="border rounded-4 p-3" style="background: linear-gradient(135deg, #f5f3ff 0%, #faf5ff 100%); border-color: rgba(124,58,237,0.18) !important;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-cloud-arrow-up-fill text-primary"></i>
                            <h6 class="fw-bold mb-0 small">Upload Lampiran Baru</h6>
                            <span class="badge bg-info-subtle text-info ms-auto" style="font-size:0.6rem;">
                                <i class="bi bi-file-earmark-pdf me-1"></i>PDF · MAX 10MB · MULTI
                            </span>
                        </div>

                        <div class="mb-2">
                            <input type="file" name="lampiran[]" id="lampiranFile" class="form-control"
                                   accept="application/pdf,.pdf" multiple required>
                        </div>

                        <div id="lampiranPreviewList" class="d-none mt-2"></div>

                        <div class="mb-2 mt-2">
                            <input type="text" name="keterangan" class="form-control form-control-sm"
                                   placeholder="Keterangan / catatan singkat (opsional, berlaku untuk semua file di upload ini)...">
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn fw-bold rounded-pill px-4 text-white shadow-sm"
                                    style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                                <i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload
                            </button>
                        </div>
                    </div>
                </form>

            </div>

            <div class="modal-footer border-top py-3 px-4 bg-light">
                <span class="text-muted small me-auto"><i class="bi bi-info-circle me-1"></i>File akan otomatis tergabung di "Show Document".</span>
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .lampiran-list { display: flex; flex-direction: column; gap: 8px; }
    .lampiran-item {
        display: flex; align-items: center; gap: 12px;
        padding: 0.6rem 0.85rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .lampiran-item:hover {
        border-color: rgba(99, 102, 241, 0.35);
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.08);
    }
    .lampiran-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #b91c1c;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .lampiran-meta { line-height: 1.25; }
    .lampiran-meta .name { font-weight: 700; font-size: 0.82rem; color: #1e293b; }
    .lampiran-meta .sub { font-size: 0.65rem; color: #64748b; font-family: monospace; }
    .lampiran-actions { display: flex; gap: 4px; flex-shrink: 0; }
    .lampiran-actions a, .lampiran-actions button {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        border: none;
        background: #f1f5f9;
        color: #475569;
        transition: all 0.2s ease;
    }
    .lampiran-actions a:hover { background: #dbeafe; color: #1d4ed8; }
    .lampiran-actions button.lampiran-del:hover { background: #fee2e2; color: #b91c1c; }

    .lampiran-preview-item {
        display: flex; align-items: center; gap: 10px;
        background: rgba(255,255,255,0.7);
        border: 1px dashed rgba(124, 58, 237, 0.3);
        border-radius: 10px;
        padding: 0.5rem 0.75rem;
        margin-top: 6px;
        font-size: 0.78rem;
    }
    .lampiran-preview-item i { color: #b91c1c; font-size: 1.1rem; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const modal = document.getElementById('modalLampiran');
    if (!modal) return;

    const uploadTemplate = "{{ route($uploadRouteName, ['id' => 'ARSIP_ID_PLACEHOLDER']) }}";
    const listTemplate   = "{{ Route::has($listRouteName) ? route($listRouteName, ['id' => 'ARSIP_ID_PLACEHOLDER']) : '' }}";
    const deleteTemplate = "{{ Route::has($deleteRouteName) ? route($deleteRouteName, ['arsip' => 'ARSIP_ID_PLACEHOLDER', 'lampiran' => 'LAMP_ID_PLACEHOLDER']) : '' }}";
    const csrf = "{{ csrf_token() }}";

    const form = modal.querySelector('#formUploadLampiran');
    const fileEl = modal.querySelector('#lampiranFile');
    const previewList = modal.querySelector('#lampiranPreviewList');
    const listWrap = modal.querySelector('#lampiranListWrap');
    const countBadge = modal.querySelector('#lampiranCountBadge');
    const infoEl = modal.querySelector('#lampiranArsipInfo');

    let currentArsipId = null;

    function fmtSize(b) {
        if (b < 1024) return b + ' B';
        if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB';
        return (b/1024/1024).toFixed(2) + ' MB';
    }

    function escapeHtml(s) {
        const div = document.createElement('div');
        div.textContent = s == null ? '' : String(s);
        return div.innerHTML;
    }

    function renderList(items) {
        const arr = items || [];
        countBadge.textContent = arr.length;
        if (arr.length === 0) {
            listWrap.innerHTML = `
                <div class="text-center text-muted py-4 lampiran-empty">
                    <i class="bi bi-inbox fs-3 opacity-50"></i>
                    <div class="small mt-2">Belum ada lampiran</div>
                </div>`;
            return;
        }
        listWrap.innerHTML = arr.map(it => {
            const delUrl = deleteTemplate
                .replace('ARSIP_ID_PLACEHOLDER', currentArsipId)
                .replace('LAMP_ID_PLACEHOLDER', it.id);
            const ket = it.keterangan ? ` · ${escapeHtml(it.keterangan)}` : '';
            return `<div class="lampiran-item" data-id="${it.id}">
                <div class="lampiran-icon"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="lampiran-meta">
                        <div class="name text-truncate">${escapeHtml(it.original_name)}</div>
                        <div class="sub">${fmtSize(it.file_size)} · ${escapeHtml(it.uploaded_at || '')}${ket}</div>
                    </div>
                </div>
                <div class="lampiran-actions">
                    <a href="${it.url}" target="_blank" title="Lihat PDF"><i class="bi bi-eye"></i></a>
                    ${delUrl ? `<button type="button" class="lampiran-del" data-del-url="${delUrl}" title="Hapus"><i class="bi bi-trash"></i></button>` : ''}
                </div>
            </div>`;
        }).join('');
    }

    function fetchList() {
        if (!listTemplate || !currentArsipId) return;
        const url = listTemplate.replace('ARSIP_ID_PLACEHOLDER', currentArsipId);
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(j => renderList(j.data || []))
            .catch(() => renderList([]));
    }

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        currentArsipId = trigger.getAttribute('data-arsip-id');
        const noReg = trigger.getAttribute('data-arsip-noreg') || '';
        form.action = uploadTemplate.replace('ARSIP_ID_PLACEHOLDER', currentArsipId);
        infoEl.innerHTML = 'Pengajuan: <b>' + escapeHtml(noReg || '#' + currentArsipId) + '</b>';
        form.reset();
        previewList.classList.add('d-none');
        previewList.innerHTML = '';
        fetchList();
    });

    fileEl?.addEventListener('change', function () {
        const files = Array.from(fileEl.files || []);
        if (!files.length) { previewList.classList.add('d-none'); previewList.innerHTML = ''; return; }
        previewList.innerHTML = files.map(f => `
            <div class="lampiran-preview-item">
                <i class="bi bi-file-earmark-pdf-fill"></i>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-bold text-truncate">${escapeHtml(f.name)}</div>
                    <div class="text-muted" style="font-size:0.65rem;">${fmtSize(f.size)}</div>
                </div>
            </div>`).join('');
        previewList.classList.remove('d-none');
    });

    // Delete (delegation)
    listWrap.addEventListener('click', function (e) {
        const btn = e.target.closest('.lampiran-del');
        if (!btn) return;
        if (!confirm('Hapus lampiran ini?')) return;
        const url = btn.getAttribute('data-del-url');
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-HTTP-Method-Override': 'DELETE' },
            body: new URLSearchParams({ _method: 'DELETE' })
        }).then(r => r.json()).then(j => fetchList());
    });

    // Submit (AJAX)
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(form);
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';
        fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json().then(j => ({ ok: r.ok, data: j })))
        .then(({ ok, data }) => {
            if (!ok) {
                alert(data.message || 'Gagal upload');
            } else {
                form.reset();
                previewList.classList.add('d-none');
                previewList.innerHTML = '';
                fetchList();
            }
        })
        .catch(() => alert('Gagal upload (network)'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload';
        });
    });
})();
</script>
@endpush
