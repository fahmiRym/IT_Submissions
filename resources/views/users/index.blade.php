@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', '👤 Manajemen User')

@push('styles')
<style>
    /* Premium Stats Cards */
    .stat-card {
        border-radius: 20px;
        border: none;
        overflow: hidden;
        position: relative;
        transition: transform 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .mesh-gradient {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: radial-gradient(at 0% 0%, rgba(255,255,255,0.15) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, rgba(255,255,255,0.1) 0px, transparent 50%);
        z-index: 1;
    }
    
    /* Table Styling */
    .table-card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .table thead th {
        background-color: #f8fafc;
        padding: 1.25rem 1rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 800;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }
    .table tbody td {
        padding: 1rem 1rem;
        vertical-align: middle;
    }
    
    .avatar-user {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    /* Action Buttons */
    .btn-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .btn-edit { background: #fefce8; color: #854d0e; }
    .btn-edit:hover { background: #fef9c3; color: #713f12; transform: scale(1.1); }
    .btn-delete { background: #fef2f2; color: #991b1b; }
    .btn-delete:hover { background: #fee2e2; color: #7f1d1d; transform: scale(1.1); }
</style>
@endpush

@section('content')

{{-- STATS SECTION --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
            <div class="mesh-gradient"></div>
            <div class="card-body p-4 position-relative" style="z-index: 2;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill">Total User</span>
                </div>
                <h2 class="fw-bold mb-1">{{ number_format($users->count()) }}</h2>
                <p class="mb-0 small opacity-75 text-uppercase fw-bold">User Terdaftar</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
            <div class="mesh-gradient"></div>
            <div class="card-body p-4 position-relative" style="z-index: 2;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="bi bi-person-gear fs-4"></i>
                    </div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill">Staff Admin</span>
                </div>
                <h2 class="fw-bold mb-1">{{ number_format($totalAdmin) }}</h2>
                <p class="mb-0 small opacity-75 text-uppercase fw-bold">Role Admin</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
            <div class="mesh-gradient"></div>
            <div class="card-body p-4 position-relative" style="z-index: 2;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="bi bi-shield-lock fs-4"></i>
                    </div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill">Superadmin</span>
                </div>
                <h2 class="fw-bold mb-1">{{ number_format($totalSuper) }}</h2>
                <p class="mb-0 small opacity-75 text-uppercase fw-bold">Role Superadmin</p>
            </div>
        </div>
    </div>
     <div class="col-md-3">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="mesh-gradient"></div>
            <div class="card-body p-4 position-relative" style="z-index: 2;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="bi bi-lightning-charge fs-4"></i>
                    </div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill">Latest</span>
                </div>
                <h4 class="fw-bold mb-1 text-truncate">{{ $latestUser }}</h4>
                <p class="mb-0 small opacity-75 text-uppercase fw-bold">User Terbaru</p>
            </div>
        </div>
    </div>
</div>

{{-- ACTION & SEARCH --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Daftar Pengguna Sistem</h5>
        <p class="text-muted small mb-0">Manajemen akun, role, dan hak akses penugasan</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="bi bi-person-plus-fill me-2"></i> Tambah User Baru
    </button>
</div>

{{-- DATA TABLE --}}
<div class="card table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="70">#</th>
                        <th>User & Username</th>
                        <th>Role / Level</th>
                        <th>Departemen Penugasan</th>
                        <th class="text-center" width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td class="ps-4 text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($u->photo && file_exists(public_path('profile_photos/' . $u->photo)))
                                    <img src="{{ asset('profile_photos/' . $u->photo) }}" alt="{{ $u->name }}" class="avatar-user me-3 rounded-circle shadow-sm" style="object-fit: cover;">
                                @else
                                    <div class="avatar-user me-3 {{ $u->role == 'superadmin' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary' }}">
                                        {{ substr(strtoupper($u->name), 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $u->name }}</div>
                                    <div class="font-monospace text-muted" style="font-size: 0.75rem;">@<span>{{ $u->username }}</span></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleC = match($u->role) {
                                    'superadmin' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fca5a5', 'icon' => 'bi-shield-fill-check'],
                                    'admin'      => ['bg' => '#f0f9ff', 'text' => '#075985', 'border' => '#7dd3fc', 'icon' => 'bi-person-fill-gear'],
                                    default      => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0', 'icon' => 'bi-person'],
                                };
                            @endphp
                            <span class="badge rounded-pill border py-2 px-3 d-inline-flex align-items-center gap-1" style="background: {{ $roleC['bg'] }}; color: {{ $roleC['text'] }}; border-color: {{ $roleC['border'] }} !important; font-size: 0.7rem; font-weight: 800;">
                                <i class="{{ $roleC['icon'] }}"></i> {{ strtoupper($u->role) }}
                            </span>
                        </td>
                        <td>
                            @if($u->department)
                                <div class="d-flex align-items-center text-muted">
                                    <i class="bi bi-building me-2 opacity-50"></i>
                                    <span class="fw-bold" style="font-size: 0.85rem;">{{ $u->department->name }}</span>
                                </div>
                            @else
                                <span class="text-muted small italic opacity-50">- No Dept -</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn-icon btn-edit" onclick="editUser({{ $u }})" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('superadmin.users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-icon btn-delete" title="Hapus">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="opacity-20 mb-3"><i class="bi bi-person-x display-1"></i></div>
                            <h6 class="fw-bold text-muted">Belum ada user terdaftar</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL CREATE --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3 mt-2 mx-2 rounded-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Tambah User Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('superadmin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-md-6 text-start">
                            <label class="form-label small fw-bold text-uppercase">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control bg-light border-0 py-2" placeholder="Ex: John Doe" required>
                        </div>
                        <div class="col-md-6 text-start">
                            <label class="form-label small fw-bold text-uppercase">Username</label>
                            <input type="text" name="username" class="form-control bg-light border-0 py-2" placeholder="Ex: john_admin" required>
                        </div>
                        <div class="col-md-6 text-start">
                            <label class="form-label small fw-bold text-uppercase">Password</label>
                            <input type="password" name="password" class="form-control bg-light border-0 py-2" placeholder="Minimal 6 karakter" required>
                        </div>
                        <div class="col-md-6 text-start">
                            <label class="form-label small fw-bold text-uppercase">Level Akses (Role)</label>
                            <select name="role" class="form-select bg-light border-0 py-2" required>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                        </div>
                        <div class="col-12 text-start">
                            <label class="form-label small fw-bold text-uppercase">Departemen Penugasan</label>
                            <select name="department_id" class="form-select bg-light border-0 py-2" required>
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-warning text-dark border-0 py-3 mt-2 mx-2 rounded-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4 p-md-5">
                    <div class="row g-4 text-start">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">Nama Lengkap</label>
                            <input type="text" name="name" id="editName" class="form-control bg-light border-0 py-2" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">Username</label>
                            <input type="text" name="username" id="editUsername" class="form-control bg-light border-0 py-2" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">Ganti Password (Opsional)</label>
                            <input type="password" name="password" class="form-control bg-light border-0 py-2" placeholder="Kosongkan jika tetap">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">Level Akses (Role)</label>
                            <select name="role" id="editRole" class="form-select bg-light border-0 py-2" required>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase">Departemen Penugasan</label>
                            <select name="department_id" id="editDeptId" class="form-select bg-light border-0 py-2" required>
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function editUser(user) {
        let url = "{{ route('superadmin.users.update', ':id') }}";
        url = url.replace(':id', user.id);
        $('#formEdit').attr('action', url);
        $('#editName').val(user.name);
        $('#editUsername').val(user.username);
        $('#editRole').val(user.role);
        $('#editDeptId').val(user.department_id);
        $('#modalEdit').modal('show');
    }
</script>
@endpush
