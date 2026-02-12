@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', '👤 Manajemen User')

@push('styles')
<style>
    .premium-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        background: #fff;
        overflow: hidden;
    }
    
    .table-premium thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        padding: 1.25rem 1rem;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .table-premium tbody td {
        padding: 1.1rem 1rem;
        vertical-align: middle;
        color: #1e293b;
        font-size: 0.88rem;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }
    
    .table-premium tbody tr:hover td {
        background-color: #fbfcfd;
    }
    
    .avatar-sm-circle {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .bg-indigo-soft { background-color: #eef2ff; color: #4f46e5; }
    .bg-rose-soft { background-color: #fff1f2; color: #e11d48; }
    
    .status-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .badge-superadmin { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .badge-admin { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    
    .btn-icon-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        font-size: 0.9rem;
    }
    
    .btn-edit-soft { background: #fef3c7; color: #d97706; }
    .btn-edit-soft:hover { background: #fbbf24; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(217, 119, 6, 0.2); }
    
    .btn-delete-soft { background: #fee2e2; color: #dc2626; }
    .btn-delete-soft:hover { background: #ef4444; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2); }

    .search-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    
    .search-box:focus {
        background: #fff;
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.05);
        outline: none;
    }

    .add-user-btn {
        background: #4f46e5;
        color: white;
        border-radius: 12px;
        padding: 0.7rem 1.5rem;
        font-weight: 700;
        border: none;
        transition: all 0.2s;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
    }
    
    .add-user-btn:hover {
        background: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        color: white;
    }
</style>
@endpush

@section('content')

{{-- TOP HEADER SECTION --}}
<div class="row align-items-center mb-4 g-3">
    <div class="col-md-6">
        <h4 class="fw-bold text-dark mb-1">👤 Manajemen User</h4>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">{{ $users->count() }} Total Pengguna</span>
            <span class="text-muted small">• Terdaftar dalam sistem</span>
        </div>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('superadmin.users.create') }}" class="add-user-btn text-decoration-none d-inline-flex align-items-center">
            <i class="bi bi-person-plus-fill me-2"></i> Tambah User Baru
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center animate__animated animate__fadeInDown">
                <i class="bi bi-check-circle-fill fs-5 me-3"></i>
                <div class="fw-bold">{{ session('success') }}</div>
            </div>
        @endif

        <div class="premium-card">
            <div class="card-header bg-white border-0 py-4 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
                        <i class="bi bi-list-stars me-2 text-primary fs-5"></i> Daftar Pengguna
                    </h6>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-premium mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="70">NO</th>
                            <th>NAMA & EMAIL</th>
                            <th>USERNAME</th>
                            <th>ROLE</th>
                            <th>DEPARTEMEN</th>
                            <th class="text-center" width="130">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                        <tr>
                            <td class="text-center">
                                <span class="fw-bold text-muted small">{{ $loop->iteration }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm-circle me-3 {{ $u->role == 'superadmin' ? 'bg-rose-soft' : 'bg-indigo-soft' }}">
                                        {{ substr(strtoupper($u->name), 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.92rem;">{{ $u->name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $u->email ?: 'Email tidak ada' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="bg-light px-3 py-1 rounded-pill d-inline-block font-monospace border" style="font-size: 0.78rem; font-weight: 600;">
                                    {{ $u->username }}
                                </div>
                            </td>
                            <td>
                                @if($u->role == 'superadmin')
                                    <span class="status-badge badge-superadmin">
                                        <i class="bi bi-shield-fill-check"></i> Superadmin
                                    </span>
                                @else
                                    <span class="status-badge badge-admin">
                                        <i class="bi bi-person-fill-gear"></i> Admin
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($u->department)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="p-1 rounded bg-light border text-secondary">
                                            <i class="bi bi-building" style="font-size: 0.75rem;"></i>
                                        </div>
                                        <span class="fw-medium text-secondary" style="font-size: 0.85rem;">{{ $u->department->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small italic">N/A</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('superadmin.users.edit',$u->id) }}" 
                                       class="btn-icon-action btn-edit-soft" title="Edit Pengguna">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    
                                    <form action="{{ route('superadmin.users.destroy',$u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon-action btn-delete-soft" title="Hapus Pengguna">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <img src="{{ asset('img/no-data.svg') }}" alt="No Data" class="opacity-25 mb-3" style="width: 120px;">
                                    <h6 class="text-muted">Belum ada data user dalam sistem.</h6>
                                    <a href="{{ route('superadmin.users.create') }}" class="btn btn-sm btn-primary rounded-pill mt-2">
                                        Tambah User Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(method_exists($users, 'links'))
            <div class="card-footer bg-white border-0 py-4 px-4">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection


@endsection
