{{-- MODAL CATATAN PERSONAL --}}
<style>
    #modalNotes .modal-content { border:none; border-radius:18px; overflow:hidden; }
    #modalNotes .modal-header-custom { padding:1.1rem 1.4rem; background:linear-gradient(135deg,#f59e0b,#d97706); color:white; }

    #modalNotes .add-form { background:#fffbeb; border:2px solid #fde68a; border-radius:14px; padding:14px; }
    #modalNotes .add-form textarea { border:2px solid #fde68a; border-radius:10px; padding:10px 12px; font-size:0.92rem; background:white; min-height:80px; resize:vertical; width:100%; transition:all .2s; }
    #modalNotes .add-form textarea:focus { outline:none; border-color:#d97706; box-shadow:0 0 0 4px rgba(217,119,6,0.15); }
    #modalNotes .add-form .submit-btn { background:linear-gradient(135deg,#f59e0b,#d97706); color:white; border:none; padding:8px 18px; border-radius:10px; font-weight:800; font-size:0.85rem; box-shadow:0 4px 12px rgba(217,119,6,0.25); display:inline-flex; align-items:center; gap:6px; }
    #modalNotes .add-form .submit-btn:hover { transform:translateY(-1px); }
    #modalNotes .add-form .char-count { font-size:0.7rem; color:#94a3b8; }

    #modalNotes .note-card { background:white; border:1px solid #e2e8f0; border-radius:13px; padding:12px 14px; margin-bottom:8px; transition:all .15s; }
    #modalNotes .note-card.mine { background:linear-gradient(135deg,#fffbeb,#fef3c7); border-color:#fbbf24; }
    #modalNotes .note-card:hover { border-color:#fcd34d; }

    #modalNotes .note-head { display:flex; align-items:center; gap:10px; margin-bottom:6px; }
    #modalNotes .note-head .avatar { width:32px; height:32px; border-radius:9px; background:#e0e7ff; color:#3730a3; font-weight:800; display:flex; align-items:center; justify-content:center; font-size:0.85rem; flex-shrink:0; }
    #modalNotes .note-card.mine .note-head .avatar { background:linear-gradient(135deg,#f59e0b,#d97706); color:white; }
    #modalNotes .note-head .author { font-weight:800; color:#0f172a; font-size:0.85rem; line-height:1.2; }
    #modalNotes .note-head .meta { color:#64748b; font-size:0.7rem; }
    #modalNotes .note-head .role-pill { font-size:0.55rem; font-weight:800; padding:1px 6px; border-radius:5px; background:#e0e7ff; color:#3730a3; letter-spacing:0.04em; text-transform:uppercase; }
    #modalNotes .note-head .actions { margin-left:auto; display:flex; gap:4px; }
    #modalNotes .note-head .btn-icon { background:transparent; border:none; padding:4px 6px; border-radius:7px; color:#64748b; font-size:0.85rem; }
    #modalNotes .note-head .btn-icon:hover { background:#f1f5f9; color:#0f172a; }
    #modalNotes .note-head .btn-icon.danger:hover { background:#fef2f2; color:#dc2626; }

    #modalNotes .note-body { color:#1e293b; font-size:0.9rem; line-height:1.55; white-space:pre-wrap; word-wrap:break-word; padding-left:42px; }

    #modalNotes .empty { padding:1.5rem; text-align:center; color:#94a3b8; font-size:0.85rem; background:#f8fafc; border:1px dashed #e2e8f0; border-radius:12px; }

    #modalNotes .section-lbl { font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.07em; color:#64748b; margin:18px 0 8px; display:flex; align-items:center; gap:6px; }

    #modalNotes .pdf-hint {
        background: linear-gradient(135deg,#ecfeff,#cffafe);
        border: 1px solid #67e8f9;
        color: #155e75;
        padding: 10px 14px;
        border-radius: 11px;
        font-size: 0.78rem;
        display: flex; align-items: start; gap: 8px;
        margin-bottom: 14px;
    }

    #modalNotes .edit-form { padding-left:42px; margin-top:6px; }
    #modalNotes .edit-form textarea { border:2px solid #fcd34d; border-radius:9px; padding:8px 10px; font-size:0.88rem; width:100%; min-height:70px; }
    #modalNotes .edit-form .row-act { display:flex; gap:6px; margin-top:6px; }
    #modalNotes .edit-form .row-act button { padding:5px 12px; border-radius:8px; font-size:0.75rem; font-weight:700; border:none; }
    #modalNotes .edit-form .save-btn { background:#10b981; color:white; }
    #modalNotes .edit-form .cancel-btn { background:#e2e8f0; color:#475569; }
</style>

<div class="modal fade" id="modalNotes" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom d-flex align-items-center gap-2">
                <i class="bi bi-journal-text fs-5"></i>
                <h5 class="modal-title fw-bold mb-0">Catatan Personal
                    <span class="badge bg-white bg-opacity-25 ms-2" id="noteNoReg" style="font-family:monospace;">—</span>
                </h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                <div class="pdf-hint">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <div>Catatan ini akan <b>ter-print di PDF dokumen</b> arsip ini, lengkap dengan nama penulis &amp; waktu. Gunakan untuk komentar review, klarifikasi, atau context tambahan.</div>
                </div>

                <div class="add-form">
                    <div class="section-lbl mt-0"><i class="bi bi-pencil-fill text-warning"></i> Tulis catatan baru</div>
                    <textarea id="noteInput" placeholder="Contoh: Mohon dicek nilai loss-nya, ini sudah disetujui Pak Anton lewat WA tgl 21 Juni 2026." maxlength="2000"></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="char-count"><span id="noteCharCount">0</span>/2000</span>
                        <button type="button" class="submit-btn" id="noteSubmitBtn">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Catatan
                        </button>
                    </div>
                </div>

                <div class="section-lbl"><i class="bi bi-list-ul"></i> Semua catatan (<span id="noteCount">0</span>)</div>
                <div id="notesList">
                    <div class="empty">Memuat...</div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Selesai</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    let currentArsipId = null;

    const $modal     = document.getElementById('modalNotes');
    const $list      = document.getElementById('notesList');
    const $input     = document.getElementById('noteInput');
    const $count     = document.getElementById('noteCount');
    const $charCount = document.getElementById('noteCharCount');
    const $submit    = document.getElementById('noteSubmitBtn');
    const $noReg     = document.getElementById('noteNoReg');

    const NOTES_BASE = "{{ url('arsip') }}";

    function escapeHtml(s) {
        return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    function renderNotes(notes) {
        $count.textContent = notes.length;
        if (!notes.length) {
            $list.innerHTML = '<div class="empty"><i class="bi bi-chat-square-text d-block mb-2" style="font-size:2rem;opacity:.4;"></i>Belum ada catatan. Tulis yang pertama!</div>';
            return;
        }
        $list.innerHTML = notes.map(n => `
            <div class="note-card ${n.is_mine ? 'mine' : ''}">
                <div class="note-head">
                    <div class="avatar">${escapeHtml((n.author_name||'?').charAt(0).toUpperCase())}</div>
                    <div>
                        <div class="author">${escapeHtml(n.author_name)} <span class="role-pill">${escapeHtml(n.author_role)}</span></div>
                        <div class="meta">${escapeHtml(n.author_dept||'')} · ${escapeHtml(n.created_at)}${n.created_at !== n.updated_at ? ' · (diedit)' : ''}</div>
                    </div>
                    ${n.is_mine ? `
                        <div class="actions">
                            <button type="button" class="btn-icon btn-edit-note" data-id="${n.id}" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                            <button type="button" class="btn-icon danger btn-del-note" data-id="${n.id}" title="Hapus"><i class="bi bi-trash3-fill"></i></button>
                        </div>
                    ` : ''}
                </div>
                <div class="note-body" data-id="${n.id}">${escapeHtml(n.note)}</div>
            </div>
        `).join('');

        $list.querySelectorAll('.btn-edit-note').forEach(b => b.addEventListener('click', () => beginEdit(parseInt(b.dataset.id,10))));
        $list.querySelectorAll('.btn-del-note').forEach(b => b.addEventListener('click', () => deleteNote(parseInt(b.dataset.id,10))));
    }

    function loadNotes() {
        if (!currentArsipId) return;
        $list.innerHTML = '<div class="empty">Memuat...</div>';
        fetch(NOTES_BASE + '/' + currentArsipId + '/notes', { headers: {'Accept':'application/json'} })
            .then(r => r.json()).then(d => renderNotes(d.notes || []));
    }

    function addNote() {
        const text = $input.value.trim();
        if (!text) { alert('Isi catatan dulu.'); return; }
        if (!currentArsipId) return;
        const fd = new FormData();
        fd.append('note', text);
        fd.append('_token', csrf);
        fetch(NOTES_BASE + '/' + currentArsipId + '/notes', {
            method:'POST', body:fd,
            headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
        })
        .then(r => { if (!r.ok) throw new Error('fail'); return r.json(); })
        .then(() => { $input.value=''; $charCount.textContent='0'; loadNotes(); })
        .catch(() => alert('Gagal menyimpan catatan.'));
    }

    function deleteNote(id) {
        if (!confirm('Hapus catatan ini?')) return;
        const fd = new FormData();
        fd.append('_token', csrf);
        fd.append('_method', 'DELETE');
        fetch(NOTES_BASE + '/' + currentArsipId + '/notes/' + id, {
            method:'POST', body:fd,
            headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
        })
        .then(() => loadNotes())
        .catch(() => alert('Gagal hapus.'));
    }

    function beginEdit(id) {
        const card = $list.querySelector(`.note-body[data-id="${id}"]`)?.closest('.note-card');
        if (!card) return;
        const body = card.querySelector('.note-body');
        const currentText = body.textContent;
        if (card.querySelector('.edit-form')) return;
        body.style.display = 'none';
        const form = document.createElement('div');
        form.className = 'edit-form';
        form.innerHTML = `
            <textarea>${escapeHtml(currentText)}</textarea>
            <div class="row-act">
                <button type="button" class="save-btn"><i class="bi bi-check-lg"></i> Simpan</button>
                <button type="button" class="cancel-btn">Batal</button>
            </div>
        `;
        card.appendChild(form);
        form.querySelector('textarea').focus();
        form.querySelector('.cancel-btn').addEventListener('click', () => { form.remove(); body.style.display=''; });
        form.querySelector('.save-btn').addEventListener('click', () => {
            const newText = form.querySelector('textarea').value.trim();
            if (!newText) { alert('Catatan tidak boleh kosong.'); return; }
            const fd = new FormData();
            fd.append('note', newText);
            fd.append('_token', csrf);
            fd.append('_method', 'PUT');
            fetch(NOTES_BASE + '/' + currentArsipId + '/notes/' + id, {
                method:'POST', body:fd,
                headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
            })
            .then(() => loadNotes())
            .catch(() => alert('Gagal update.'));
        });
    }

    $input.addEventListener('input', () => { $charCount.textContent = $input.value.length; });
    $submit.addEventListener('click', addNote);

    // Wire up tombol Notes di tabel
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-notes');
        if (!btn) return;
        currentArsipId = parseInt(btn.dataset.arsipId, 10);
        $noReg.textContent = btn.dataset.noReg || '—';
        $input.value = ''; $charCount.textContent = '0';
        new bootstrap.Modal($modal).show();
        loadNotes();
    });
})();
</script>
@endpush
