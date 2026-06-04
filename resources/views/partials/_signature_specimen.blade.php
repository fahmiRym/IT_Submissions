{{-- Specimen Tanda Tangan Digital. Pakai: @include('partials._signature_specimen', ['action' => route('admin.profile.signature')]) --}}
<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-body p-4">
        <h6 class="fw-bold text-primary mb-1"><i class="bi bi-pen me-2"></i>Tanda Tangan Digital (Specimen)</h6>
        <p class="text-muted small mb-3">Gambar tanda tangan di kotak, atau unggah file PNG/JPG. Specimen ini distempel ke dokumen saat Anda menandatangani.</p>

        <div class="row g-4">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-secondary">Specimen Saat Ini</label>
                <div class="border rounded-3 d-flex align-items-center justify-content-center bg-light" style="height: 150px;">
                    @if(auth()->user()->signature_path)
                        <img src="{{ auth()->user()->signatureUrl() }}" alt="Specimen TTD" style="max-height: 130px; max-width: 100%; object-fit: contain;">
                    @else
                        <span class="text-muted small fst-italic">Belum ada tanda tangan.</span>
                    @endif
                </div>
                @if(auth()->user()->signature_path)
                    <form method="POST" action="{{ $action }}" class="mt-2" onsubmit="return confirm('Hapus tanda tangan digital?')">
                        @csrf
                        <input type="hidden" name="remove_signature" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"><i class="bi bi-trash me-1"></i>Hapus</button>
                    </form>
                @endif
            </div>

            <div class="col-md-7">
                <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="formSignature">
                    @csrf
                    <ul class="nav nav-pills mb-2" id="sigTab" style="gap:6px;">
                        <li class="nav-item"><button class="nav-link active py-1 px-3" type="button" data-bs-toggle="pill" data-bs-target="#sigDraw">Gambar</button></li>
                        <li class="nav-item"><button class="nav-link py-1 px-3" type="button" data-bs-toggle="pill" data-bs-target="#sigUpload">Upload</button></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="sigDraw">
                            <div class="border rounded-3 bg-white" style="touch-action: none;">
                                <canvas id="sigCanvas" style="width:100%; height:150px; display:block;"></canvas>
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" id="sigClear"><i class="bi bi-eraser me-1"></i>Bersihkan</button>
                                <input type="hidden" name="signature_data" id="signatureData">
                            </div>
                        </div>
                        <div class="tab-pane fade" id="sigUpload">
                            <label class="form-label small fw-bold text-secondary">File Tanda Tangan (PNG/JPG, maks 2MB)</label>
                            <input type="file" name="signature_file" accept="image/png,image/jpeg" class="form-control">
                            <small class="text-muted">Disarankan PNG transparan agar rapi saat distempel.</small>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-save me-1"></i>Simpan Tanda Tangan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
(function () {
    const canvas = document.getElementById('sigCanvas');
    if (!canvas) return;

    function resize() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        if (pad) pad.clear();
    }

    const pad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)', penColor: '#0f172a' });
    // resize setelah elemen tampil (tab pertama aktif)
    setTimeout(resize, 200);
    window.addEventListener('resize', resize);

    document.getElementById('sigClear').addEventListener('click', () => pad.clear());

    document.getElementById('formSignature').addEventListener('submit', function (e) {
        const fileInput = this.querySelector('input[name="signature_file"]');
        const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
        if (!hasFile) {
            if (pad.isEmpty()) {
                e.preventDefault();
                alert('Gambar tanda tangan dulu, atau pilih file untuk diunggah.');
                return;
            }
            document.getElementById('signatureData').value = pad.toDataURL('image/png');
        }
    });
})();
</script>
@endpush
