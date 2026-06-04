
<div class="modal fade" id="modalProdukDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header text-white border-0 py-3" style="background: linear-gradient(135deg, #a855f7, #7c3aed);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-upc-scan fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Detail Pengajuan Produk Baru</h5>
                        <small class="text-white-50" id="pdNoReg">-</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-3">
                                <div class="row g-2 small">
                                    <div class="col-6"><span class="text-muted">Pengaju</span><div class="fw-bold" id="pdPengaju">-</div></div>
                                    <div class="col-6"><span class="text-muted">Dept / Unit</span><div class="fw-bold" id="pdDeptUnit">-</div></div>
                                    <div class="col-6"><span class="text-muted"><i class="bi bi-calendar-plus me-1"></i>Tanggal Dibuat</span><div class="fw-bold" id="pdCreated">-</div></div>
                                    <div class="col-6"><span class="text-muted"><i class="bi bi-clock-history me-1"></i>Terakhir Diubah</span><div class="fw-bold" id="pdUpdated">-</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-3 d-flex flex-column justify-content-center">
                                <span class="text-muted small">Status Proses</span>
                                <h4 class="fw-extrabold mb-0" id="pdStatus">-</h4>
                            </div>
                        </div>
                    </div>
                </div>

                
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-box-seam me-2 text-primary"></i>Daftar Produk</h6>
                <div id="pdItems" class="d-flex flex-column gap-3 mb-4"></div>

                
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-journal-text me-2 text-info"></i>Log Perubahan</h6>
                <div id="pdLogs" class="d-flex flex-column gap-2"></div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
(function () {
    if (window.__produkDetailBound) return;
    window.__produkDetailBound = true;

    const PD_BASE = <?php echo json_encode($detailBase, 15, 512) ?>;

    function statusBadge(st) {
        const map = { 'Done': 'success', 'Process': 'warning', 'Review': 'info', 'Pending': 'secondary', 'Void': 'danger', 'Partial Done': 'primary' };
        const c = map[st] || 'secondary';
        return `<span class="badge bg-${c} bg-opacity-10 text-${c} border border-${c} border-opacity-25 rounded-pill px-3 py-1">${st || '-'}</span>`;
    }
    function approvalBadge(st) {
        const c = (st === 'Done') ? 'success' : 'warning';
        return `<span class="badge bg-${c} bg-opacity-10 text-${c} border border-${c} border-opacity-25 fw-bold">${(st || 'Waiting List').toUpperCase()}</span>`;
    }
    function esc(s) { return (s == null ? '' : String(s)).replace(/[&<>"]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m])); }

    $(document).on('click', '.btn-detail-produk', function () {
        const id = $(this).data('id');
        $('#pdItems').html('<div class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Memuat...</div>');
        $('#pdLogs').empty();

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalProdukDetail'));
        modal.show();

        $.ajax({
            url: PD_BASE + '/' + id + '/produk-detail',
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function (res) {
                const a = res.arsip || {};
                $('#pdNoReg').text(a.no_registrasi || '-');
                $('#pdPengaju').text(a.pengaju || '-');
                $('#pdDeptUnit').text((a.department || '-') + ' / ' + (a.unit || '-'));
                $('#pdCreated').text(a.created_at || '-');
                $('#pdUpdated').html((a.updated_at || '-') + (a.editor ? ` <span class="text-muted">oleh ${esc(a.editor)}</span>` : ''));
                $('#pdStatus').html(statusBadge(a.ket_process));

                // ITEMS
                let itemsHtml = '';
                (res.items || []).forEach((it, i) => {
                    const bcId = 'pdBc' + i;
                    itemsHtml += `
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3 text-center border-end">
                                    <svg id="${bcId}" class="pd-barcode" data-code="${esc(it.barcode || '')}"></svg>
                                    <div class="small fw-bold font-monospace text-dark">${esc(it.barcode || '-')}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-extrabold text-dark">${esc(it.product_name || '-')}</div>
                                    <div class="small text-muted font-monospace mb-1">${esc(it.product_code || '(tanpa kode)')}</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        ${it.tipe_produk ? `<span class="badge bg-light text-dark border">${esc(it.tipe_produk)}</span>` : ''}
                                        ${it.kategori ? `<span class="badge bg-light text-secondary border">${esc(it.kategori)}</span>` : ''}
                                        ${it.satuan ? `<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">${esc(it.satuan)}</span>` : ''}
                                    </div>
                                    ${it.keterangan ? `<div class="small text-muted mt-1 fst-italic">"${esc(it.keterangan)}"</div>` : ''}
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <div class="mb-2">${approvalBadge(it.status_approval)}</div>
                                    <div class="text-muted" style="font-size:0.7rem;"><i class="bi bi-calendar-plus me-1"></i>${esc(it.created_at || '-')}</div>
                                    <div class="text-muted" style="font-size:0.7rem;"><i class="bi bi-pencil me-1"></i>${esc(it.updated_at || '-')}${it.editor ? ' · ' + esc(it.editor) : ''}</div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
                $('#pdItems').html(itemsHtml || '<div class="text-muted text-center py-3">Tidak ada produk.</div>');

                // Render barcodes
                document.querySelectorAll('#pdItems .pd-barcode').forEach(el => {
                    const code = el.getAttribute('data-code');
                    if (code) {
                        try { JsBarcode(el, code, { format: 'CODE128', width: 1.6, height: 45, fontSize: 12, margin: 4 }); }
                        catch (e) { /* ignore */ }
                    }
                });

                // LOGS
                let logsHtml = '';
                (res.logs || []).forEach(l => {
                    const actColor = l.action === 'created' ? 'success' : (l.action === 'deleted' ? 'danger' : 'primary');
                    let changes = '';
                    if (l.changes && typeof l.changes === 'object') {
                        const keys = Object.keys(l.changes).filter(k => !['updated_at','created_at'].includes(k));
                        changes = keys.slice(0, 6).map(k => `<span class="badge bg-light text-secondary border me-1" style="font-size:0.6rem;">${esc(k)}</span>`).join('');
                    }
                    logsHtml += `
                    <div class="d-flex gap-2 align-items-start p-2 rounded-3" style="background:#fff; border:1px solid #f1f5f9;">
                        <span class="badge bg-${actColor} bg-opacity-10 text-${actColor} text-uppercase" style="font-size:0.6rem;">${esc(l.action)}</span>
                        <div class="flex-grow-1">
                            <div class="small"><span class="fw-bold">${esc(l.user)}</span> <span class="text-muted">· ${esc(l.created_at)}</span></div>
                            ${changes ? `<div class="mt-1">${changes}</div>` : ''}
                        </div>
                    </div>`;
                });
                $('#pdLogs').html(logsHtml || '<div class="text-muted small text-center py-2">Belum ada riwayat perubahan.</div>');
            },
            error: function () {
                $('#pdItems').html('<div class="text-danger text-center py-3">Gagal memuat detail.</div>');
            }
        });
    });
})();
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\e_arsip\resources\views/partials/_produk_detail_modal.blade.php ENDPATH**/ ?>