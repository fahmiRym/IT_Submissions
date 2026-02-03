@extends('layouts.app')

@section('title', 'Master Manager')
@section('page-title', '🧑‍💼 Master Manager')

@push('styles')
<style>
    .card-modern {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .table-container {
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #6b7280;
        padding: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        color: #374151;
        font-size: 0.9rem;
    }
    
    .table tbody tr:hover {
        background-color: #f9fafb;
    }
    
    .btn-action {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: none;
        transition: all 0.2s;
    }
    .btn-action:hover {
        transform: translateY(-2px);
    }
    .btn-action-edit { background-color: #fef3c7; color: #d97706; }
    .btn-action-edit:hover { background-color: #fde68a; color: #b45309; }
    
    .btn-action-delete { background-color: #fee2e2; color: #dc2626; }
    .btn-action-delete:hover { background-color: #fecaca; color: #b91c1c; }

    .avatar-initial {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background-color: #e0e7ff;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')

{{-- HEADER & ACTIONS --}}
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold text-dark mb-1">🧑‍💼 Master Manager</h4>
        <p class="text-muted small mb-0">Manajemen data manager dan pimpinan unit.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('superadmin.managers.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-person-plus-fill me-2"></i> Tambah Manager
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Daftar Manager</h6>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" width="80">#</th>
                                <th>Nama Manager</th>
                                <th>Tanggal Dibuat</th>
                                <th class="text-center" width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($managers as $m)
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initial me-3">
                                            {{ substr(strtoupper($m->name), 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $m->name }}</div>
                                            <div class="small text-muted">ID: {{ $m->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        {{ $m->created_at ? $m->created_at->format('d M Y') : '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('superadmin.managers.edit',$m->id) }}" 
                                           class="btn-action btn-action-edit" title="Edit Manager">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        
                                        <form action="{{ route('superadmin.managers.destroy',$m->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus manager ini? Data yang terkait mungkin akan terpengaruh.')">
                                            @csrf @method('DELETE')
                                            <button class="btn-action btn-action-delete" title="Hapus Permanen">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-person-x fs-1 d-block mb-3 opacity-25"></i>
                                        Belum ada data manager.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if(method_exists($managers, 'links'))
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $managers->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
