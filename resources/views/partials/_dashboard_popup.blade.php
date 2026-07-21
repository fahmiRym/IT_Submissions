{{-- Shared Dashboard Popup Modal
     Pasang <button>/<a> dengan class .dashboard-stat-trigger dan data attributes:
       data-popup-title, data-popup-status, data-popup-jenis, data-popup-dept,
       data-popup-manager, data-popup-unit, data-popup-kategori, data-popup-start, data-popup-end
     Hanya yang ada nilainya yang dikirim.
--}}
<style>
    /* === Stat-card hover affordance === */
    .dashboard-stat-trigger {
        cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease;
        position: relative;
    }
    .dashboard-stat-trigger:hover {
        transform: translateY(-3px);
    }
    .dashboard-stat-trigger::after {
        content: '\F138'; /* bi-chevron-right */
        font-family: 'bootstrap-icons';
        position: absolute;
        top: 8px; right: 10px;
        opacity: 0;
        transition: opacity .2s ease;
        font-size: 0.9rem;
        color: currentColor;
    }
    .dashboard-stat-trigger:hover::after { opacity: 0.6; }

    /* === Modal === */
    #dashStatPopup .modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
    }
    #dashStatPopup .modal-header-custom {
        padding: 1.1rem 1.4rem;
        background: linear-gradient(135deg, #4f46e5, #3730a3);
        color: white;
        display: flex; align-items: center; gap: 10px;
    }
    #dashStatPopup .modal-header-custom .ico {
        width: 36px; height: 36px; border-radius: 10px;
        background: rgba(255,255,255,0.18); display: inline-flex;
        align-items: center; justify-content: center; font-size: 1.1rem;
        flex-shrink: 0;
    }
    #dashStatPopup .modal-header-custom .h-title { font-weight: 800; font-size: 1.05rem; margin: 0; }
    #dashStatPopup .modal-header-custom .h-sub   { font-size: 0.75rem; opacity: 0.85; margin: 0; }

    #dashStatPopup .filter-pill {
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(255,255,255,0.18);
        color: white; font-size: 0.7rem; font-weight: 800;
        padding: 3px 9px; border-radius: 7px;
        letter-spacing: 0.04em; margin: 2px 4px 2px 0;
    }

    #dashStatPopup .search-bar {
        display: flex; gap: 8px; align-items: center;
        padding: 12px 16px; background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    #dashStatPopup .search-bar input {
        flex: 1; border: 1.5px solid #e2e8f0; border-radius: 10px;
        padding: 0.5rem 0.9rem; font-size: 0.88rem;
    }
    #dashStatPopup .search-bar input:focus {
        outline: none; border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
    }

    #dashStatPopup .table { margin: 0; }
    #dashStatPopup .table thead th {
        background: #f8fafc; color: #64748b;
        font-size: 0.68rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.06em;
        padding: 0.75rem 0.9rem; border-bottom: 2px solid #f1f5f9;
        position: sticky; top: 0; z-index: 2;
    }
    #dashStatPopup .table tbody td {
        padding: 0.7rem 0.9rem; vertical-align: middle;
        font-size: 0.83rem; color: #334155;
    }
    #dashStatPopup .table tbody tr:hover { background: #fafbff; }

    #dashStatPopup .no-reg-pill {
        background: #eef2ff; color: #3730a3;
        font-family: 'JetBrains Mono', monospace;
        padding: 2px 8px; border-radius: 6px;
        font-size: 0.75rem; font-weight: 800;
    }

    #dashStatPopup .jenis-chip, #dashStatPopup .status-chip {
        font-size: 0.65rem; font-weight: 800;
        padding: 2px 8px; border-radius: 6px;
        text-transform: uppercase; letter-spacing: 0.03em;
    }

    #dashStatPopup .pager {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 16px; border-top: 1px solid #f1f5f9;
        background: white; gap: 8px; flex-wrap: wrap;
    }
    #dashStatPopup .pager .pager-info { font-size: 0.78rem; color: #64748b; }
    #dashStatPopup .pager button {
        background: #f1f5f9; color: #475569; border: none;
        padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.78rem;
        transition: all .2s;
    }
    #dashStatPopup .pager button:hover:not(:disabled) { background: #e0e7ff; color: #3730a3; }
    #dashStatPopup .pager button:disabled { opacity: 0.4; cursor: not-allowed; }

    #dashStatPopup .pager .page-num {
        background: linear-gradient(135deg, #4f46e5, #3730a3);
        color: white; font-weight: 800;
        padding: 6px 12px; border-radius: 8px; font-size: 0.78rem;
        min-width: 36px; text-align: center;
    }

    #dashStatPopup .empty-state {
        padding: 3rem 1rem; text-align: center; color: #94a3b8;
    }
    #dashStatPopup .empty-state i { font-size: 3rem; opacity: 0.35; display: block; margin-bottom: 8px; }

    #dashStatPopup .loading {
        padding: 2rem; text-align: center; color: #64748b;
    }
    #dashStatPopup .loading .spinner-border { width: 32px; height: 32px; }

    /* Responsive — small screens get fullscreen modal */
    @media (max-width: 767.98px) {
        #dashStatPopup .modal-dialog { margin: 0; max-width: 100%; height: 100vh; }
        #dashStatPopup .modal-content { height: 100vh; border-radius: 0; }
        #dashStatPopup .modal-body { max-height: calc(100vh - 130px); overflow-y: auto; }
        #dashStatPopup .table thead th { font-size: 0.62rem; padding: 0.5rem 0.6rem; }
        #dashStatPopup .table tbody td { font-size: 0.75rem; padding: 0.55rem 0.6rem; }
        #dashStatPopup .filter-pill { font-size: 0.6rem; padding: 2px 7px; }
        #dashStatPopup .hide-mobile { display: none !important; }
    }
    @media (min-width: 768px) {
        #dashStatPopup .modal-dialog { max-width: 1080px; }
        #dashStatPopup .modal-body { max-height: 65vh; overflow-y: auto; padding: 0; }
    }
</style>

<div class="modal fade" id="dashStatPopup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom">
                <div class="ico"><i class="bi bi-table"></i></div>
                <div style="flex-grow:1;min-width:0;">
                    <p class="h-title" id="dashStatTitle">Daftar Pengajuan</p>
                    <p class="h-sub" id="dashStatSub">Memuat...</p>
                    <div id="dashStatFilterPills" style="margin-top:4px;"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="search-bar">
                <input type="text" id="dashStatSearch" placeholder="Cari no_reg, no_doc, keterangan..." autocomplete="off">
                <button type="button" class="btn btn-sm btn-light border" onclick="dashStatLoad(1)" style="border-radius:10px;">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            <div class="modal-body p-0">
                <div id="dashStatTableWrap" class="table-responsive">
                    <div class="loading"><div class="spinner-border text-primary"></div></div>
                </div>
            </div>

            <div class="pager">
                <span class="pager-info" id="dashStatInfo">—</span>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="dashStatGoto(-1)" id="dashStatPrev" disabled>
                        <i class="bi bi-chevron-left"></i> Prev
                    </button>
                    <span class="page-num" id="dashStatPage">1</span>
                    <button type="button" onclick="dashStatGoto(1)" id="dashStatNext" disabled>
                        Next <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const POPUP_URL = "{{ route('dashboard.popup') }}";
    const $modalEl  = document.getElementById('dashStatPopup');
    const $title    = document.getElementById('dashStatTitle');
    const $sub      = document.getElementById('dashStatSub');
    const $pills    = document.getElementById('dashStatFilterPills');
    const $tbl      = document.getElementById('dashStatTableWrap');
    const $search   = document.getElementById('dashStatSearch');
    const $info     = document.getElementById('dashStatInfo');
    const $page     = document.getElementById('dashStatPage');
    const $prev     = document.getElementById('dashStatPrev');
    const $next     = document.getElementById('dashStatNext');

    let currentFilters = {};
    let currentPage = 1;
    let lastPage = 1;
    let totalRows = 0;

    function esc(s) {
        return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    function statusColor(s) {
        const map = {
            'Done':        ['#dcfce7', '#15803d'],
            'Review':      ['#fef3c7', '#92400e'],
            'Process':     ['#dbeafe', '#1e40af'],
            'Pending':     ['#fef3c7', '#854d0e'],
            'Partial Done':['#e0e7ff', '#3730a3'],
            'Void':        ['#fee2e2', '#991b1b'],
        };
        return map[s] || ['#f1f5f9', '#475569'];
    }
    function jenisColor(j) {
        const map = {
            'Cancel':        ['#fef2f2','#991b1b'],
            'Adjust':        ['#ecfeff','#155e75'],
            'Mutasi_Billet': ['#eef2ff','#3730a3'],
            'Mutasi_Produk': ['#ecfdf5','#065f46'],
            'Internal_Memo': ['#fffbeb','#92400e'],
            'Bundel':        ['#fdf2f8','#9d174d'],
            'Produk_Baru':   ['#f0f9ff','#075985'],
        };
        return map[j] || ['#f1f5f9','#475569'];
    }

    function renderPills() {
        const labels = {
            status: 'Status', jenis: 'Jenis', kategori: 'Kategori',
            department_id: 'Dept', manager_id: 'Manager', unit_id: 'Unit',
            start_date: 'Sejak', end_date: 'Sampai',
        };
        const parts = [];
        for (const k in currentFilters) {
            const v = currentFilters[k];
            if (v && labels[k]) {
                parts.push(`<span class="filter-pill"><i class="bi bi-funnel-fill"></i> ${esc(labels[k])}: ${esc(v)}</span>`);
            }
        }
        $pills.innerHTML = parts.join('');
    }

    function renderRows(rows) {
        if (!rows.length) {
            $tbl.innerHTML = `<div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h6 class="fw-bold mt-2">Tidak ada data</h6>
                <p class="small text-muted">Coba ubah filter atau search keyword.</p>
            </div>`;
            return;
        }
        $tbl.innerHTML = `
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>No Registrasi</th>
                        <th class="hide-mobile">Tgl</th>
                        <th>Jenis</th>
                        <th class="hide-mobile">Dept / Unit</th>
                        <th class="hide-mobile">Pemohon</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                ${rows.map((r, i) => {
                    const [sBg, sTx] = statusColor(r.status);
                    const [jBg, jTx] = jenisColor(r.jenis_pengajuan);
                    return `
                        <tr>
                            <td>${(currentPage-1)*10 + i + 1}</td>
                            <td>
                                <span class="no-reg-pill">${esc(r.no_registrasi || '—')}</span>
                                <div class="hide-mobile" style="font-size:0.7rem;color:#94a3b8;margin-top:2px;">${esc(r.kategori || '')}</div>
                            </td>
                            <td class="hide-mobile">${esc(r.tgl_pengajuan || '—')}</td>
                            <td><span class="jenis-chip" style="background:${jBg};color:${jTx};">${esc((r.jenis_pengajuan||'').replace('_',' '))}</span></td>
                            <td class="hide-mobile">
                                <div style="font-weight:700;">${esc(r.department || '—')}</div>
                                <div style="font-size:0.7rem;color:#94a3b8;">${esc(r.unit || '')}</div>
                            </td>
                            <td class="hide-mobile">${esc(r.admin || '—')}</td>
                            <td><span class="status-chip" style="background:${sBg};color:${sTx};">${esc(r.status || '—')}</span></td>
                        </tr>
                    `;
                }).join('')}
                </tbody>
            </table>
        `;
    }

    window.dashStatLoad = function (page) {
        currentPage = page || 1;
        const params = new URLSearchParams();
        for (const k in currentFilters) {
            if (currentFilters[k]) params.set(k, currentFilters[k]);
        }
        const search = $search.value.trim();
        if (search) params.set('q', search);
        params.set('page', currentPage);

        $tbl.innerHTML = '<div class="loading"><div class="spinner-border text-primary"></div></div>';
        $info.textContent = 'Memuat...';

        fetch(POPUP_URL + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            totalRows = d.total;
            currentPage = d.current_page;
            lastPage = d.last_page;

            renderRows(d.rows);
            $page.textContent = currentPage;
            $info.textContent = `Total ${d.total} pengajuan · Halaman ${currentPage}/${lastPage}`;
            $prev.disabled = currentPage <= 1;
            $next.disabled = currentPage >= lastPage;
            $sub.textContent = `Menampilkan ${d.rows.length} dari ${d.total} pengajuan`;
        })
        .catch(() => {
            $tbl.innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-circle"></i><h6 class="fw-bold text-danger">Gagal memuat data</h6></div>';
            $info.textContent = 'Error';
        });
    };

    window.dashStatGoto = function (delta) {
        const nextP = currentPage + delta;
        if (nextP < 1 || nextP > lastPage) return;
        dashStatLoad(nextP);
    };

    // Search dengan enter atau debounce
    let searchTimer = null;
    $search.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => dashStatLoad(1), 300);
    });
    $search.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); dashStatLoad(1); }
    });

    // Wire up triggers
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.dashboard-stat-trigger');
        if (!trigger) return;
        e.preventDefault();

        currentFilters = {};
        const map = {
            'popupStatus': 'status', 'popupJenis': 'jenis',
            'popupKategori': 'kategori', 'popupDept': 'department_id',
            'popupManager': 'manager_id', 'popupUnit': 'unit_id',
            'popupStart': 'start_date', 'popupEnd': 'end_date',
        };
        for (const dk in map) {
            const v = trigger.dataset[dk];
            if (v && v.trim() !== '') currentFilters[map[dk]] = v;
        }

        $title.textContent = trigger.dataset.popupTitle || 'Daftar Pengajuan';
        $search.value = '';
        renderPills();

        new bootstrap.Modal($modalEl).show();
        dashStatLoad(1);
    });
})();
</script>
@endpush
