@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', '✏ Edit User')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">

        {{-- ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.users.update', $user->id) }}">
            @csrf
            @method('PUT')

            {{-- NAMA --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama User</label>
                <input type="text"
                       name="name"
                       value="{{ old('name', $user->name) }}"
                       class="form-control"
                       required>
            </div>

            {{-- USERNAME --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text"
                       name="username"
                       value="{{ old('username', $user->username) }}"
                       class="form-control"
                       required>
            </div>

            {{-- PASSWORD (OPSIONAL) --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Password Baru</label>
                <input type="password"
                       name="password"
                       class="form-control">
                <small class="text-muted">
                    Kosongkan jika tidak ingin mengganti password
                </small>
            </div>

            {{-- ROLE --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" class="form-select" required>
                    <option value="admin" {{ $user->role=='admin'?'selected':'' }}>
                        Admin
                    </option>
                    <option value="superadmin" {{ $user->role=='superadmin'?'selected':'' }}>
                        Super Admin
                    </option>
                </select>
            </div>

            {{-- DEPARTEMEN --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Departemen</label>
                <select name="department_id" class="form-select" required>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}"
                            {{ $user->department_id == $d->id ? 'selected' : '' }}>
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
                <button class="btn btn-warning px-4">
                    💾 Update
                </button>
            </div>

        </form>

    </div>
</div>

@endsection
