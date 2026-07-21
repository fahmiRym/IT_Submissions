/* =====================================================================
   adjust-enhancer.js  (v2 — kolom baru: Kode|Nama|Lot|Lokasi|Odoo|Fisik|Selisih|Adjus)
   Live totals + auto-fill qty_in/qty_out + Selisih + ADJUS badge + row color.
   Bekerja di #wrapperAdjust (create) dan #wrapperAdjustEdit (edit).
   ===================================================================== */
(function () {
    'use strict';

    const num = (v) => {
        const n = parseFloat(v);
        return Number.isFinite(n) ? n : 0;
    };

    const cellInput = (row, suffix) => row.querySelector('input[name$="[' + suffix + ']"]');

    /**
     * Hitung Selisih (Fisik - Odoo) lalu update:
     *  - Display "Selisih"
     *  - Badge "ADJUS" (IN/OUT/-)
     *  - Hidden qty_in / qty_out
     *  - Row color class
     */
    function recalcRow(row) {
        const odooEl  = cellInput(row, 'odoo');
        const fisikEl = cellInput(row, 'fisik');
        const inEl    = row.querySelector('input.adjust-hidden-qtyin') || cellInput(row, 'qty_in');
        const outEl   = row.querySelector('input.adjust-hidden-qtyout') || cellInput(row, 'qty_out');

        if (!odooEl || !fisikEl) return;

        const odoo  = num(odooEl.value);
        const fisik = num(fisikEl.value);
        const diff  = fisik - odoo;
        const absDiff = Math.abs(diff);

        // Update Selisih display
        const sel = row.querySelector('.adjust-selisih-display');
        if (sel) {
            sel.textContent = absDiff === 0 ? '0' : (Number.isInteger(absDiff) ? absDiff.toLocaleString('id-ID') : absDiff.toFixed(2));
            sel.dataset.selisih = absDiff;
            sel.classList.remove('text-success', 'text-danger', 'text-muted');
            if (diff > 0) sel.classList.add('text-success');
            else if (diff < 0) sel.classList.add('text-danger');
            else sel.classList.add('text-muted');
        }

        // Update ADJUS badge
        const adjus = row.querySelector('.adjust-adjus-badge');
        if (adjus) {
            adjus.classList.remove('bg-success', 'bg-danger', 'bg-secondary');
            if (diff > 0) {
                adjus.textContent = 'IN';
                adjus.classList.add('bg-success');
                adjus.dataset.adjus = 'IN';
            } else if (diff < 0) {
                adjus.textContent = 'OUT';
                adjus.classList.add('bg-danger');
                adjus.dataset.adjus = 'OUT';
            } else {
                adjus.textContent = '-';
                adjus.classList.add('bg-secondary');
                adjus.dataset.adjus = '';
            }
        }

        // Auto-fill hidden qty_in / qty_out
        if (inEl)  inEl.value  = diff > 0 ? absDiff : 0;
        if (outEl) outEl.value = diff < 0 ? absDiff : 0;

        // Row color
        row.classList.remove('adjust-row-pos', 'adjust-row-neg', 'adjust-row-zero');
        if (diff > 0)      row.classList.add('adjust-row-pos');
        else if (diff < 0) row.classList.add('adjust-row-neg');
        else               row.classList.add('adjust-row-zero');
    }

    function recalcTotals(wrapperId) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;
        const rows = wrapper.querySelectorAll('tr');

        let totalIn = 0, totalOut = 0, count = 0;
        rows.forEach(row => {
            recalcRow(row); // refresh per row first
            const odoo  = num(cellInput(row, 'odoo')?.value);
            const fisik = num(cellInput(row, 'fisik')?.value);
            const diff = fisik - odoo;
            if (diff > 0)      totalIn  += diff;
            else if (diff < 0) totalOut += Math.abs(diff);
            count++;
        });

        const panel = document.querySelector('.adjust-totals[data-wrapper="' + wrapperId + '"]');
        if (!panel) return;

        const net = totalIn - totalOut;
        const fmt = (n) => Number.isInteger(n) ? n.toLocaleString('id-ID') : n.toFixed(2);
        const set = (stat, val) => {
            const el = panel.querySelector('[data-stat="' + stat + '"]');
            if (el) el.textContent = fmt(val);
        };
        set('count', count);
        set('in', totalIn);
        set('out', totalOut);

        const netEl = panel.querySelector('[data-stat="net"]');
        if (netEl) {
            netEl.textContent = (net > 0 ? '+' : '') + fmt(net);
            netEl.classList.remove('text-success', 'text-danger', 'text-muted');
            netEl.classList.add(net > 0 ? 'text-success' : (net < 0 ? 'text-danger' : 'text-muted'));
        }
    }

    /** Tombol "Auto-Calc" hanya force re-render (semua sudah auto via input event) */
    function handleAutoCalcClick(e) {
        const btn = e.target.closest('.adjust-btn-autocalc');
        if (!btn) return;
        e.preventDefault();
        const scope = btn.getAttribute('data-scope') || 'create';
        const wrapperId = scope === 'edit' ? 'wrapperAdjustEdit' : 'wrapperAdjust';
        recalcTotals(wrapperId);
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Terhitung';
        setTimeout(() => { btn.innerHTML = orig; }, 1200);
    }

    function bindLiveRecalc(wrapperId) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;

        // Capture future-added rows via event delegation
        wrapper.addEventListener('input', (e) => {
            const t = e.target;
            if (!t.name) return;
            if (/\[(odoo|fisik)\]$/.test(t.name)) {
                const row = t.closest('tr');
                if (row) recalcRow(row);
                recalcTotals(wrapperId);
            }
        });

        const mo = new MutationObserver(() => recalcTotals(wrapperId));
        mo.observe(wrapper, { childList: true });

        recalcTotals(wrapperId);
    }

    document.addEventListener('DOMContentLoaded', () => {
        bindLiveRecalc('wrapperAdjust');
        bindLiveRecalc('wrapperAdjustEdit');
        document.addEventListener('click', handleAutoCalcClick);
    });

    window.adjustRecalcAll = function () {
        bindLiveRecalc('wrapperAdjust');
        bindLiveRecalc('wrapperAdjustEdit');
    };
})();
