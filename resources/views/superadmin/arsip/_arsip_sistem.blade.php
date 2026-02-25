<div class="modal fade" id="modalArsipSistem" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content rounded-4 shadow">

<form id="formArsipSistem">
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
            <input type="number" name="sequence_number" id="arsipSistemSeqInput"
                   class="form-control form-control-lg fw-bold text-success border-2"
                   placeholder="Contoh: 0500" style="border-color: #d1fae5 !important;">
            <div class="form-text mt-2 small text-muted">
                <i class="bi bi-info-circle me-1"></i> Biarkan <b>KOSONG</b> untuk menggunakan nomor urut otomatis sistem.
            </div>
        </div>

    </div>

    <style>
        @keyframes pulse-green {
            0%   { transform: scale(1);   opacity: 1; }
            50%  { transform: scale(1.2); opacity: .7; }
            100% { transform: scale(1);   opacity: 1; }
        }
        .animate-pulse-green { animation: pulse-green 2s infinite ease-in-out; }
    </style>

    {{-- FOOTER --}}
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" id="btnSubmitArsipSistem" class="btn btn-success px-4 fw-semibold">
            ✔ Arsipkan Sekarang
        </button>
    </div>
</form>

</div>
</div>
</div>


{{-- ============================================================ --}}
{{-- MODAL HASIL ARSIP — muncul otomatis setelah berhasil diarsip --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalHasilArsip" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content rounded-4 shadow-lg border-0">

    <div class="modal-header border-0 pb-0 px-4 pt-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow"
                 style="width:52px;height:52px;background:linear-gradient(135deg,#22c55e,#16a34a);">
                <i class="bi bi-check2-circle text-white fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-success">Arsip Berhasil!</h5>
                <small class="text-muted">Dokumen telah diarsipkan oleh sistem</small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="location.reload()"></button>
    </div>

    <div class="modal-body px-4 pb-2 pt-3">

        {{-- ============================================================ --}}
        {{-- SECTION CANCEL: No Doc + Sub Transaksi + Copy All            --}}
        {{-- ============================================================ --}}
        <div id="sectionCancelResult" class="d-none">

            {{-- Label info --}}
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 fw-bold"
                      style="font-size:.8rem;">
                    <i class="bi bi-x-octagon-fill me-1"></i>CANCEL
                </span>
                <small class="text-muted">Klik <b>Copy Semua</b> untuk menyalin No Doc + Sub Transaksi sekaligus</small>
            </div>

            {{-- Preview gabungan --}}
            <div class="mb-3 rounded-3 overflow-hidden border border-2" style="border-color:#86efac !important;">
                {{-- Header bar --}}
                <div class="d-flex align-items-center justify-content-between px-3 py-2"
                     style="background:#dcfce7;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clipboard2-fill text-success"></i>
                        <span class="fw-bold text-success small">Hasil Siap Copy</span>
                    </div>
                    <button type="button" id="btnCopyAll"
                            class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-sm"
                            onclick="copyAllResult(this)">
                        <i class="bi bi-clipboard-fill me-1"></i>Copy Semua
                    </button>
                </div>
                {{-- Content --}}
                <div class="p-3 bg-white">
                    <pre id="resultCopyAll"
                         class="mb-0 fw-bold font-monospace"
                         style="font-size:.9rem;white-space:pre-wrap;word-break:break-all;line-height:1.7;"></pre>
                </div>
            </div>

            {{-- Keterangan --}}
            <div class="d-flex align-items-start gap-2 p-3 rounded-3 mb-2"
                 style="background:#fef9c3;border:1px solid #fde047;">
                <i class="bi bi-info-circle-fill text-warning flex-shrink-0 mt-1"></i>
                <small class="text-dark">
                    Baris <b>MO</b> dan <b>PO</b> (Induk Transaksi) sudah otomatis dihapus.
                    Yang tersisa hanya <b>Sub Transaksi</b> (INK, INT, BPB, SJ, SJF, LL, dll).
                </small>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SECTION NON-CANCEL: hanya No Doc + No Registrasi             --}}
        {{-- ============================================================ --}}
        <div id="sectionNonCancelResult">

            {{-- No Doc --}}
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary text-uppercase" style="letter-spacing:.5px;">
                    <i class="bi bi-file-earmark-check-fill text-success me-1"></i>No Dokumen Dihasilkan
                </label>
                <div class="p-3 rounded-3 d-flex align-items-center justify-content-between gap-3"
                     style="background:#f0fdf4;border:2px solid #86efac;">
                    <span id="resultNoDoc" class="fw-bold text-success font-monospace fs-6"
                          style="letter-spacing:1px;word-break:break-all;"></span>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 flex-shrink-0 shadow-sm"
                            onclick="copyText('resultNoDoc', this)">
                        <i class="bi bi-clipboard-fill me-1"></i>Copy
                    </button>
                </div>
            </div>

            {{-- No Registrasi --}}
            <div class="mb-2">
                <label class="form-label small fw-bold text-secondary text-uppercase" style="letter-spacing:.5px;">
                    <i class="bi bi-bookmark-fill text-info me-1"></i>No Registrasi
                </label>
                <div class="p-3 rounded-3 d-flex align-items-center justify-content-between gap-3"
                     style="background:#f0f9ff;border:2px solid #7dd3fc;">
                    <span id="resultNoReg" class="fw-bold text-info font-monospace"
                          style="letter-spacing:.5px;word-break:break-all;"></span>
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 flex-shrink-0 shadow-sm"
                            onclick="copyText('resultNoReg', this)">
                        <i class="bi bi-clipboard-fill me-1"></i>Copy
                    </button>
                </div>
            </div>

        </div>

    </div>

    <div class="modal-footer border-0 px-4 pb-4 pt-2">
        <button type="button" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm"
                data-bs-dismiss="modal" onclick="location.reload()">
            <i class="bi bi-check-lg me-1"></i>Selesai & Tutup
        </button>
    </div>

</div>
</div>
</div>
