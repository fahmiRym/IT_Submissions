{{-- Running-totals footer untuk section Adjust. --}}
<div class="adjust-totals mt-3 p-3 rounded-3"
     data-scope="{{ $scope ?? 'create' }}"
     data-wrapper="{{ $wrapper ?? 'wrapperAdjust' }}">
    <div class="row g-2 align-items-center">
        <div class="col-6 col-md-3">
            <div class="adjust-stat">
                <div class="adjust-stat-label">ITEMS</div>
                <div class="adjust-stat-value text-primary" data-stat="count">0</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adjust-stat">
                <div class="adjust-stat-label">TOTAL IN</div>
                <div class="adjust-stat-value text-success" data-stat="in">0</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adjust-stat">
                <div class="adjust-stat-label">TOTAL OUT</div>
                <div class="adjust-stat-value text-danger" data-stat="out">0</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adjust-stat">
                <div class="adjust-stat-label">NET ADJUSTMENT</div>
                <div class="adjust-stat-value" data-stat="net">0</div>
            </div>
        </div>
    </div>
    <div class="adjust-totals-hint mt-2" data-hint>
        <i class="bi bi-info-circle me-1"></i>Net positif = stok bertambah, negatif = stok berkurang. Tombol <b>Auto-Calc</b> mengisi qty_in/qty_out berdasarkan selisih (Fisik − Odoo).
    </div>
</div>
