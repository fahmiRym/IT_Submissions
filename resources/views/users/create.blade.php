@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', '➕ Tambah User')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">

        {{-- ERROR VALIDATION --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.users.store') }}">
            @csrf

            {{-- NAMA --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama User</label>
                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name') }}"
                       required>
            </div>

            {{-- USERNAME --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text"
                       name="username"
                       class="form-control"
                       value="{{ old('username') }}"
                       required>
                <small class="text-muted">Digunakan untuk login</small>
            </div>

            {{-- PASSWORD --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password"
                       name="password"
                       class="form-control"
                       required>
                <small class="text-muted">Minimal 6 karakter</small>
            </div>

            {{-- ROLE --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" class="form-select" required>
                    <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Admin</option>
                    <option value="superadmin" {{ old('role')=='superadmin'?'selected':'' }}>Super Admin</option>
                </select>
            </div>

            {{-- DEPARTEMEN --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Departemen</label>
                <select name="department_id" class="form-select" required>
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}"
                            {{ old('department_id')==$d->id?'selected':'' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ACTION --}}
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('superadmin.users.index') }}"
                class="btn btn-secondary">
                    ⬅ Kembali
                </a>
                <button class="btn btn-success px-4">
                    💾 Simpan
                </button>
            </div>

        </form>

    </div>
</div>

@endsection
