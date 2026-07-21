{{-- Shared JS template builder untuk row Adjust (create + edit).
     Include 1x di tiap index.blade.php (admin & superadmin). Membuat window.buildAdjustRow().
     Kolom: Kode | Nama | Lot | Lokasi | Odoo | Fisik | Selisih(auto) | Adjus(auto) | x --}}
@php $adjustLocations = \App\Models\ArsipAdjustItem::getLocations(); @endphp
<script>
(function () {
    const ADJUST_LOCATIONS = @json($adjustLocations);

    function buildLocationOptions(selected) {
        let opts = '<option value="">-- Lokasi --</option>';
        ADJUST_LOCATIONS.forEach(loc => {
            const sel = (loc === selected) ? 'selected' : '';
            opts += `<option value="${loc}" ${sel}>${loc}</option>`;
        });
        return opts;
    }

    /**
     * Build 1 row Adjust dengan kolom baru (Kode|Nama|Lot|Lokasi|Odoo|Fisik|Selisih|Adjus).
     * @param {string} namePrefix - 'adjust' (admin create) atau 'detail_barang[adjust]' (superadmin create / edit)
     * @param {string|number} idx - row index unik
     * @param {object} data - optional preset values for edit mode
     * @returns {string} HTML <tr>
     */
    window.buildAdjustRow = function (namePrefix, idx, data) {
        data = data || {};
        const safe = v => (v === null || v === undefined) ? '' : v;
        const n = `${namePrefix}[${idx}]`;
        return `
        <tr class="adjust-row">
            <td class="ps-2">
                <input type="text" name="${n}[product_code]" class="form-control form-control-sm border-0 bg-light"
                       placeholder="Kode" value="${safe(data.product_code)}" required style="min-width: 90px;">
            </td>
            <td>
                <input type="text" name="${n}[nama_produk]" class="form-control form-control-sm border-0 bg-light"
                       placeholder="Nama Barang" value="${safe(data.product_name || data.nama_produk)}" required style="min-width: 150px;">
            </td>
            <td>
                <input type="text" name="${n}[lot]" class="form-control form-control-sm border-0 bg-light"
                       placeholder="Lot" value="${safe(data.lot)}" style="min-width: 80px;">
            </td>
            <td>
                <select name="${n}[location]" class="form-select form-select-sm border-0 bg-light" style="min-width: 140px;">
                    ${buildLocationOptions(data.location || '')}
                </select>
            </td>
            <td>
                <input type="number" step="any" name="${n}[odoo]"
                       class="form-control form-control-sm border-0 bg-light text-center fw-bold adjust-input-odoo"
                       placeholder="0" value="${safe(data.odoo)}" style="min-width: 60px;">
            </td>
            <td>
                <input type="number" step="any" name="${n}[fisik]"
                       class="form-control form-control-sm border-0 bg-light text-center fw-bold adjust-input-fisik"
                       placeholder="0" value="${safe(data.fisik)}" style="min-width: 60px;">
            </td>
            <td class="text-center">
                <span class="adjust-selisih-display fw-bold" data-selisih="0">0</span>
            </td>
            <td class="text-center">
                <span class="adjust-adjus-badge badge bg-secondary" data-adjus="">-</span>
            </td>
            <td class="text-end pe-1">
                <input type="hidden" name="${n}[qty_in]" value="${safe(data.qty_in) || 0}" class="adjust-hidden-qtyin">
                <input type="hidden" name="${n}[qty_out]" value="${safe(data.qty_out) || 0}" class="adjust-hidden-qtyout">
                <button type="button" class="btn btn-link text-danger p-0 btnRemove" title="Hapus">
                    <i class="bi bi-x-circle-fill fs-6"></i>
                </button>
            </td>
        </tr>`;
    };
})();
</script>
