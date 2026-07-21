{{-- Premium header banner untuk section Adjust (dipakai di create & edit form). --}}
<div class="adjust-banner mb-3 rounded-4 overflow-hidden shadow-sm">
    <div class="adjust-banner-bg position-relative p-3">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="adjust-banner-icon d-flex align-items-center justify-content-center rounded-3 flex-shrink-0">
                <i class="bi bi-sliders2-vertical"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h6 class="fw-bold mb-0 text-white" style="letter-spacing:0.3px;">STOCK ADJUSTMENT</h6>
                    <span class="badge bg-white text-info fw-bold" style="font-size:0.6rem;">
                        <i class="bi bi-arrow-repeat me-1"></i>SYNC ODOO
                    </span>
                    <span class="badge bg-warning text-dark fw-bold" style="font-size:0.6rem;">
                        <i class="bi bi-calculator-fill me-1"></i>VIA ACCOUNTING
                    </span>
                </div>
                <p class="mb-0 mt-1 text-white-50" style="font-size:0.72rem; line-height:1.35;">
                    Penyesuaian stok antara <b>Odoo</b> &amp; <b>fisik gudang</b>. Setelah final-approved, data dikirim otomatis ke Odoo (queue + auto-retry).
                </p>
            </div>
            <div class="adjust-banner-actions d-flex gap-2 flex-shrink-0">
                <button type="button"
                        class="btn btn-light btn-sm fw-bold rounded-pill px-3 adjust-btn-autocalc"
                        data-scope="{{ $scope ?? 'create' }}"
                        title="Hitung otomatis qty_in/qty_out dari (Fisik - Odoo)">
                    <i class="bi bi-magic me-1"></i>Auto-Calc
                </button>
            </div>
        </div>
    </div>
</div>
