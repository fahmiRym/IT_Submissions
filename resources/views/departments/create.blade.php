@extends('layouts.app')

@section('title', 'Tambah Departemen')
@section('page-title', '➕ Tambah Departemen')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">

        <form method="POST" action="{{ route('superadmin.departments.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nama Departemen</label>
                <input type="text"
                       name="name"
                       class="form-control"
                       required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    💾 Simpan
                </button>

                <a href="{{ route('superadmin.departments.index') }}"
                   class="btn btn-secondary">
                    Kembali
                </a>
            </div>

        </form>

    </div>
</div>

@endsection
