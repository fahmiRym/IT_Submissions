@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', '👤 Profil Saya')

@push('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        height: 160px;
        border-radius: 20px 20px 0 0;
        position: relative;
    }
    .profile-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        overflow: hidden;
        background: #fff;
    }
    .avatar-wrapper {
        position: relative;
        margin-top: -80px;
        text-align: center;
        margin-bottom: 25px;
    }
    .avatar-circle {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: #fff;
        padding: 5px;
        display: inline-block;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .avatar-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(45deg, #f0f4ff, #e0e7ff);
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        font-weight: 800;
        border: 2px solid #e2e8f0;
    }
    .stat-card {
        background: #fff;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 12px;
    }
    .form-section-title {
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }
    .form-section-title::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #f1f5f9;
        margin-left: 1rem;
    }
    .form-control-modern {
        border-radius: 12px;
        padding: 12px 18px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
    }
    .form-control-modern:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .input-group-modern-text {
        border-radius: 12px 0 0 12px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div class="fw-bold">Perbaiki kesalahan berikut:</div>
            </div>
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- STATS SUMMARY --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div class="small fw-bold text-muted">Total Pengajuan</div>
                    <div class="h3 fw-bold mb-0">{{ $stats['total'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="small fw-bold text-muted">Pending/Check</div>
                    <div class="h3 fw-bold mb-0">{{ $stats['pending'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-gear-wide-connected"></i>
                    </div>
                    <div class="small fw-bold text-muted">Dalam Proses</div>
                    <div class="h3 fw-bold mb-0">{{ $stats['process'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-check-all"></i>
                    </div>
                    <div class="small fw-bold text-muted">Selesai</div>
                    <div class="h3 fw-bold mb-0">{{ $stats['done'] }}</div>
                </div>
            </div>
        </div>

        <div class="card profile-card">
            <div class="profile-header"></div>
            <div class="card-body px-4 px-md-5 pb-5">
                
                {{-- AVATAR & BASIC INFO --}}
                <div class="avatar-wrapper">
                    <div class="avatar-circle">
                        <div class="avatar-inner">
                            {{ substr(strtoupper($user->name), 0, 1) }}
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ $user->name }}</h3>
                    <p class="text-muted d-flex align-items-center justify-content-center gap-2 mb-1">
                        <i class="bi bi-envelope"></i> {{ $user->email }}
                    </p>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold">
                            <i class="bi bi-shield-check me-1"></i> Admin Area
                        </span>
                        @if($user->department)
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">
                            <i class="bi bi-building me-1"></i> {{ $user->department->name }}
                        </span>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.profile.update') }}">
                    @csrf 
                    @method('PUT')

                    <div class="form-section-title">Informasi Akun</div>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-modern-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                       class="form-control form-control-modern" placeholder="Nama Anda" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Email</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-modern-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                       class="form-control form-control-modern" placeholder="email@contoh.com" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-title text-primary">Ganti Password</div>
                    <div class="alert alert-light border-0 rounded-4 mb-4 small">
                        <i class="bi bi-info-circle-fill me-2 text-primary"></i> 
                        Kosongkan kolom di bawah ini jika Anda tidak ingin mengubah password lama Anda.
                    </div>
                    
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-modern-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control form-control-modern" 
                                       placeholder="Minimal 6 karakter">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-modern-text"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" name="password_confirmation" class="form-control form-control-modern" 
                                       placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-light rounded-pill px-4 fw-bold">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="bi bi-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection
