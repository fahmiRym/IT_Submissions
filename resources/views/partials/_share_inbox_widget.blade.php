{{--
    Share Inbox Widget — toast popup + modal detail untuk penerima share BA.
    Auto-poll tiap ~10s. Muncul saat ada share baru (unread).
    Include ini di layouts/app.blade.php (sekali saja, dalam @auth).
--}}

{{-- Audio ─── SEPARATE dari notification-sound. Sound khusus share BA (pakeet.mp3). --}}
<audio id="share-inbox-sound" src="{{ asset('audio/pakeet.mp3') }}" preload="auto"></audio>

{{-- Toast container ── bottom-right, WA-web style ── --}}
<div id="share-toast-stack"
     class="position-fixed"
     style="bottom: 20px; right: 20px; z-index: 1080; max-width: 340px;">
</div>

{{-- Modal detail share ── --}}
<div class="modal fade" id="shareInboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-share-fill me-2"></i>
                    BA Dibagikan Kepada Anda
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="shareModalBody">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2"></div>
                    Memuat detail...
                </div>
            </div>
            <div class="modal-footer bg-light">
                <a href="#" id="shareModalViewFull" target="_blank" class="btn btn-outline-primary btn-sm d-none">
                    <i class="bi bi-file-earmark-text me-1"></i>Buka BA Lengkap
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning btn-sm d-none" id="shareModalSaveNote">
                    <i class="bi bi-journal-text me-1"></i>Simpan Catatan
                </button>
                <button type="button" class="btn btn-success btn-sm d-none" id="shareModalApprove">
                    <i class="bi bi-check2-circle me-1"></i>Setujui Terpilih
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .share-toast {
        background: #fff;
        border-left: 4px solid #0d6efd;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        padding: 12px 14px;
        margin-top: 10px;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        animation: shareToastSlide 0.3s ease-out;
    }
    .share-toast:hover { transform: translateY(-2px); box-shadow: 0 6px 22px rgba(0, 0, 0, 0.18); }
    .share-toast .st-title { font-size: 0.82rem; font-weight: 700; color: #0d6efd; margin-bottom: 3px; }
    .share-toast .st-reg   { font-size: 0.75rem; color: #1e293b; font-weight: 600; }
    .share-toast .st-meta  { font-size: 0.7rem; color: #64748b; margin-top: 3px; }
    .share-toast .st-close { position: absolute; top: 6px; right: 8px; color: #94a3b8; font-size: 1rem; line-height: 1; border: 0; background: transparent; cursor: pointer; }
    .share-toast { position: relative; }

    @keyframes shareToastSlide {
        from { opacity: 0; transform: translateX(30px); }
        to   { opacity: 1; transform: translateX(0); }
    }

    #shareInboxModal .tx-row {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0;
        margin-bottom: 6px; background: #fff;
    }
    #shareInboxModal .tx-row.approved { background: #ecfdf5; border-color: #a7f3d0; }
    #shareInboxModal .tx-row code { font-size: 0.85rem; color: #1e293b; font-weight: 600; }
    #shareInboxModal .tx-row .approved-info { font-size: 0.72rem; color: #059669; font-style: italic; }
</style>

<script>
(function () {
    if (typeof jQuery === 'undefined') return;

    const UNREAD_URL   = "{{ route('arsip.shares.unread') }}";
    const DETAIL_TPL   = "{{ url('arsip/shares') }}/__ID__/detail";
    const READ_TPL     = "{{ url('arsip/shares') }}/__ID__/read";
    const NOTE_TPL     = "{{ url('arsip/shares') }}/__ID__/note";
    const APPROVE_TPL  = "{{ url('arsip/shares') }}/__ID__/approve";

    const $stack = $('#share-toast-stack');
    const $modal = $('#shareInboxModal');
    const $body  = $('#shareModalBody');
    let currentShareId = null;
    let seenShareIds = new Set();
    let firstPoll = true;

    function playShareSound() {
        const a = document.getElementById('share-inbox-sound');
        if (!a) return;
        try { a.currentTime = 0; a.play().catch(() => {}); } catch(e) {}
    }

    // ── Desktop / OS-level notification (muncul di atas aplikasi lain, spt WA Web) ──
    // Butuh user grant permission sekali. Kalau ditolak, in-app toast tetap berfungsi.
    function ensureNotifPermission() {
        if (!('Notification' in window)) return Promise.resolve('unsupported');
        if (Notification.permission === 'granted') return Promise.resolve('granted');
        if (Notification.permission === 'denied')  return Promise.resolve('denied');
        return Notification.requestPermission();
    }

    function showDesktopNotif(item) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        try {
            const n = new Notification('BA Dibagikan · ' + (item.no_registrasi || ''), {
                body: (item.jenis || 'Pengajuan') + '\nDari: ' + (item.shared_by || 'Sistem') +
                      (item.note ? '\n"' + item.note + '"' : ''),
                icon: '{{ asset('img/logo.png') }}',
                tag:  'share-' + item.share_id, // dedup per share_id
                requireInteraction: false,
            });
            n.onclick = function () {
                window.focus();                     // fokus tab
                openShareModal(item.share_id);      // buka modal
                n.close();
            };
        } catch (e) { /* ignore */ }
    }

    function renderToast(item) {
        if (seenShareIds.has(item.share_id)) return;
        seenShareIds.add(item.share_id);

        const $t = $(`
            <div class="share-toast" data-share-id="${item.share_id}">
                <button type="button" class="st-close" data-dismiss>&times;</button>
                <div class="st-title"><i class="bi bi-share-fill me-1"></i>BA Dibagikan</div>
                <div class="st-reg">${item.no_registrasi} · ${item.jenis || '—'}</div>
                <div class="st-meta">
                    Dari <b>${item.shared_by}</b> · ${item.shared_at || ''}
                    ${item.note ? `<div class="mt-1 fst-italic">"${item.note}"</div>` : ''}
                </div>
            </div>
        `);
        $t.on('click', function (e) {
            if ($(e.target).is('[data-dismiss]')) return;
            openShareModal(item.share_id);
            $t.fadeOut(200, () => $t.remove());
        });
        $t.find('[data-dismiss]').on('click', function (e) {
            e.stopPropagation();
            $t.fadeOut(200, () => $t.remove());
        });
        $stack.append($t);
        // Auto-remove setelah 12s biar tidak menumpuk
        setTimeout(() => { $t.fadeOut(400, () => $t.remove()); }, 12000);
    }

    function pollUnread() {
        $.getJSON(UNREAD_URL).done(function (res) {
            if (!res || !Array.isArray(res.items)) return;

            const newItems = res.items.filter(it => !seenShareIds.has(it.share_id));
            if (newItems.length > 0) {
                // Sound + desktop notif HANYA untuk arrival baru (bukan first-poll saat page load).
                if (!firstPoll) {
                    playShareSound();
                    newItems.forEach(showDesktopNotif); // OS-level popup (browser boleh minimized)
                }
                newItems.forEach(renderToast);          // In-app toast selalu, termasuk saat load
            }
            firstPoll = false;
        });
    }

    function openShareModal(shareId) {
        currentShareId = shareId;
        $body.html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat...</div>');
        $('#shareModalViewFull, #shareModalApprove, #shareModalSaveNote').addClass('d-none');
        $modal.modal('show');

        // Mark read (fire and forget)
        $.ajax({ url: READ_TPL.replace('__ID__', shareId), type: 'POST',
                 headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

        // Fetch detail
        $.getJSON(DETAIL_TPL.replace('__ID__', shareId)).done(function (d) {
            const approvedTxSet = new Set(
                (d.approvals || []).map(a => (a.no_transaksi || '').trim().toLowerCase())
            );
            const rows = (d.no_transaksis || []).map((tx, i) => {
                const isApproved = approvedTxSet.has(tx.trim().toLowerCase());
                const approvedBy = (d.approvals || []).find(a => (a.no_transaksi || '').trim().toLowerCase() === tx.trim().toLowerCase());
                return `
                    <div class="tx-row ${isApproved ? 'approved' : ''}">
                        <input type="checkbox" class="form-check-input tx-check" value="${tx.replace(/"/g, '&quot;')}"
                               ${isApproved ? 'checked disabled' : ''} id="tx-${i}">
                        <div class="flex-grow-1">
                            <code>${tx}</code>
                            ${isApproved && approvedBy ? `<div class="approved-info"><i class="bi bi-patch-check-fill"></i> Sudah disetujui oleh <b>${approvedBy.approved_name}</b></div>` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            $body.html(`
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="text-muted small">Nomor Registrasi</div>
                            <div class="fw-bold text-primary">${d.arsip.no_registrasi} <span class="text-muted fw-normal">· ${d.arsip.jenis_pengajuan}</span></div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Dibagikan oleh</div>
                            <div class="fw-semibold small">${d.share.shared_by}</div>
                        </div>
                    </div>
                    <div class="row g-2 small text-muted">
                        <div class="col-md-6"><b>Pemohon:</b> ${d.arsip.pemohon || '—'}</div>
                        <div class="col-md-6"><b>Dept/Unit:</b> ${d.arsip.department || '—'} / ${d.arsip.unit || '—'}</div>
                    </div>
                    ${d.share.note ? `<div class="alert alert-info small mt-2 py-2 mb-0"><i class="bi bi-info-circle me-1"></i>${d.share.note}</div>` : ''}
                </div>

                <hr>

                <div class="mb-3">
                    <label class="fw-bold small mb-2">
                        <i class="bi bi-check2-square me-1"></i>
                        Daftar No. Transaksi (centang yang disetujui)
                    </label>
                    ${rows || '<div class="text-muted small fst-italic">Tidak ada no_transaksi di BA ini.</div>'}
                </div>

                <div class="mb-2">
                    <label class="fw-bold small mb-1">Catatan Anda (opsional)</label>
                    <textarea class="form-control form-control-sm" rows="2" id="shareModalNoteContent"
                              placeholder="Contoh: sudah divalidasi, lanjut proses...">${d.share.note_content || ''}</textarea>
                </div>
            `);

            $('#shareModalViewFull').attr('href', d.view_url).removeClass('d-none');
            $('#shareModalSaveNote').removeClass('d-none');
            if ((d.no_transaksis || []).length > 0) {
                $('#shareModalApprove').removeClass('d-none');
            }
        }).fail(function () {
            $body.html('<div class="alert alert-danger">Gagal memuat detail share.</div>');
        });
    }

    // Action: simpan catatan
    $('#shareModalSaveNote').on('click', function () {
        if (!currentShareId) return;
        const content = $('#shareModalNoteContent').val();
        const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
        $.ajax({
            url: NOTE_TPL.replace('__ID__', currentShareId),
            type: 'POST',
            data: { note_content: content },
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).done(function () {
            $btn.html('<i class="bi bi-check2 me-1"></i>Tersimpan');
            setTimeout(() => $btn.prop('disabled', false).html('<i class="bi bi-journal-text me-1"></i>Simpan Catatan'), 1500);
        }).fail(function () {
            $btn.prop('disabled', false).html('<i class="bi bi-journal-text me-1"></i>Simpan Catatan');
            alert('Gagal menyimpan catatan.');
        });
    });

    // Action: approve terpilih
    $('#shareModalApprove').on('click', function () {
        if (!currentShareId) return;
        const selected = $('.tx-check:checked:not(:disabled)').map(function () { return $(this).val(); }).get();
        if (selected.length === 0) {
            alert('Centang minimal 1 no. transaksi.');
            return;
        }
        if (!confirm(`Setujui ${selected.length} no. transaksi? Approval Anda akan tampil sebagai watermark di print BA.`)) return;

        const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyetujui...');
        $.ajax({
            url: APPROVE_TPL.replace('__ID__', currentShareId),
            type: 'POST',
            data: { no_transaksis: selected },
            traditional: true,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).done(function () {
            $btn.html('<i class="bi bi-check2-all me-1"></i>Berhasil');
            setTimeout(() => { $modal.modal('hide'); }, 800);
        }).fail(function () {
            $btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i>Setujui Terpilih');
            alert('Gagal menyetujui.');
        });
    });

    // Init: minta permission notif OS (auto — muncul prompt pertama kali)
    // Prompt hanya boleh via user gesture, jadi fallback: trigger on first click di dokumen
    ensureNotifPermission().then(function (perm) {
        if (perm === 'default') {
            // Belum di-grant, hook satu-shot pada click apa saja utk request permission
            document.addEventListener('click', function once() {
                Notification.requestPermission();
                document.removeEventListener('click', once);
            }, { once: true });
        }
    });

    // Init polling — start immediate + interval 10s
    pollUnread();
    setInterval(pollUnread, 10000);

    // Expose for external trigger (misalnya klik badge notif → buka modal)
    window.openShareInboxModal = openShareModal;
})();
</script>
