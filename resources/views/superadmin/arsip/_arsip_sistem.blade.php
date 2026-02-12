<div class="modal fade" id="modalArsipSistem" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content rounded-4 shadow">

<form method="POST" id="formArsipSistem">
    @csrf
    @method('PUT')

    {{-- HEADER --}}
    <div class="modal-header bg-success text-white">
        <h5 class="modal-title">📦 Arsip Sistem</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>

    {{-- BODY --}}
    <div class="modal-body">

        <div class="alert alert-warning">
            <strong>Perhatian:</strong><br>
            Dengan menekan <b>ARSIPKAN</b>, maka:
            <ul class="mb-0 mt-2">
                <li>Status dokumen menjadi <b>Done</b></li>
                <li>Tanggal arsip otomatis diisi sistem</li>
                <li><b>No Arsip</b> akan dibuat otomatis</li>
                <li><b>No Dokumen</b> akan digenerate dan dikunci</li>
                <li>Data tidak dapat diubah kembali</li>
            </ul>
        </div>

        {{-- PREVIEW FORMAT --}}
        <div class="border rounded p-3 bg-light mb-3">
            <div class="fw-semibold mb-2">📄 Contoh Format No Dokumen:</div>

<pre class="mb-0 small text-dark">
Cancelled No Doc : 1599/{{ date('m') }}/IT/{{ date('Y') }}
Adjust No Doc   : DC/{{ date('Y') }}/{{ date('m') }}/{{ date('d') }}/0100
Mutasi Produk   : RPP/{{ date('Y') }}/{{ date('m') }}/1485
Internal Memo   : IM/{{ date('Y') }}/{{ date('m') }}/1485
</pre>

            <small class="text-muted d-block mt-2">
                * No Dokumen dan No Arsip hanya dibuat saat proses arsip.
            </small>
        </div>

        {{-- MANUAL SEQUENCE INPUT --}}
        <div class="p-3 rounded-3 border border-success border-opacity-25 bg-white shadow-sm">
            <label class="form-label small fw-bold text-success d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-hash fs-5 animate-pulse-green"></i> 
                Atur Nomor Urut (Manual Sequence)
            </label>
            <input type="number" name="sequence_number" class="form-control form-control-lg fw-bold text-success border-2" 
                   placeholder="Contoh: 0500" style="border-color: #d1fae5 !important;">
            <div class="form-text mt-2 small text-muted">
                <i class="bi bi-info-circle me-1"></i> Biarkan <b>KOSONG</b> untuk menggunakan nomor urut otomatis sistem.
            </div>
        </div>

    </div>

    <style>
        @keyframes pulse-green {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-pulse-green {
            animation: pulse-green 2s infinite ease-in-out;
        }
    </style>

    {{-- FOOTER --}}
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            Batal
        </button>

        <button type="submit" class="btn btn-success px-4 fw-semibold">
            ✔ Arsipkan Sekarang
        </button>
    </div>

</form>

</div>
</div>
</div>
