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
                       value="{{ $department->name }}"
                       class="form-control"
                       required>
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="{{ route('superadmin.departments.index') }}"
               class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

@endsection
