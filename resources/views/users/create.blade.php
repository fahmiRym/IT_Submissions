@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', '➕ Tambah User')

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
</style>
@endpush

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-8">
        
        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-1">➕ Tambah User Baru</h4>
            <p class="text-muted small">Lengkapi formulir di bawah ini untuk mendaftarkan akun baru.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div class="small">
                    <strong>Mohon koreksi data berikut:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="premium-card">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('superadmin.users.store') }}">
                    @csrf

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-premium">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control premium-input @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-premium">Username</label>
                            <input type="text" name="username" class="form-control premium-input @error('username') is-invalid @enderror" 
                                   value="{{ old('username') }}" required placeholder="Contoh: budi_s">
                            <div class="text-muted" style="font-size: 0.7rem; margin-top: 0.4rem;">
                                <i class="bi bi-info-circle me-1"></i> ID unik untuk akses masuk ke sistem.
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-premium">Password</label>
                            <input type="password" name="password" class="form-control premium-input @error('password') is-invalid @enderror" 
                                   required placeholder="••••••••">
                            <div class="text-muted" style="font-size: 0.7rem; margin-top: 0.4rem;">
                                <i class="bi bi-shield-lock me-1"></i> Minimal 6 karakter.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-premium">Level Akses (Role)</label>
                            <select name="role" class="form-select premium-input @error('role') is-invalid @enderror" required>
                                <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Admin</option>
                                <option value="superadmin" {{ old('role')=='superadmin'?'selected':'' }}>Super Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label-premium">Departemen</label>
                        <select name="department_id" class="form-select premium-input @error('department_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Departemen Penugasan --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id')==$d->id?'selected':'' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-3 pt-3">
                        <a href="{{ route('superadmin.users.index') }}" class="btn-premium-cancel text-decoration-none">
                            Batal
                        </a>
                        <button type="submit" class="btn-premium-save">
                            <i class="bi bi-check2-circle me-2"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

