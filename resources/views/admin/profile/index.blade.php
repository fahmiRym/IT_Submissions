@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', '👤 Profil Saya')

@push('styles')
<style>
    :root {
        --profile-primary: #2563eb;
        --profile-secondary: #3b82f6;
        --profile-bg: #f8fafc;
        --profile-card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    }

    .profile-header {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        height: 140px;
        border-radius: 16px 16px 0 0;
        position: relative;
    }

    .profile-card {
        border-radius: 16px;
        box-shadow: var(--profile-card-shadow);
        border: none;
        overflow: hidden;
    }

    .avatar-wrapper {
        position: relative;
        margin-top: -65px;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .avatar-circle {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: #fff;
        padding: 4px;
        display: inline-block;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .avatar-img, .avatar-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 800;
        background: #f1f5f9;
        color: var(--profile-primary);
    }

    .photo-upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: var(--profile-primary);
        color: white;
        border: 3px solid white;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.85rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .photo-upload-btn:hover {
        background: #1e40af;
        transform: scale(1.15);
    }

    .stat-box {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 0.75rem;
        text-align: center;
        transition: all 0.2s ease;
    }

    .stat-box:hover {
        border-color: #e2e8f0;
        background: #fbfcfd;
    }

    .stat-value {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 0.1rem;
    }

    .stat-label {
        font-size: 0.65rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 1rem;
        border-radius: 50px;
        background: #eef2ff;
        color: #2563eb;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.025em;
        border: 1px solid #e0e7ff;
    }

    .form-group-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .premium-input {
        background-color: #f8fafc !important;
        border: 1px solid transparent !important;
        border-radius: 12px !important;
        padding: 0.8rem 1.2rem !important;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.2s ease;
    }

    .premium-input:focus {
        background-color: #fff !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.05) !important;
    }

    .security-section {
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        padding: 1.5rem;
        margin-top: 1rem;
    }

    .security-title {
        font-size: 0.9rem;
        font-weight: 800;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }

    .security-icon-box {
        width: 28px;
        height: 28px;
        background: #eef2ff;
        color: #2563eb;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .info-alert {
        background: #f8fafc;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #64748b;
        font-size: 0.8rem;
        margin-bottom: 1rem;
    }

    .btn-save {
        background: #2563eb;
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.8rem 2rem;
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.25);
    }

    .btn-save:hover {
        background: #1e40af;
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
    }

    .btn-reset {
        background: #f1f5f9;
        color: #64748b;
        border: none;
        border-radius: 12px;
        padding: 0.8rem 1.5rem;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .btn-reset:hover {
        background: #e2e8f0;
        color: #475569;
    }

    .delete-photo-link {
        color: #ef4444;
        font-size: 0.75rem;
        font-weight: 700;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.2s ease;
    }

    .delete-photo-link:hover {
        color: #dc2626;
        opacity: 0.8;
    }
</style>
@endpush

@section('content')
<div class="row">
    {{-- LEFT: PROFILE CARD --}}
    <div class="col-xl-4 mb-4">
        <div class="card profile-card border-0">
            <div class="profile-header"></div>
            <div class="card-body p-0 pb-4">
                <div class="avatar-wrapper">
                    <div class="avatar-circle">
                        @if($user->photo)
                            <img src="{{ asset('profile_photos/' . $user->photo) }}" alt="Profile Photo" class="avatar-img" id="currentPhoto">
                        @else
                            <div class="avatar-inner" id="avatarInitial">
                                {{ substr(strtoupper($user->name), 0, 1) }}
                            </div>
                        @endif
                        <label for="photoInput" class="photo-upload-btn" title="Ganti Foto">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                    </div>

                    <div class="mt-3">
                        <h4 class="fw-bold text-dark mb-0">{{ $user->name }}</h4>
                        <p class="text-muted small mb-3">{{ $user->email }}</p>
                        
                        <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                            <div class="role-badge">
                                <i class="bi bi-shield-check me-1"></i> ADMIN
                            </div>
                            @if($user->department)
                            <div class="role-badge" style="background: #f8fafc; color: #64748b; border-color: #e2e8f0;">
                                <i class="bi bi-building me-1"></i> {{ $user->department->name }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <hr class="mx-4 my-2 opacity-10">

                <div class="px-4 py-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-value text-primary">{{ $stats['total'] }}</div>
                                <div class="stat-label">Total Arsip</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-value text-success">{{ $stats['done'] }}</div>
                                <div class="stat-label">Selesai</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-value text-warning">{{ $stats['pending'] }}</div>
                                <div class="stat-label">Pending</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-value text-info">{{ $stats['process'] }}</div>
                                <div class="stat-label">Proses</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: EDIT FORM --}}
    <div class="col-xl-8">
        
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-5 me-3"></i>
                <div class="fw-bold">{{ session('success') }}</div>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="bi bi-gear-fill me-2 text-primary"></i> Edit Profil Saya
                </h5>
                @if($user->photo)
                    <a href="javascript:void(0)" class="delete-photo-link" onclick="removeCurrentPhoto()">
                        <i class="bi bi-trash-fill"></i> Hapus Foto Saat Ini
                    </a>
                @endif
            </div>

            <div class="card-body px-4 pb-4 pt-0">
                <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                    @csrf 
                    @method('PUT')

                    {{-- PHOTO UPLOAD HIDDEN INPUTS --}}
                    <input type="file" id="photoInput" name="photo" accept="image/*" class="d-none">
                    <input type="hidden" id="removePhoto" name="remove_photo" value="0">
                    
                    {{-- PREVIEW SECTION (Dynamic) --}}
                    <div id="photoPreviewBanner" class="d-none mb-4 animate__animated animate__fadeIn">
                        <div class="p-3 rounded-4 bg-light d-flex align-items-center justify-content-between border">
                            <div class="d-flex align-items-center gap-3">
                                <div class="position-relative">
                                    <img id="tempPhotoPreview" src="" alt="Preview" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <div class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 12px; height: 12px;"></div>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">Pratinjau Foto Baru</div>
                                    <small class="text-muted" id="photoNameLabel" style="font-size: 0.7rem;"></small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 fw-bold text-danger" onclick="cancelPhotoUpload()">
                                Batal
                            </button>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-group-label">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                   class="form-control premium-input @error('name') is-invalid @enderror" placeholder="Nama Anda" required>
                            @error('name') <div class="invalid-feedback ps-2">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-group-label">Alamat Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                   class="form-control premium-input @error('email') is-invalid @enderror" placeholder="email@contoh.com" required>
                            @error('email') <div class="invalid-feedback ps-2">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="security-section">
                        <div class="security-title">
                            <div class="security-icon-box">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            Ganti Password
                        </div>

                        <div class="info-alert">
                            <i class="bi bi-info-circle-fill text-primary"></i>
                            Biarkan kosong jika tidak ingin mengubah password lama Anda.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-group-label" style="font-size: 0.65rem;">Password Baru</label>
                                <input type="password" name="password" class="form-control premium-input @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter">
                                @error('password') <div class="invalid-feedback ps-2">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-group-label" style="font-size: 0.65rem;">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control premium-input" placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-5 pb-2">
                        <button type="reset" class="btn-reset">Reset</button>
                        <button type="submit" class="btn-save">
                            <i class="bi bi-save-fill"></i> Simpan Perubahan
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
        const photoInput = document.getElementById('photoInput');
        
        // Photo upload preview
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewBanner = document.getElementById('photoPreviewBanner');
                    const tempPhotoPreview = document.getElementById('tempPhotoPreview');
                    const photoNameLabel = document.getElementById('photoNameLabel');
                    const currentPhoto = document.getElementById('currentPhoto');
                    const avatarInitial = document.getElementById('avatarInitial');
                    
                    tempPhotoPreview.src = e.target.result;
                    photoNameLabel.textContent = file.name;
                    previewBanner.classList.remove('d-none');
                    
                    // Update avatar preview
                    if (currentPhoto) {
                        currentPhoto.src = e.target.result;
                    } else if (avatarInitial) {
                        avatarInitial.outerHTML = `<img src="${e.target.result}" alt="Profile" class="avatar-img" id="currentPhoto">`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    function cancelPhotoUpload() {
        const photoInput = document.getElementById('photoInput');
        const previewBanner = document.getElementById('photoPreviewBanner');
        const currentPhoto = document.getElementById('currentPhoto');
        const avatarInitial = document.getElementById('avatarInitial');
        
        photoInput.value = '';
        previewBanner.classList.add('d-none');
        
        // Restore
        @if($user->photo)
            if (currentPhoto) currentPhoto.src = "{{ asset('profile_photos/' . $user->photo) }}";
        @else
            if (currentPhoto) currentPhoto.outerHTML = `<div class="avatar-inner" id="avatarInitial">{{ substr(strtoupper($user->name), 0, 1) }}</div>`;
        @endif
    }

    function removeCurrentPhoto() {
        if (confirm('Hapus foto profil saat ini?')) {
            const removeInput = document.getElementById('removePhoto');
            removeInput.value = '1';
            document.querySelector('form').submit();
        }
    }
</script>
@endpush

