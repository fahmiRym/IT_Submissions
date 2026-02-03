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
        <div class="border rounded p-3 bg-light">
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

    </div>

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
