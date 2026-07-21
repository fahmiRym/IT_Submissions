{{-- TTD Digital: sekarang otomatis via QR. Tidak butuh upload/gambar specimen.
     Partial ini di-include di Profil — hanya tampilkan info + preview QR. --}}
@php
    $previewQrDataUri = \App\Services\QrSignatureService::renderTextQrDataUri(
        'https://it-submissions/verify?preview-' . (auth()->user()->id ?? 'x'),
        180
    );
@endphp

<div class="card border-0 shadow-sm rounded-4 mt-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="p-4 text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white bg-opacity-25 rounded-3 p-2" style="backdrop-filter: blur(8px);">
                    <i class="bi bi-qr-code-scan fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Tanda Tangan Digital (QR-Based)</h5>
                    <small class="opacity-75">TTD otomatis menggunakan QR Code unik per dokumen. Tidak perlu upload gambar.</small>
                </div>
            </div>
        </div>

        <div class="p-4">
            <div class="row g-4 align-items-center">
                <div class="col-md-4 text-center">
                    <div class="border rounded-4 p-3 bg-light d-inline-block">
                        @if($previewQrDataUri)
                            <img src="{{ $previewQrDataUri }}" alt="Preview QR" style="width: 140px; height: 140px;">
                        @else
                            <div style="width:140px;height:140px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;">QR Preview</div>
                        @endif
                    </div>
                    <div class="text-muted small mt-2"><i class="bi bi-info-circle me-1"></i>Contoh QR — yang asli ter-generate per dokumen.</div>
                </div>
                <div class="col-md-8">
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-check-circle-fill me-1"></i>Cara Kerja TTD Digital Baru</h6>
                    <ol class="small text-secondary" style="line-height:1.7;">
                        <li>Saat Anda klik <b>"Setujui &amp; Tanda Tangani"</b> pada pengajuan, sistem otomatis:
                            <ul class="mt-1">
                                <li>Membuat <b>hash unik</b> (SHA-256) dari ID Anda + ID dokumen + timestamp + secret key</li>
                                <li>Mengeluarkan <b>QR code</b> yang berisi URL verifikasi</li>
                                <li>Menempelkan QR di kotak tanda tangan dokumen draft</li>
                            </ul>
                        </li>
                        <li>Siapapun bisa <b>scan QR</b> tersebut → masuk ke halaman <span class="font-monospace text-primary">/verify</span> yang menampilkan: nama penandatangan, jabatan, waktu TTD, hash valid.</li>
                        <li>Hash <b>tidak bisa dipalsukan</b> karena pakai secret key server (anti-forgery).</li>
                    </ol>
                    <div class="alert alert-info border-0 small mt-3 d-flex align-items-start gap-2 mb-0"
                         style="background:rgba(99,102,241,0.08); color:#4338ca;">
                        <i class="bi bi-shield-fill-check mt-1"></i>
                        <div>
                            <b>Tidak perlu lagi</b> menggambar atau upload tanda tangan fisik. Sistem akan generate otomatis QR Code unik tiap kali Anda menandatangani dokumen.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
