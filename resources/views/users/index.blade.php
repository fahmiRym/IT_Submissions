@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', '👤 Manajemen User')

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

    .role-badge {
        font-size: 0.75rem;
        padding: 0.35em 0.8em;
        border-radius: 6px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .role-superadmin { background-color: #fee2e2; color: #991b1b; }
    .role-admin { background-color: #dbeafe; color: #1e40af; }
    .role-user { background-color: #f3f4f6; color: #374151; }

    .avatar-initial {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: #f3f4f6;
        color: #4b5563;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
</style>
@endpush

@section('content')

{{-- HEADER & ACTIONS --}}
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold text-dark mb-1">👤 Manajemen User</h4>
        <p class="text-muted small mb-0">Kelola akun pengguna dan hak akses sistem.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-person-plus-fill me-2"></i> Tambah User
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Daftar Pengguna Aktif</h6>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" width="60">No</th>
                                <th>User Info</th>
                                <th>Username / Login ID</th>
                                <th>Role</th>
                                <th>Departemen</th>
                                <th class="text-center" width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $u)
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initial me-3 bg-{{ $u->role == 'superadmin' ? 'danger' : 'primary' }} text-white bg-opacity-75">
                                            {{ substr(strtoupper($u->name), 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $u->name }}</div>
                                            <div class="small text-muted">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $u->username }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $roleClass = match($u->role) {
                                            'superadmin' => 'role-superadmin',
                                            'admin' => 'role-admin',
                                            default => 'role-user'
                                        };
                                    @endphp
                                    <span class="role-badge {{ $roleClass }}">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td>
                                    @if($u->department)
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-building text-muted me-2"></i>
                                            <span>{{ $u->department->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('superadmin.users.edit',$u->id) }}" 
                                           class="btn-action btn-action-edit" title="Edit User">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        
                                        <form action="{{ route('superadmin.users.destroy',$u->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini? Akses mereka akan dicabut permanen.')">
                                            @csrf @method('DELETE')
                                            <button class="btn-action btn-action-delete" title="Hapus User">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-3 opacity-25"></i>
                                        Belum ada data user.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if(method_exists($users, 'links'))
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
