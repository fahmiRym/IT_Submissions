{{-- MODAL BAGIKAN ARSIP — Superadmin only --}}
<style>
    #modalShare .modal-content { border:none; border-radius:18px; overflow:hidden; }
    #modalShare .modal-header-custom { padding:1.1rem 1.4rem; background:linear-gradient(135deg,#06b6d4,#0891b2); color:white; }

    /* Toggle target type */
    #modalShare .target-switch {
        display: flex; background: #f1f5f9; border-radius: 12px; padding: 4px;
        gap: 4px; margin-bottom: 16px;
    }
    #modalShare .target-switch button {
        flex: 1; border: none; background: transparent;
        padding: 9px 14px; font-weight: 800; font-size: 0.82rem;
        color: #64748b; border-radius: 9px; transition: all .25s;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    }
    #modalShare .target-switch button.active {
        background: white; color: #0891b2;
        box-shadow: 0 4px 12px rgba(8,145,178,0.18);
    }

    #modalShare .picker-input { border:2px solid #e2e8f0; border-radius:12px; padding:0.7rem 1rem; font-size:0.9rem; background:#f8fafc; transition:all .2s; }
    #modalShare .picker-input:focus { outline:none; border-color:#0891b2; background:white; box-shadow:0 0 0 4px rgba(8,145,178,0.12); }

    #modalShare .picker-results { max-height:240px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:12px; background:#f8fafc; }
    #modalShare .picker-results .item { display:flex; align-items:center; gap:10px; padding:10px 12px; border-bottom:1px solid #e2e8f0; cursor:pointer; transition:background .15s; }
    #modalShare .picker-results .item:last-child { border-bottom:none; }
    #modalShare .picker-results .item:hover { background:white; }
    #modalShare .picker-results .avatar { width:34px; height:34px; border-radius:10px; background:#dbeafe; color:#1d4ed8; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.9rem; }
    #modalShare .picker-results .meta { font-size:0.7rem; color:#64748b; font-family:monospace; }
    #modalShare .picker-results .add-btn { margin-left:auto; background:#0891b2; color:white; border:none; padding:5px 10px; border-radius:8px; font-size:0.7rem; font-weight:700; }
    #modalShare .picker-results .add-btn:hover { background:#0e7490; }
    #modalShare .empty { padding:14px; text-align:center; color:#94a3b8; font-size:0.82rem; }

    /* Role grid */
    #modalShare .role-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
    #modalShare .role-card {
        position: relative; display: flex; align-items: center; gap: 12px;
        padding: 12px 14px; background: white; border: 2px solid #e2e8f0;
        border-radius: 13px; cursor: pointer; transition: all .2s;
    }
    #modalShare .role-card:hover { border-color: #c4b5fd; transform: translateY(-1px); box-shadow: 0 6px 14px rgba(0,0,0,0.06); }
    #modalShare .role-card .ricon { width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
    #modalShare .role-card .rname { font-weight: 800; font-size: 0.88rem; color: #0f172a; line-height: 1.2; }
    #modalShare .role-card .rmeta { font-size: 0.7rem; color: #64748b; margin-top: 1px; }

    #modalShare .shares-list .row-share { display:flex; align-items:center; gap:10px; padding:10px 12px; background:#f0fdfa; border:1px solid #99f6e4; border-radius:11px; margin-bottom:6px; }
    #modalShare .shares-list .row-share.role-share { background: #ede9fe; border-color: #c4b5fd; }
    #modalShare .shares-list .avatar-x { width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg,#06b6d4,#0891b2); color:white; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.9rem; }
    #modalShare .shares-list .avatar-x.role { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }
    #modalShare .shares-list .name { font-weight:800; color:#0f172a; font-size:0.85rem; }
    #modalShare .shares-list .meta-line { font-size:0.7rem; color:#475569; }
    #modalShare .shares-list .btn-revoke { margin-left:auto; background:#fee2e2; color:#dc2626; border:none; width:32px; height:32px; border-radius:8px; }
    #modalShare .shares-list .btn-revoke:hover { background:#fecaca; }

    #modalShare .section-lbl { font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.07em; color:#64748b; margin:18px 0 8px; display:flex; align-items:center; gap:6px; }
    #modalShare .role-badge { font-size:0.6rem; font-weight:800; padding:2px 7px; border-radius:6px; letter-spacing:0.03em; background:#e0e7ff; color:#3730a3; text-transform:uppercase; }

    #modalShare .denied-banner {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border: 1px solid #fbbf24; color: #92400e;
        padding: 12px 14px; border-radius: 12px;
        font-size: 0.85rem; display: flex; align-items: start; gap: 10px;
    }
</style>

<div class="modal fade" id="modalShare" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom d-flex align-items-center gap-2">
                <i class="bi bi-share-fill fs-5"></i>
                <h5 class="modal-title fw-bold mb-0">Bagikan Pengajuan
                    <span class="badge bg-white bg-opacity-25 ms-2" id="shareNoReg" style="font-family:monospace;">—</span>
                </h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                <div id="shareManageWrap">
                    <div class="alert border-0 rounded-3 d-flex align-items-start gap-2 small mb-3" style="background:#ecfeff;color:#155e75;">
                        <i class="bi bi-info-circle-fill mt-1"></i>
                        <div>Pilih target — <b>User spesifik</b> untuk 1 orang, atau <b>Role</b> untuk semua orang dengan role tsb (mis. semua "accounting").</div>
                    </div>

                    {{-- Target type toggle --}}
                    <div class="target-switch">
                        <button type="button" class="active" data-mode="user">
                            <i class="bi bi-person-fill"></i> User Spesifik
                        </button>
                        <button type="button" data-mode="role">
                            <i class="bi bi-people-fill"></i> Role / Posisi
                        </button>
                    </div>

                    {{-- Note input (shared) --}}
                    <input type="text" id="shareNoteInput" class="picker-input w-100 mb-3" placeholder="Catatan (opsional) — kenapa di-share?" maxlength="255">

                    {{-- Mode: USER --}}
                    <div id="modeUser">
                        <div class="section-lbl"><i class="bi bi-search"></i> Cari user</div>
                        <input type="text" id="sharePickerInput" class="picker-input w-100 mb-2" placeholder="Ketik nama, username, atau NIK...">
                        <div class="picker-results" id="sharePickerResults">
                            <div class="empty">Mulai ketik untuk mencari user...</div>
                        </div>
                    </div>

                    {{-- Mode: ROLE --}}
                    <div id="modeRole" style="display:none;">
                        <div class="section-lbl"><i class="bi bi-people-fill"></i> Pilih role yang dishare</div>
                        <div class="role-grid" id="roleGrid">
                            {{-- diisi via JS dari endpoint search --}}
                        </div>
                    </div>
                </div>

                <div id="shareViewOnly" style="display:none;">
                    <div class="denied-banner">
                        <i class="bi bi-shield-exclamation"></i>
                        <div>Hanya <b>Superadmin</b> yang boleh memodifikasi share. Anda dapat melihat daftar share saat ini di bawah.</div>
                    </div>
                </div>

                <div class="section-lbl"><i class="bi bi-people-fill"></i> Sedang Dibagikan Ke</div>
                <div class="shares-list" id="sharesList">
                    <div class="empty" style="background:#f8fafc;border:1px dashed #e2e8f0;border-radius:11px;">Belum ada akses yang dibagikan.</div>
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
    let canManage = false;

    const $modal      = document.getElementById('modalShare');
    const $picker     = document.getElementById('sharePickerInput');
    const $note       = document.getElementById('shareNoteInput');
    const $results    = document.getElementById('sharePickerResults');
    const $roleGrid   = document.getElementById('roleGrid');
    const $sharesList = document.getElementById('sharesList');
    const $noReg      = document.getElementById('shareNoReg');
    const $modeUser   = document.getElementById('modeUser');
    const $modeRole   = document.getElementById('modeRole');
    const $manageWrap = document.getElementById('shareManageWrap');
    const $viewOnly   = document.getElementById('shareViewOnly');

    function escapeHtml(s) {
        return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    function renderUsers(users) {
        if (!users.length) {
            $results.innerHTML = '<div class="empty">Tidak ada user cocok.</div>';
            return;
        }
        $results.innerHTML = users.map(u => `
            <div class="item" data-id="${u.id}">
                <div class="avatar">${escapeHtml((u.name || '?').charAt(0).toUpperCase())}</div>
                <div>
                    <div class="fw-bold" style="font-size:0.85rem;color:#0f172a;">${escapeHtml(u.name)}</div>
                    <div class="meta">${escapeHtml(u.username)}${u.department ? ' · ' + escapeHtml(u.department) : ''}</div>
                </div>
                <span class="role-badge ms-2">${escapeHtml(u.role)}</span>
                <button type="button" class="add-btn">+ Tambah</button>
            </div>
        `).join('');
        $results.querySelectorAll('.item').forEach(el => {
            el.addEventListener('click', () => addShare({ target_type: 'user', user_id: parseInt(el.dataset.id, 10) }));
        });
    }

    function renderRoles(roles) {
        if (!roles.length) {
            $roleGrid.innerHTML = '<div class="empty">Tidak ada role tersedia.</div>';
            return;
        }
        $roleGrid.innerHTML = roles.map(r => `
            <div class="role-card" data-role="${escapeHtml(r.key)}">
                <div class="ricon" style="background:${r.color}1a;color:${r.color};">
                    <i class="${r.icon}"></i>
                </div>
                <div style="flex-grow:1;min-width:0;">
                    <div class="rname">${escapeHtml(r.label)}</div>
                    <div class="rmeta">${r.user_count} user aktif</div>
                </div>
                <button type="button" class="add-btn" style="background:#7c3aed;border-radius:8px;padding:5px 10px;color:white;border:none;font-size:0.7rem;font-weight:700;">+ Pilih</button>
            </div>
        `).join('');
        $roleGrid.querySelectorAll('.role-card').forEach(el => {
            el.addEventListener('click', () => addShare({ target_type: 'role', role: el.dataset.role }));
        });
    }

    function renderShares(shares) {
        if (!shares.length) {
            $sharesList.innerHTML = '<div class="empty" style="background:#f8fafc;border:1px dashed #e2e8f0;border-radius:11px;">Belum ada akses yang dibagikan.</div>';
            return;
        }
        $sharesList.innerHTML = shares.map(s => {
            const isRole = s.target_type === 'role';
            const initial = (s.display_name || '?').replace(/^Role:\s*/, '').charAt(0).toUpperCase();
            return `
                <div class="row-share ${isRole ? 'role-share' : ''}">
                    <div class="avatar-x ${isRole ? 'role' : ''}">
                        <i class="bi ${isRole ? 'bi-people-fill' : 'bi-person-fill'}"></i>
                    </div>
                    <div style="flex-grow:1;min-width:0;">
                        <div class="name">${escapeHtml(s.display_name)}</div>
                        <div class="meta-line">
                            ${escapeHtml(s.sub_text || '')}
                            ${s.note ? '<br><i class="bi bi-chat-left-quote me-1"></i><i>' + escapeHtml(s.note) + '</i>' : ''}
                            ${s.shared_by_name ? '<br><span style="opacity:.7;">oleh ' + escapeHtml(s.shared_by_name) + ' · ' + escapeHtml(s.created_at) + '</span>' : ''}
                        </div>
                    </div>
                    ${canManage ? '<button type="button" class="btn-revoke" title="Cabut akses" data-share-id="' + s.id + '"><i class="bi bi-x-lg"></i></button>' : ''}
                </div>
            `;
        }).join('');
        if (canManage) {
            $sharesList.querySelectorAll('.btn-revoke').forEach(b => {
                b.addEventListener('click', () => revokeShare(parseInt(b.dataset.shareId, 10)));
            });
        }
    }

    const SEARCH_URL = "{{ route('arsip.shares.user-search') }}";
    const SHARE_URL_BASE = "{{ url('arsip') }}";

    // Search user (debounced)
    let timer = null;
    $picker.addEventListener('input', () => {
        clearTimeout(timer);
        const q = $picker.value.trim();
        if (q.length < 1) { $results.innerHTML = '<div class="empty">Mulai ketik untuk mencari user...</div>'; return; }
        timer = setTimeout(() => {
            fetch(SEARCH_URL + "?mode=user&q=" + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json()).then(d => renderUsers(d.users || []));
        }, 220);
    });

    // Load roles when role tab opened
    function loadRoles() {
        fetch(SEARCH_URL + "?mode=role", {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json()).then(d => renderRoles(d.roles || []));
    }

    // Toggle mode
    document.querySelectorAll('.target-switch button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.target-switch button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const mode = btn.dataset.mode;
            if (mode === 'user') { $modeUser.style.display = 'block'; $modeRole.style.display = 'none'; }
            else { $modeUser.style.display = 'none'; $modeRole.style.display = 'block'; loadRoles(); }
        });
    });

    function loadShares() {
        if (!currentArsipId) return;
        const url = SHARE_URL_BASE + "/" + currentArsipId + "/shares";
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                canManage = !!d.can_manage;
                $manageWrap.style.display = canManage ? 'block' : 'none';
                $viewOnly.style.display = canManage ? 'none' : 'block';
                renderShares(d.shares || []);
            });
    }

    function addShare(payload) {
        if (!currentArsipId || !canManage) return;
        const url = SHARE_URL_BASE + "/" + currentArsipId + "/shares";
        const fd = new FormData();
        fd.append('target_type', payload.target_type);
        if (payload.user_id) fd.append('user_id', payload.user_id);
        if (payload.role) fd.append('role', payload.role);
        if ($note.value.trim()) fd.append('note', $note.value.trim());
        fd.append('_token', csrf);
        fetch(url, { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => { if (!r.ok) throw new Error('share failed'); return r.text(); })
            .then(() => {
                $picker.value = ''; $note.value = '';
                $results.innerHTML = '<div class="empty">Target berhasil ditambah. Bisa tambah lagi atau tutup modal.</div>';
                loadShares();
            })
            .catch(() => alert('Gagal membagikan. Coba refresh halaman.'));
    }

    function revokeShare(shareId) {
        if (!currentArsipId || !canManage || !confirm('Cabut akses share ini?')) return;
        const url = SHARE_URL_BASE + "/" + currentArsipId + "/shares/" + shareId;
        const fd = new FormData();
        fd.append('_token', csrf);
        fd.append('_method', 'DELETE');
        fetch(url, { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(() => loadShares())
            .catch(() => alert('Gagal mencabut share.'));
    }

    // Wire up tombol Share di tabel
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-share');
        if (!btn) return;
        currentArsipId = parseInt(btn.dataset.arsipId, 10);
        $noReg.textContent = btn.dataset.noReg || '—';
        $picker.value = ''; $note.value = '';
        // Reset mode ke User
        document.querySelectorAll('.target-switch button').forEach(b => b.classList.remove('active'));
        const userBtn = document.querySelector('.target-switch button[data-mode="user"]');
        if (userBtn) userBtn.classList.add('active');
        $modeUser.style.display = 'block'; $modeRole.style.display = 'none';
        $results.innerHTML = '<div class="empty">Mulai ketik untuk mencari user...</div>';
        $roleGrid.innerHTML = '';
        $sharesList.innerHTML = '<div class="empty">Memuat...</div>';
        new bootstrap.Modal($modal).show();
        loadShares();
    });
})();
</script>
@endpush
