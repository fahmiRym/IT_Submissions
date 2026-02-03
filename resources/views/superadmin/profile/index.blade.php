@extends('layouts.app')

@section('title', 'Profile Saya')
@section('page-title', '👤 Pengaturan Akun')

@push('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        height: 180px;
        border-radius: 20px 20px 0 0;
        position: relative;
    }
    .profile-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        overflow: hidden;
        background: #fff;
    }
    .avatar-wrapper {
        position: relative;
        margin-top: -90px;
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
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .avatar-initial {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(45deg, #f8f9fc, #eaecf4);
        color: #4e73df;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        font-weight: 800;
        border: 2px solid #eaecf4;
    }
    .stat-mini-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        transition: all 0.2s;
    }
    .stat-mini-card:hover {
        background: #fff;
        border-color: #4e73df;
        transform: translateY(-3px);
    }
    .stat-mini-val {
        font-size: 1.25rem;
        font-weight: 700;
        color: #4e73df;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-mini-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
    }
    .form-control-user {
        border-radius: 12px;
        padding: 12px 18px;
        border: 1px solid #d1d3e2;
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    .form-control-user:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.1);
    }
    .input-group-text {
        border-radius: 12px 0 0 12px;
        border: 1px solid #d1d3e2;
        background-color: #f8f9fc;
        color: #6e707e;
    }
    .section-title {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }
    .section-title::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #f1f5f9;
        margin-left: 1rem;
    }
</style>
@endpush

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-10 col-lg-11">
        
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i> 
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 rounded-4" role="alert">
            <div class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> Terdapat Kesalahan:</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        {{-- GLOBAL SYSTEM STATS --}}
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-6">
                <div class="stat-mini-card">
                    <div class="stat-mini-val">{{ $stats['total_users'] }}</div>
                    <div class="stat-mini-label">Pengguna</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-mini-card">
                    <div class="stat-mini-val">{{ $stats['total_arsip'] }}</div>
                    <div class="stat-mini-label">Total Arsip</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-mini-card">
                    <div class="stat-mini-val text-warning">{{ $stats['pending'] }}</div>
                    <div class="stat-mini-label">Pending</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-mini-card">
                    <div class="stat-mini-val text-info">{{ $stats['process'] }}</div>
                    <div class="stat-mini-label">Proses</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-mini-card">
                    <div class="stat-mini-val text-success">{{ $stats['done'] }}</div>
                    <div class="stat-mini-label">Selesai</div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="stat-mini-card">
                    <div class="stat-mini-val text-primary">{{ $stats['departments'] }}</div>
                    <div class="stat-mini-label">Dept</div>
                </div>
            </div>
        </div>

        <div class="card profile-card">
            {{-- HEADER BACKGROUND --}}
            <div class="profile-header"></div>
            
            <div class="card-body px-4 px-md-5 pb-5">
                
                {{-- AVATAR --}}
                <div class="avatar-wrapper">
                    <div class="avatar-circle">
                        <div class="avatar-initial">
                            {{ substr(strtoupper($user->name), 0, 1) }}
                        </div>
                    </div>
                    <h4 class="mt-3 fw-bold text-dark mb-1">{{ $user->name }}</h4>
                    <p class="text-muted d-flex align-items-center justify-content-center gap-2 mb-2">
                        <i class="bi bi-envelope-at"></i> {{ $user->email }}
                    </p>
                    <div class="d-flex justify-content-center">
                        <span class="badge bg-primary rounded-pill px-4 py-2 border shadow-sm">
                            <i class="bi bi-shield-lock-fill me-1"></i> Global Superadmin
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('superadmin.profile.update') }}" class="mt-4">
                    @csrf
                    @method('PUT')

                    {{-- PERSONAL INFO --}}
                    <div class="section-title">Informasi Pribadi</div>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label text-xs fw-bold text-muted px-1">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control form-control-user border-start-0" 
                                       value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-xs fw-bold text-muted px-1">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control form-control-user border-start-0" 
                                       value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                    </div>

                    {{-- SECURITY --}}
                    <div class="section-title text-primary">Keamanan & Akses</div>
                    <div class="alert alert-light border-0 shadow-sm mb-4 rounded-4 d-flex align-items-center py-3">
                        <i class="bi bi-info-circle-fill fs-5 me-3 text-primary"></i>
                        <small class="text-muted fw-medium">Kosongkan kolom di bawah jika Anda tidak ingin mengubah password saat ini.</small>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label text-xs fw-bold text-muted px-1">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" class="form-control form-control-user border-start-0" 
                                       placeholder="Minimal 6 karakter">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-xs fw-bold text-muted px-1">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" name="password_confirmation" class="form-control form-control-user border-start-0" 
                                       placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="d-flex justify-content-end gap-3 pt-3 border-top mt-5">
                        <button type="reset" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="bi bi-save2-fill me-2"></i> Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Optional interaction
    });
</script>
@endpush
