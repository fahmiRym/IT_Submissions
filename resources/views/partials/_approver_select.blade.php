{{-- Pemilihan approver bertingkat per pengajuan.
     Pakai: @include('partials._approver_select', ['approverUsers' => $approverUsers, 'jenisSelectId' => 'jenisPengajuanTambahAdmin']) --}}
@php $approverUsers = $approverUsers ?? collect(); @endphp
<div class="card border-0 shadow-sm rounded-3 mb-3 approver-card" data-jenis-select="{{ $jenisSelectId }}">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-diagram-3-fill text-primary"></i>
            <h6 class="fw-bold text-primary mb-0" style="font-size:0.85rem;">Alur Persetujuan (Approver)</h6>
        </div>
        <p class="text-muted mb-3" style="font-size:0.7rem;">Pilih penanggung jawab tiap tahap. Persetujuan berjalan berurutan: Pemohon → SPV → Kabag → Manager → (Accounting) → Departemen IT.</p>

        <div class="row g-2">
            @foreach(['SPV' => 'SPV', 'Kabag' => 'Kabag', 'Manager' => 'Manager', 'Accounting' => 'Accounting'] as $role => $label)
                <div class="col-12 approver-field" data-role="{{ $role }}">
                    <label class="form-label small fw-bold text-secondary mb-1">{{ $label }}</label>
                    <select name="approvers[{{ $role }}]" class="form-select form-select-sm bg-light border-0">
                        <option value="">-- Pilih {{ $label }} --</option>
                        @foreach($approverUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}{{ $u->jabatan ? ' ('.$u->jabatan.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
        <div class="text-muted mt-2" style="font-size:0.65rem;"><i class="bi bi-info-circle me-1"></i>Tahap <b>Departemen IT</b> otomatis (oleh Superadmin/Tim IT) sebagai persetujuan final.</div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const card = document.querySelector('.approver-card[data-jenis-select="{{ $jenisSelectId }}"]');
    if (!card) return;
    const jenisSel = document.getElementById('{{ $jenisSelectId }}');
    if (!jenisSel) return;

    function fieldsFor(jenis) {
        if (jenis === 'Produk_Baru') return [];            // langsung IT
        if (jenis === 'Adjust') return ['SPV','Kabag','Manager','Accounting'];
        return ['SPV','Kabag','Manager'];                  // selainnya
    }

    function apply() {
        const allow = fieldsFor(jenisSel.value);
        card.style.display = allow.length ? '' : 'none';
        card.querySelectorAll('.approver-field').forEach(f => {
            const role = f.getAttribute('data-role');
            const show = allow.includes(role);
            f.style.display = show ? '' : 'none';
            const sel = f.querySelector('select');
            if (sel) sel.disabled = !show; // disabled tidak ikut ter-submit
        });
    }

    jenisSel.addEventListener('change', apply);
    apply();
})();
</script>
@endpush
