@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi')
@section('page-title', '⚙️ Pengaturan Aplikasi')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 mt-2 mx-2">
                    <h5 class="fw-bold mb-0">Identitas Aplikasi</h5>
                    <p class="text-muted small mb-0">Ubah nama dan logo perusahaan untuk branding sistem</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success mx-4 mt-3 border-0 rounded-4 shadow-sm d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger mx-4 mt-3 border-0 rounded-4 shadow-sm">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('superadmin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-4">
                            {{-- Nama Aplikasi --}}
                            <div class="col-12">
                                <label class="form-label small fw-bold text-uppercase">Nama Aplikasi / Instansi</label>
                                <input type="text" name="app_name" class="form-control bg-light border-0 py-2 px-3"
                                    value="{{ $app_name }}" placeholder="Contoh: IT Submissions - PT Inkasa Jaya Alluminium"
                                    required>
                            </div>

                            {{-- Kota BA --}}
                            <div class="col-12">
                                <label class="form-label small fw-bold text-uppercase">
                                    <i class="bi bi-geo-alt-fill text-primary me-1"></i>Location Company
                                </label>
                                <input type="text" name="kota_ba" class="form-control bg-light border-0 py-2 px-3"
                                    value="{{ $kota_ba ?? 'PASURUAN' }}" placeholder="Contoh: PASURUAN">
                                <small class="text-muted">
                                    Nama kota ini akan muncul di footer Berita Acara, contoh: <strong>PASURUAN, 09 Mei
                                        2025</strong>
                                </small>
                            </div>

                            {{-- Logo Terkini --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-uppercase">Logo Saat Ini</label>
                                <div class="bg-light rounded-4 p-3 d-flex align-items-center justify-content-center border"
                                    style="height: 150px;">
                                    @if($app_logo)
                                        <img src="{{ asset('storage/settings/' . $app_logo) }}" alt="Logo" class="img-fluid"
                                            style="max-height: 100px;">
                                    @else
                                        <div class="text-center">
                                            <i class="bi bi-image text-muted display-4"></i>
                                            <p class="text-muted small mb-0 mt-2">Belum ada logo</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Upload Logo Baru --}}
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-uppercase">Ganti Logo Baru</label>
                                <input type="file" name="app_logo" class="form-control bg-light border-0 py-2 mb-2"
                                    accept="image/*">
                                <div class="alert alert-info border-0 rounded-3 py-2 px-3 mb-0" style="font-size: 0.75rem;">
                                    <i class="bi bi-info-circle-fill me-1"></i> Format direkomendasikan: <strong>PNG
                                        Transparan</strong> atau <strong>JPG</strong> (Maks. 2MB).
                                </div>
                            </div>

                            {{-- Watermark Settings --}}
                            <div class="col-12 mt-4">
                                <div class="bg-light rounded-4 p-4 border border-info border-opacity-25">
                                    <h6 class="fw-bold mb-3 text-info"><i class="bi bi-fonts me-2"></i>Kustomisasi Teks Watermark</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Status DONE / LENGKAP</label>
                                            <input type="text" name="wm_done" class="form-control border-0 shadow-sm" value="{{ $wm_done }}" placeholder="Kosongkan jika tidak ingin watermark">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Status VOID / BATAL</label>
                                            <input type="text" name="wm_void" class="form-control border-0 shadow-sm" value="{{ $wm_void }}" placeholder="Kosongkan jika tidak ingin watermark">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-xs fw-bold text-muted mb-1">Status REJECT / TOLAK</label>
                                            <input type="text" name="wm_reject" class="form-control border-0 shadow-sm" value="{{ $wm_reject }}" placeholder="Kosongkan jika tidak ingin watermark">
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-3" style="font-size: 0.7rem;">
                                        * Teks ini akan muncul secara diagonal di latar belakang cetakan dokumen (Berita Acara).
                                    </small>
                                </div>
                            </div>

                            {{-- Feature Toggle: Pengajuan Produk Baru --}}
                            <div class="col-12">
                                <div class="p-3 border-0 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #faf5ff, #ede9fe);">
                                    <label class="form-label small fw-bold text-uppercase d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-toggles2 text-primary"></i>
                                        Fitur Pengajuan Produk Baru
                                    </label>
                                    <div class="form-check form-switch">
                                        {{-- hidden 0 supaya unchecked-state tetap ter-submit sbg "off" --}}
                                        <input type="hidden" name="produk_baru_enabled" value="0">
                                        <input type="checkbox" name="produk_baru_enabled" value="1"
                                            class="form-check-input" id="produkBaruEnabledToggle"
                                            style="cursor:pointer; width: 2.6em; height: 1.4em;"
                                            {{ ($produk_baru_enabled ?? '1') === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold ms-1" for="produkBaruEnabledToggle">
                                            <span class="text-success state-on" style="display: {{ ($produk_baru_enabled ?? '1') === '1' ? 'inline' : 'none' }};">
                                                <i class="bi bi-check-circle-fill me-1"></i> AKTIF — user bisa mengajukan Produk Baru
                                            </span>
                                            <span class="text-danger state-off" style="display: {{ ($produk_baru_enabled ?? '1') === '1' ? 'none' : 'inline' }};">
                                                <i class="bi bi-pause-circle-fill me-1"></i> DINONAKTIFKAN SEMENTARA
                                            </span>
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">
                                        * Mematikan fitur ini akan menyembunyikan menu, filter, dashboard card, dan opsi
                                        "Pengajuan Produk Baru" di form pengajuan untuk semua role. Data lama tetap aman.
                                    </small>
                                </div>
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                                <a href="{{ route('superadmin.dashboard') }}"
                                    class="btn btn-light rounded-pill px-4">Batal</a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tog = document.getElementById('produkBaruEnabledToggle');
            if (!tog) return;
            const sOn = document.querySelector('.state-on');
            const sOff = document.querySelector('.state-off');
            tog.addEventListener('change', function () {
                if (this.checked) { sOn.style.display = 'inline'; sOff.style.display = 'none'; }
                else { sOn.style.display = 'none'; sOff.style.display = 'inline'; }
            });
        });
    </script>
@endsection