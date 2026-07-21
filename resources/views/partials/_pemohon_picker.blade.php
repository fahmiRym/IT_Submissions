{{-- Multi-pemohon picker dengan search by employee_id / nama.
     Pakai: @include('partials._pemohon_picker', ['fieldId' => 'pemohonPicker', 'name' => 'requesters', 'selected' => []])
     - $fieldId : ID select element (unik per modal)
     - $name    : name attribute (mis. 'requesters', dikirim sebagai array user_id)
     - $selected: array of {id, employee_id, name} untuk preload edit
     - $textName: optional, nama hidden untuk fallback text "pemohon" (join name) — default 'pemohon' --}}
@php
    $fieldId  = $fieldId ?? 'pemohonPicker';
    $name     = $name ?? 'requesters';
    $selected = $selected ?? [];
    $textName = $textName ?? 'pemohon';
@endphp

<div class="pemohon-picker-wrap" data-picker-id="{{ $fieldId }}">
    <label class="form-label small fw-bold text-secondary text-uppercase d-flex align-items-center gap-2 mb-1">
        <i class="bi bi-people-fill text-primary"></i>
        Pemohon (Multi-pilih)
        <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:0.55rem;">Cari by NIK / nama</span>
    </label>
    <select id="{{ $fieldId }}" name="{{ $name }}[]" multiple
            class="form-control pemohon-picker-select" placeholder="Ketik NIK atau nama karyawan...">
        @foreach($selected as $s)
            <option value="{{ $s['id'] ?? $s->id }}" selected>
                {{ ($s['employee_id'] ?? $s->employee_id ?? '') }} — {{ $s['name'] ?? $s->name ?? '' }}
            </option>
        @endforeach
    </select>
    {{-- Backward-compat hidden mirror: gabungan nama → kolom `pemohon` lama --}}
    <input type="hidden" name="{{ $textName }}" id="{{ $fieldId }}_text" value="{{ collect($selected)->pluck('name')->implode(', ') }}">
    <small class="text-muted" style="font-size:0.65rem;">
        <i class="bi bi-info-circle me-1"></i>Bisa pilih lebih dari satu. Dropdown menampilkan NIK + nama + dept/unit.
    </small>
</div>

@once
    @push('styles')
    {{-- Tom-Select CSS (CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .ts-wrapper.form-control {
            padding: 0.35rem 0.5rem !important;
            min-height: 42px;
        }
        .ts-control {
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
        }
        .ts-wrapper .item {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
            color: white !important;
            border-radius: 999px !important;
            padding: 3px 10px 3px 12px !important;
            font-weight: 600;
            font-size: 0.78rem;
            margin: 2px 4px 2px 0 !important;
            box-shadow: 0 1px 3px rgba(79, 70, 229, 0.25);
        }
        .ts-wrapper .item .ts-remove {
            color: rgba(255,255,255,0.75) !important;
            margin-left: 5px;
        }
        .ts-wrapper .item .ts-remove:hover { color: #fff !important; }
        .ts-dropdown {
            border-radius: 12px !important;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
            border: 1px solid #e2e8f0 !important;
        }
        .ts-dropdown .option {
            padding: 0.55rem 0.75rem !important;
            font-size: 0.85rem;
        }
        .ts-dropdown .option.active {
            background: #eef2ff !important;
            color: #4f46e5 !important;
        }
        .pemohon-opt-line1 { font-weight: 700; color: #1e293b; }
        .pemohon-opt-line2 { font-size: 0.7rem; color: #64748b; }
        .pemohon-opt-nik {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            font-weight: 700;
            font-size: 0.7rem;
            padding: 1px 7px;
            border-radius: 5px;
            margin-right: 8px;
            font-family: monospace;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        window.initPemohonPicker = function (selectId) {
            const el = document.getElementById(selectId);
            if (!el) return null;
            if (el.tomselect) return el.tomselect; // already initialized

            const ts = new TomSelect('#' + selectId, {
                valueField: 'id',
                labelField: 'name',
                searchField: ['name', 'employee_id', 'username'],
                maxOptions: 30,
                plugins: ['remove_button'],
                load: function (query, callback) {
                    fetch("{{ route('users.search') }}?q=" + encodeURIComponent(query))
                        .then(r => r.json())
                        .then(j => callback(j.data || []))
                        .catch(() => callback());
                },
                render: {
                    option: function (data, escape) {
                        const dept = data.department ? escape(data.department) : '';
                        const unit = data.work_unit ? ' · ' + escape(data.work_unit) : '';
                        return `<div>
                            <div class="pemohon-opt-line1">
                                <span class="pemohon-opt-nik">${escape(data.employee_id || '-')}</span>
                                ${escape(data.name)}
                            </div>
                            ${dept ? `<div class="pemohon-opt-line2">${dept}${unit}</div>` : ''}
                        </div>`;
                    },
                    item: function (data, escape) {
                        return `<div>${escape(data.employee_id || '')} · ${escape(data.name)}</div>`;
                    },
                    no_results: function () {
                        return '<div class="no-results p-2 text-muted small">Tidak ditemukan. Coba ketik NIK atau nama lain.</div>';
                    },
                },
                onChange: function () {
                    // Sync hidden field 'pemohon' text fallback
                    const mirror = document.getElementById(selectId + '_text');
                    if (!mirror) return;
                    const items = Object.values(this.options)
                        .filter(o => this.items.includes(String(o.id)))
                        .map(o => o.name);
                    mirror.value = items.join(', ');
                },
            });

            return ts;
        };

        // Auto-init untuk picker yang sudah ada di DOM saat load
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.pemohon-picker-select').forEach(sel => {
                if (sel.id) window.initPemohonPicker(sel.id);
            });
        });

        // Re-init helper untuk AJAX modal (edit)
        window.refreshPemohonPicker = function (selectId, presetItems) {
            const el = document.getElementById(selectId);
            if (!el) return;
            if (el.tomselect) el.tomselect.destroy();
            // preload options from presetItems (array of {id, employee_id, name})
            if (Array.isArray(presetItems) && presetItems.length) {
                el.innerHTML = '';
                presetItems.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = (p.employee_id ? p.employee_id + ' — ' : '') + p.name;
                    opt.selected = true;
                    el.appendChild(opt);
                });
            }
            window.initPemohonPicker(selectId);
        };
    </script>
    @endpush
@endonce
