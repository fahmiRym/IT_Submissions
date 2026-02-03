@extends('layouts.app')

@section('title', 'Tambah Unit')
@section('page-title', '➕ Tambah Unit')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.units.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nama Unit</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('superadmin.units.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

@endsection
