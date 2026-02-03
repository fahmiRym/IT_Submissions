@extends('layouts.app')

@section('title', 'Edit Manager')
@section('page-title', '✏ Edit Manager')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.managers.update', $manager->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama Manager</label>
                <input type="text" name="name"
                       value="{{ $manager->name }}"
                       class="form-control"
                       required>
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="{{ route('superadmin.managers.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

@endsection
