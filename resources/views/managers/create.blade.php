@extends('layouts.app')

@section('title', 'Tambah Manager')
@section('page-title', '➕ Tambah Manager')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.managers.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nama Manager</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('superadmin.managers.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

@endsection
