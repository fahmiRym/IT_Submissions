@extends('layouts.app')

@section('title', 'Edit Departemen')
@section('page-title', '✏ Edit Departemen')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.departments.update', $department->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama Departemen</label>
                <input type="text" name="name"
                       value="{{ old('name', $department->name) }}"
                       class="form-control"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Cabang / Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">— Tidak di-set (pakai default global) —</option>
                    @foreach($branches ?? [] as $b)
                        <option value="{{ $b->id }}" {{ old('branch_id', $department->branch_id) == $b->id ? 'selected' : '' }}>
                            {{ $b->name }} ({{ $b->kota }})
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Kota cabang dipakai stamp dokumen pengajuan dept ini.</small>
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="{{ route('superadmin.departments.index') }}"
               class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

@endsection
