@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', '✏ Edit User')

@push('styles')
<style>
    .premium-card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        background: #fff;
    }
    .form-label-premium {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.6rem;
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
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.05) !important;
        outline: none;
    }
    .btn-premium-save {
        background: #4f46e5;
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.8rem 2.5rem;
        font-weight: 700;
        transition: all 0.2s;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
    }
    .btn-premium-save:hover {
        background: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        color: white;
    }
    .btn-premium-cancel {
        background: #f1f5f9;
        color: #64748b;
        border-radius: 12px;
        padding: 0.8rem 1.5rem;
        font-weight: 700;
        transition: all 0.2s;
    }
    .btn-premium-cancel:hover {
        background: #e2e8f0;
        color: #475569;
    }
    .info-box {
        background: #eef2ff;
        border-radius: 12px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #4f46e5;
        font-size: 0.8rem;
    }
</style>
@endpush

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-8">
        
        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-1">✏ Edit User: {{ $user->name }}</h4>
            <p class="text-muted small">Perbarui informasi akun atau hak akses pengguna ini.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div class="small">
                    <strong>Mohon koreksi data berikut:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="premium-card">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('superadmin.users.update', $user->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-premium">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                   class="form-control premium-input @error('name') is-invalid @enderror" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-premium">Username</label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" 
                                   class="form-control premium-input @error('username') is-invalid @enderror" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-premium">Level Akses (Role)</label>
                        <select name="role" class="form-select premium-input @error('role') is-invalid @enderror" required>
                            <option value="admin" {{ $user->role=='admin'?'selected':'' }}>Admin</option>
                            <option value="superadmin" {{ $user->role=='superadmin'?'selected':'' }}>Super Admin</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-premium">Departemen</label>
                        <select name="department_id" class="form-select premium-input @error('department_id') is-invalid @enderror" required>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ $user->department_id == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="security-section pt-3 border-top mt-5">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock me-2 text-primary"></i> Pengaturan Keamanan</h6>
                        
                        <div class="info-box mb-4">
                            <i class="bi bi-info-circle-fill fs-5"></i>
                            <div>Kosongkan kolom password di bawah ini jika Anda tidak ingin mengubah password user.</div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label-premium">Password Baru (Opsional)</label>
                                <input type="password" name="password" class="form-control premium-input" placeholder="Masukkan password baru jika ingin mengganti">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-5 pt-3">
                        <a href="{{ route('superadmin.users.index') }}" class="btn-premium-cancel text-decoration-none">
                            Batal
                        </a>
                        <button type="submit" class="btn-premium-save">
                            <i class="bi bi-arrow-repeat me-2"></i> Update Data User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

