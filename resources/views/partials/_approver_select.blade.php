{{-- Pemilihan approver bertingkat per pengajuan + visual flow timeline.
     Pakai: @include('partials._approver_select', ['approverUsers' => $approverUsers, 'jenisSelectId' => 'jenisPengajuanTambahAdmin']) --}}
@php $approverUsers = $approverUsers ?? collect(); @endphp

<div class="card border-0 shadow-sm rounded-3 mb-3 approver-card overflow-hidden" data-jenis-select="{{ $jenisSelectId }}">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-diagram-3-fill text-primary"></i>
            <h6 class="fw-bold text-primary mb-0" style="font-size:0.85rem;">Alur Persetujuan (Approver)</h6>
            <span class="badge bg-warning text-dark ms-auto approver-adjust-badge d-none" style="font-size:0.6rem;">
                <i class="bi bi-star-fill me-1"></i>ADJUSTMENT FLOW
            </span>
        </div>

        {{-- Visual Flow Timeline (auto-scaling) --}}
        <div class="approval-flow d-flex flex-wrap align-items-center gap-1 mb-3 pb-2 border-bottom">
            @php
                $flowSteps = [
                    ['key' => 'Pemohon',   'label' => 'Pemohon',    'icon' => 'bi-person-fill',     'color' => '#6366f1', 'auto' => true],
                    ['key' => 'SPV',       'label' => 'SPV',        'icon' => 'bi-person-badge',    'color' => '#0ea5e9', 'auto' => false],
                    ['key' => 'Kabag',     'label' => 'Kabag',      'icon' => 'bi-person-vcard',    'color' => '#10b981', 'auto' => false],
                    ['key' => 'Manager',   'label' => 'Manager',    'icon' => 'bi-person-workspace','color' => '#f59e0b', 'auto' => false],
                    ['key' => 'Accounting','label' => 'Accounting', 'icon' => 'bi-calculator-fill', 'color' => '#dc2626', 'auto' => false, 'adjust_only' => true],
                    ['key' => 'IT',        'label' => 'Dept. IT',   'icon' => 'bi-shield-check',    'color' => '#7c3aed', 'auto' => true],
                ];
            @endphp
            @foreach($flowSteps as $i => $step)
                <div class="approval-step d-inline-flex align-items-center gap-1 flex-shrink-0
                            @if(!empty($step['adjust_only'])) approval-step-adjust @endif"
                     data-step="{{ $step['key'] }}"
                     style="--step-color: {{ $step['color'] }};">
                    <span class="approval-step-pill d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill"
                          style="background: {{ $step['color'] }}15; color: {{ $step['color'] }}; font-size:0.65rem; font-weight:700;">
                        <i class="bi {{ $step['icon'] }}"></i>
                        <span>{{ $step['label'] }}</span>
                        @if(!empty($step['auto']))
                            <span class="badge ms-1" style="background: {{ $step['color'] }}; color:white; font-size:0.5rem; padding: 1px 5px;">AUTO</span>
                        @endif
                    </span>
                    @if($i < count($flowSteps) - 1)
                        <i class="bi bi-chevron-right text-muted" style="font-size:0.55rem; opacity:0.5;"></i>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="text-muted mb-3" style="font-size:0.7rem;">
            <i class="bi bi-info-circle me-1"></i>
            Pilih PIC tiap tahap. Persetujuan berjalan berurutan; tahap <b>Pemohon</b> &amp; <b>Departemen IT</b> otomatis.
            <span class="text-danger fw-bold approver-adjust-hint d-none">Khusus <b>Adjust</b> wajib lewat Accounting sebelum IT (kontrol stok &amp; sinkron Odoo).</span>
        </p>

        <div class="row g-2">
            @foreach(['SPV' => 'SPV', 'Kabag' => 'Kabag', 'Manager' => 'Manager', 'Accounting' => 'Accounting'] as $role => $label)
                <div class="col-12 col-md-6 col-lg-12 approver-field" data-role="{{ $role }}">
                    <label class="form-label small fw-bold text-secondary mb-1 d-flex align-items-center gap-1">
                        @if($role === 'Accounting')
                            <i class="bi bi-calculator-fill text-danger"></i>
                            <span>{{ $label }}</span>
                            <span class="badge bg-danger ms-1 approver-adjust-mark d-none" style="font-size:0.55rem;">WAJIB ADJUST</span>
                        @else
                            <i class="bi bi-person-circle text-secondary"></i>
                            <span>{{ $label }}</span>
                        @endif
                    </label>
                    <select name="approvers[{{ $role }}]"
                            class="form-select form-select-sm bg-light border-0 @if($role==='Accounting') approver-accounting @endif">
                        <option value="">-- Pilih {{ $label }} --</option>
                        @foreach($approverUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}{{ $u->jabatan ? ' ('.$u->jabatan.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('styles')
<style>
    .approval-flow { row-gap: 4px; }
    .approval-step-pill { transition: all 0.25s ease; white-space: nowrap; }
    .approval-step-pill:hover { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
    .approval-step.active .approval-step-pill {
        background: var(--step-color) !important;
        color: #fff !important;
        box-shadow: 0 4px 10px color-mix(in srgb, var(--step-color) 40%, transparent);
    }
    .approval-step.approval-step-adjust .approval-step-pill {
        outline: 1px dashed currentColor;
        outline-offset: 1px;
    }
    .approver-card.is-adjust {
        background: linear-gradient(135deg, rgba(220,38,38,0.03) 0%, rgba(255,255,255,1) 50%);
        border-left: 3px solid #dc2626 !important;
    }
    @media (max-width: 575.98px) {
        .approval-step-pill { font-size: 0.6rem !important; padding: 0.2rem 0.5rem !important; }
        .approval-step-pill .badge { font-size: 0.45rem !important; }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const card = document.querySelector('.approver-card[data-jenis-select="{{ $jenisSelectId }}"]');
    if (!card) return;
    const jenisSel = document.getElementById('{{ $jenisSelectId }}');
    if (!jenisSel) return;

    function fieldsFor(jenis) {
        if (jenis === 'Produk_Baru') return [];
        if (jenis === 'Adjust') return ['SPV','Kabag','Manager','Accounting'];
        return ['SPV','Kabag','Manager'];
    }

    function apply() {
        const jenis = jenisSel.value;
        const allow = fieldsFor(jenis);
        const isAdjust = jenis === 'Adjust';

        card.style.display = allow.length ? '' : 'none';
        card.classList.toggle('is-adjust', isAdjust);
        card.querySelectorAll('.approver-adjust-badge, .approver-adjust-hint, .approver-adjust-mark')
            .forEach(el => el.classList.toggle('d-none', !isAdjust));

        // toggle field rows
        card.querySelectorAll('.approver-field').forEach(f => {
            const role = f.getAttribute('data-role');
            const show = allow.includes(role);
            f.style.display = show ? '' : 'none';
            const sel = f.querySelector('select');
            if (sel) sel.disabled = !show;
        });

        // toggle visual timeline step
        card.querySelectorAll('.approval-step').forEach(step => {
            const k = step.getAttribute('data-step');
            const visible = ['Pemohon','IT'].includes(k) || allow.includes(k);
            step.style.display = visible ? '' : 'none';
            step.classList.toggle('active', isAdjust && k === 'Accounting');
        });
    }

    jenisSel.addEventListener('change', apply);
    apply();
})();
</script>
@endpush
