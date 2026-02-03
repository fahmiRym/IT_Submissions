@extends('layouts.app')

@section('title', 'Edit Unit')
@section('page-title', '✏ Edit Unit')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.units.update', $unit->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama Unit</label>
                <input type="text" name="name"
                       value="{{ $unit->name }}"
                       class="form-control"
                       required>
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="{{ route('superadmin.units.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

@endsection
