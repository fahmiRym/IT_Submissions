@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', '👤 Manajemen User')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body, .card, .table, button, input, select { font-family: 'Outfit', sans-serif !important; }
    .stat-card { border: none; border-radius: 20px; overflow: hidden; position: relative; transition: transform 0.25s ease, box-shadow 0.25s ease; box-shadow: 0 4px 20px rgba(0,0,0,0.07); }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.13); }
    .stat-card .mesh { position: absolute; inset: 0; z-index: 1; background: radial-gradient(circle at 10% 10%, rgba(255,255,255,0.18) 0%, transparent 60%), radial-gradient(circle at 90% 90%, rgba(255,255,255,0.1) 0%, transparent 50%); }
    .stat-card .card-body { z-index: 2; position: relative; }
    .stat-icon { width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    .stat-value { font-size: 2.2rem; font-weight: 800; letter-spacing: -1px; line-height: 1; }
    .stat-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75; }
    .table-card { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.05); }
    .table-card .card-header-section { padding: 1.5rem 1.75rem; background: white; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .section-title { font-size: 1.05rem; font-weight: 800; color: #1e293b; margin: 0; }
    .section-sub { font-size: 0.78rem; color: #94a3b8; margin: 2px 0 0; font-weight: 500; }
    .table thead th { background: #f8fafc; color: #64748b; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.07em; padding: 1rem 1.25rem; border-bottom: 2px solid #f1f5f9; }
    .table tbody tr { border-bottom: 1px solid #f8fafc; transition: background 0.15s; }
    .table tbody tr:hover { background: #fafbff; }
    .table tbody td { padding: 0.9rem 1.25rem; vertical-align: middle; color: #334155; font-weight: 500; }
    .avatar-user { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .btn-act { width: 36px; height: 36px; border-radius: 10px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; transition: all 0.2s ease; }
    .btn-act-toggle-off { background: #fef2f2; color: #dc2626; }
    .btn-act-toggle-off:hover { background: #fee2e2; transform: scale(1.1); box-shadow: 0 4px 8px rgba(220,38,38,0.2); }
    .btn-act-toggle-on { background: #f0fdf4; color: #16a34a; }
    .btn-act-toggle-on:hover { background: #dcfce7; transform: scale(1.1); box-shadow: 0 4px 8px rgba(22,163,74,0.2); }
    .btn-act-edit { background: #fffbeb; color: #d97706; }
    .btn-act-edit:hover { background: #fef9c3; transform: scale(1.1); box-shadow: 0 4px 8px rgba(217,119,6,0.2); }
    .btn-act-delete { background: #fef2f2; color: #dc2626; }
    .btn-act-delete:hover { background: #fee2e2; transform: scale(1.1); box-shadow: 0 4px 8px rgba(220,38,38,0.2); }
    .btn-add { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white; border: none; border-radius: 12px; padding: 0.6rem 1.4rem; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(14,165,233,0.3); transition: all 0.2s ease; }
    .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(14,165,233,0.4); color: white; }
    .badge-status { font-size: 0.72rem; font-weight: 700; border-radius: 30px; padding: 0.3rem 0.8rem; letter-spacing: 0.03em; }
    .modal-content { border: none; border-radius: 20px; overflow: hidden; }
    .modal-header-custom { padding: 1.25rem 1.5rem; margin: 10px; border-radius: 14px; border: none; display: flex; align-items: center; gap: 0.75rem; }
    .modal-body { padding: 1.5rem; }
    .form-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.4rem; }
    .form-control, .form-select { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.65rem 0.9rem; font-family: 'Outfit', sans-serif; font-size: 0.9rem; transition: border-color 0.2s, box-shadow 0.2s; background: #f8fafc; }
    .form-control:focus, .form-select:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.12); background: white; }
    .btn-cancel { background: #f1f5f9; color: #64748b; border: none; border-radius: 10px; padding: 0.6rem 1.4rem; font-weight: 600; font-size: 0.88rem; transition: all 0.2s; }
    .btn-cancel:hover { background: #e2e8f0; }
    .btn-save { border: none; border-radius: 10px; padding: 0.6rem 1.4rem; font-weight: 700; font-size: 0.88rem; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(0,0,0,0.15); }
</style>
@endpush

@section('content')

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg,#4f46e5,#3730a3);">
            <div class="mesh"></div>
            <div class="card-body p-4 d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill fw-semibold" style="font-size:0.7rem;">Total</span>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($users->count()) }}</div>
                    <div class="stat-label mt-1">User Terdaftar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg,#0ea5e9,#0284c7);">
            <div class="mesh"></div>
            <div class="card-body p-4 d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-icon"><i class="bi bi-person-fill-gear"></i></div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill fw-semibold" style="font-size:0.7rem;">Admin</span>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($totalAdmin) }}</div>
                    <div class="stat-label mt-1">Role Admin</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg,#ef4444,#dc2626);">
            <div class="mesh"></div>
            <div class="card-body p-4 d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-icon"><i class="bi bi-shield-lock-fill"></i></div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill fw-semibold" style="font-size:0.7rem;">Super</span>
                </div>
                <div>
                    <div class="stat-value">{{ number_format($totalSuper) }}</div>
                    <div class="stat-label mt-1">Role Superadmin</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-white h-100" style="background: linear-gradient(135deg,#10b981,#059669);">
            <div class="mesh"></div>
            <div class="card-body p-4 d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                    <span class="badge bg-white bg-opacity-20 rounded-pill fw-semibold" style="font-size:0.7rem;">Latest</span>
                </div>
                <div>
                    <div class="fw-bold text-truncate" style="font-size:1rem;letter-spacing:-0.3px;">{{ $latestUser ?: '-' }}</div>
                    <div class="stat-label mt-1">User Terbaru</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header-section">
        <div>
            <p class="section-title">Daftar Pengguna Sistem</p>
            <p class="section-sub">Manajemen akun, role, dan hak akses penugasan</p>
        </div>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalCreate">
            <i class="bi bi-person-plus-fill"></i> Tambah User Baru
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="60">#</th>
                        <th>User & Username</th>
                        <th>Role / Level</th>
                        <th>Departemen Penugasan</th>
                        <th class="text-center pe-4" width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td class="ps-4 fw-bold text-secondary">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($u->photo && file_exists(public_path('profile_photos/' . $u->photo)))
                                    <img src="{{ asset('profile_photos/' . $u->photo) }}" alt="{{ $u->name }}"
                                         class="avatar-user rounded-circle" style="object-fit:cover;border-radius:50%!important;">
                                @else
                                    <div class="avatar-user {{ $u->role == 'superadmin' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary' }}">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark {{ !$u->is_active ? 'text-decoration-line-through' : '' }}">{{ $u->name }}</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="text-muted" style="font-size:0.78rem; font-family:'Courier New',monospace;">{{ $u->username }}</span>
                                        @if($u->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2" style="font-size:0.6rem;">Aktif</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2" style="font-size:0.6rem;">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleC = match($u->role) {
                                    'superadmin' => ['bg'=>'#fef2f2','text'=>'#991b1b','border'=>'#fca5a5','icon'=>'bi-shield-fill-check'],
                                    'admin'      => ['bg'=>'#f0f9ff','text'=>'#075985','border'=>'#7dd3fc','icon'=>'bi-person-fill-gear'],
                                    default      => ['bg'=>'#f8fafc','text'=>'#475569','border'=>'#e2e8f0','icon'=>'bi-person'],
                                };
                            @endphp
                            <span class="badge rounded-pill border py-2 px-3 d-inline-flex align-items-center gap-1 {{ !$u->is_active ? 'opacity-50' : '' }}"
                                  style="background:{{ $roleC['bg'] }};color:{{ $roleC['text'] }};border-color:{{ $roleC['border'] }}!important;font-size:0.7rem;font-weight:800;font-family:'Outfit',sans-serif;">
                                <i class="{{ $roleC['icon'] }}"></i> {{ strtoupper($u->role) }}
                            </span>
                        </td>
                        <td>
                            @if($u->department)
                                <div class="d-flex align-items-center gap-2 text-muted {{ !$u->is_active ? 'opacity-50' : '' }}">
                                    <i class="bi bi-building opacity-50"></i>
                                    <span class="fw-semibold" style="font-size:0.85rem;">{{ $u->department->name }}</span>
                                </div>
                            @else
                                <span class="text-muted small fst-italic opacity-50">— No Dept —</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                @if($u->id !== auth()->id())
                                <form action="{{ route('superadmin.users.toggle', $u->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        class="btn-act {{ $u->is_active ? 'btn-act-toggle-off' : 'btn-act-toggle-on' }}">
                                        <i class="bi {{ $u->is_active ? 'bi-slash-circle-fill' : 'bi-check-circle-fill' }}"></i>
                                    </button>
                                </form>
                                @endif
                                <button class="btn-act btn-act-edit" title="Edit" onclick="editUser({{ $u }})">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form action="{{ route('superadmin.users.destroy', $u->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus user {{ addslashes($u->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-act btn-act-delete" title="Hapus"><i class="bi bi-trash3-fill"></i></button>
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
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom" style="background: linear-gradient(135deg,#0ea5e9,#0284c7);">
                <div class="bg-white bg-opacity-20 rounded-3 p-2"><i class="bi bi-person-plus-fill text-white fs-5"></i></div>
                <h5 class="modal-title fw-bold text-white mb-0">Tambah User Baru</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('superadmin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Contoh: john_admin" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Level Akses (Role)</label>
                            <select name="role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Departemen Penugasan</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">— Pilih Departemen —</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-save text-white" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);">
                        <i class="bi bi-person-check-fill me-1"></i> Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
                <div class="bg-white bg-opacity-20 rounded-3 p-2"><i class="bi bi-pencil-square text-white fs-5"></i></div>
                <h5 class="modal-title fw-bold text-white mb-0">Edit Data User</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" id="editUsername" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ganti Password <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Level Akses (Role)</label>
                            <select name="role" id="editRole" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Departemen Penugasan</label>
                            <select name="department_id" id="editDeptId" class="form-select" required>
                                <option value="">— Pilih Departemen —</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-save text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="bi bi-check-lg me-1"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function editUser(user) {
        let url = "{{ route('superadmin.users.update', ':id') }}".replace(':id', user.id);
        $('#formEdit').attr('action', url);
        $('#editName').val(user.name);
        $('#editUsername').val(user.username);
        $('#editRole').val(user.role);
        $('#editDeptId').val(user.department_id);
        $('#modalEdit').modal('show');
    }
</script>
@endpush
