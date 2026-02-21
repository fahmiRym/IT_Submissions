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
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('superadmin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-4">
                        {{-- Nama Aplikasi --}}
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase">Nama Aplikasi / Instansi</label>
                            <input type="text" name="app_name" class="form-control bg-light border-0 py-2 px-3" 
                                   value="{{ $app_name }}" placeholder="Contoh: IT Submissions - PT Maju Jaya" required>
                        </div>

                        {{-- Logo Terkini --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-uppercase">Logo Saat Ini</label>
                            <div class="bg-light rounded-4 p-3 d-flex align-items-center justify-content-center border" style="height: 150px;">
                                @if($app_logo)
                                    <img src="{{ asset('storage/settings/' . $app_logo) }}" alt="Logo" class="img-fluid" style="max-height: 100px;">
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
                            <input type="file" name="app_logo" class="form-control bg-light border-0 py-2 mb-2" accept="image/*">
                            <div class="alert alert-info border-0 rounded-3 py-2 px-3 mb-0" style="font-size: 0.75rem;">
                                <i class="bi bi-info-circle-fill me-1"></i> Format direkomendasikan: <strong>PNG Transparan</strong> atau <strong>JPG</strong> (Maks. 2MB).
                            </div>
                        </div>

                        <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('superadmin.dashboard') }}" class="btn btn-light rounded-pill px-4">Batal</a>
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
@endsection
