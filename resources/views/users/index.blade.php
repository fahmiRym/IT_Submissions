@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', '👤 Manajemen User')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body, .card, .table, button, input, select { font-family: 'Outfit', sans-serif !important; }
    /* ── STAT CARDS (Dashboard Style) ── */
    .card-stat-vibrant {
        border: none;
        border-radius: 16px;
        color: white;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .card-stat-vibrant:hover { 
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
    }
    .stat-overlay-icon {
        position: absolute;
        right: -15px;
        bottom: -15px;
        font-size: 7rem;
        opacity: 0.15;
        transform: rotate(-10deg);
        color: white;
    }
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
    .btn-act-delegate { background: #eef2ff; color: #4f46e5; }
    .btn-act-delegate:hover { background: #e0e7ff; transform: scale(1.1); box-shadow: 0 4px 8px rgba(79,70,229,0.2); }
    .btn-act-delegated { background: linear-gradient(135deg,#fbbf24,#f59e0b); color: #fff; position: relative; }
    .btn-act-delegated:hover { transform: scale(1.1); box-shadow: 0 4px 12px rgba(245,158,11,0.4); }
    .btn-act-delegated::after { content: ''; position: absolute; top: -3px; right: -3px; width: 8px; height: 8px; border-radius: 50%; background: #10b981; border: 2px solid #fff; }
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

@push('styles')
<style>
    @keyframes pulse-live {
        0% { box-shadow: 0 0 0 0 rgba(34,197,94,.5); }
        70% { box-shadow: 0 0 0 8px rgba(34,197,94,0); }
        100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }
    .mini-stat { background: white; border: 1px solid #f1f5f9; border-radius: 14px; padding: 0.85rem 1rem; display: flex; align-items: center; gap: 12px; transition: all .25s; }
    .mini-stat:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(15,23,42,0.06); }
    .mini-stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .mini-stat-val { font-size: 1.4rem; font-weight: 800; line-height: 1; color: #0f172a; }
    .mini-stat-lbl { font-size: 0.65rem; letter-spacing: 0.1em; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-top: 2px; }
</style>
@endpush

@section('content')

{{-- Mini stats strip --}}
<div class="row g-2 mb-4">
    <div class="col-6 col-md">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:#dcfce7; color:#16a34a;"><i class="bi bi-broadcast-pin"></i></div>
            <div><div class="mini-stat-val text-success">{{ $activeNow ?? 0 }}</div><div class="mini-stat-lbl">Online Now</div></div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:#dbeafe; color:#1d4ed8;"><i class="bi bi-calendar-check-fill"></i></div>
            <div><div class="mini-stat-val text-primary">{{ $loggedInToday ?? 0 }}</div><div class="mini-stat-lbl">Login Hari Ini</div></div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-person-plus-fill"></i></div>
            <div><div class="mini-stat-val text-warning">{{ $newThisMonth ?? 0 }}</div><div class="mini-stat-lbl">Baru Bulan Ini</div></div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:#fef3c7; color:#92400e;"><i class="bi bi-calculator-fill"></i></div>
            <div><div class="mini-stat-val" style="color:#92400e;">{{ $totalAccounting ?? 0 }}</div><div class="mini-stat-lbl">Accounting</div></div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:#dcfce7; color:#16a34a;"><i class="bi bi-check2-circle"></i></div>
            <div><div class="mini-stat-val text-success">{{ $totalActive ?? 0 }}</div><div class="mini-stat-lbl">Aktif</div></div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-slash-circle"></i></div>
            <div><div class="mini-stat-val text-danger">{{ $totalInactive ?? 0 }}</div><div class="mini-stat-lbl">Nonaktif</div></div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:#f1f5f9; color:#64748b;"><i class="bi bi-question-circle"></i></div>
            <div><div class="mini-stat-val text-muted">{{ $neverLoggedIn ?? 0 }}</div><div class="mini-stat-lbl">Never Login</div></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card-stat-vibrant h-100 p-4" style="background: linear-gradient(135deg, #4f46e5, #3730a3);">
            <h6 class="text-white-50 text-uppercase small fw-bold mb-2">User Terdaftar</h6>
            <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($users->total()) }}</h2>
            <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-people-fill me-1"></i> TOTAL DATA</div>
            <i class="bi bi-people-fill stat-overlay-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat-vibrant h-100 p-4" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
            <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Role Admin</h6>
            <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($totalAdmin) }}</h2>
            <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-person-fill-gear me-1"></i> ADMIN USER</div>
            <i class="bi bi-person-fill-gear stat-overlay-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat-vibrant h-100 p-4" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
            <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Role Superadmin</h6>
            <h2 class="mb-0 fw-bold display-5 text-white">{{ number_format($totalSuper) }}</h2>
            <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-shield-lock-fill me-1"></i> SYSTEM ADMIN</div>
            <i class="bi bi-shield-lock-fill stat-overlay-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat-vibrant h-100 p-4" style="background: linear-gradient(135deg, #10b981, #059669);">
            <h6 class="text-white-50 text-uppercase small fw-bold mb-2">User Terbaru</h6>
            <h2 class="mb-0 fw-bold text-white text-truncate" style="font-size:2.2rem; line-height:1.2; padding-top:4px;">{{ $latestUser ?: '-' }}</h2>
            <div class="mt-2 text-white-50 small font-monospace"><i class="bi bi-lightning-charge-fill me-1"></i> LATEST ACCOUNT</div>
            <i class="bi bi-lightning-charge-fill stat-overlay-icon"></i>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header-section">
        <div>
            <p class="section-title">Daftar Pengguna Sistem</p>
            <p class="section-sub">Manajemen akun, role, dan hak akses penugasan · Menampilkan {{ $users->count() }} dari {{ $users->total() }}</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <form method="GET" class="d-flex gap-2 align-items-center">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / username / NIK..."
                       class="form-control form-control-sm" style="min-width:230px; border-radius:10px;">
                <select name="role" class="form-select form-select-sm" style="border-radius:10px; min-width:130px;">
                    <option value="">Semua Role</option>
                    @foreach(['admin','superadmin','accounting','spv','kabag','manager'] as $r)
                        <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-light border btn-sm" type="submit" style="border-radius:10px;"><i class="bi bi-search"></i></button>
                @if(request('q') || request('role'))
                    <a href="{{ route('superadmin.users.index') }}" class="btn btn-link btn-sm text-muted p-0" title="Reset"><i class="bi bi-x-circle"></i></a>
                @endif
            </form>
            @include('partials._per_page_select', ['id' => 'perPageUsers'])
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalCreate">
                <i class="bi bi-person-plus-fill"></i> Tambah User Baru
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="60">#</th>
                        <th>User & Username</th>
                        <th>Role / Level</th>
                        <th>Departemen</th>
                        <th class="text-center" width="110">Submission</th>
                        <th class="text-center" width="160">Last Login</th>
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
                                        <span class="text-muted" style="font-size:0.78rem; font-family:'Courier New',monospace;">{{ preg_replace('/^nik_/', '', $u->username) }}</span>
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
                                    'accounting' => ['bg'=>'#fffbeb','text'=>'#92400e','border'=>'#fde68a','icon'=>'bi-calculator-fill'],
                                    'spv'        => ['bg'=>'#ecfeff','text'=>'#155e75','border'=>'#67e8f9','icon'=>'bi-person-badge'],
                                    'kabag'      => ['bg'=>'#fef3c7','text'=>'#854d0e','border'=>'#fcd34d','icon'=>'bi-diagram-3-fill'],
                                    'manager'    => ['bg'=>'#ede9fe','text'=>'#5b21b6','border'=>'#c4b5fd','icon'=>'bi-briefcase-fill'],
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
                        <td class="text-center">
                            @php $sc = $u->submissions_count ?? 0; @endphp
                            <span class="badge rounded-pill px-3 py-2 fw-bold"
                                  style="background:{{ $sc > 0 ? 'linear-gradient(135deg,#dbeafe,#bfdbfe)' : '#f1f5f9' }}; color:{{ $sc > 0 ? '#1d4ed8' : '#94a3b8' }}; font-size:0.78rem;">
                                <i class="bi bi-file-earmark-text me-1"></i>{{ $sc }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($u->last_login_at)
                                @php
                                    $diff = $u->last_login_at->diffInMinutes(now());
                                    $isOnline = $diff < 30;
                                    $color = $isOnline ? '#16a34a' : ($diff < 1440 ? '#0891b2' : '#94a3b8');
                                @endphp
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fw-bold" style="color:{{ $color }}; font-size:0.78rem;">
                                        @if($isOnline)
                                            <span class="d-inline-block rounded-circle me-1" style="width:8px; height:8px; background:#16a34a; box-shadow:0 0 0 0 rgba(34,197,94,.5); animation:pulse-live 1.6s infinite;"></span>
                                            Online
                                        @else
                                            {{ $u->last_login_at->diffForHumans(null, true) }} lalu
                                        @endif
                                    </span>
                                    <small class="text-muted" style="font-size:0.65rem;">{{ $u->last_login_at->format('d/m/Y H:i') }}</small>
                                </div>
                            @else
                                <span class="badge bg-light text-muted border" style="font-size:0.65rem;">
                                    <i class="bi bi-dash-circle me-1"></i>Belum pernah
                                </span>
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
                                @if($u->id !== auth()->id())
                                    @php
                                        $activeDel = $u->activeDelegate();
                                        $delPayload = [
                                            'id' => $u->id,
                                            'name' => $u->name,
                                            'delegate_to_id' => $u->delegate_to_id,
                                            'delegate_to_name' => optional($u->delegateTo)->name,
                                            'delegate_active_from' => optional($u->delegate_active_from)->toDateString(),
                                            'delegate_active_until' => optional($u->delegate_active_until)->toDateString(),
                                            'delegate_reason' => $u->delegate_reason,
                                            'is_active_now' => (bool) $activeDel,
                                        ];
                                        $delJson = htmlspecialchars(json_encode($delPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES);
                                    @endphp
                                    <button type="button"
                                            class="btn-act {{ $activeDel ? 'btn-act-delegated' : 'btn-act-delegate' }}"
                                            title="{{ $activeDel ? 'Delegasi AKTIF ke ' . $activeDel->name : 'Kelola Delegasi TTD' }}"
                                            onclick="openDelegateModal(JSON.parse('{!! $delJson !!}'))">
                                        <i class="bi bi-arrow-return-left"></i>
                                    </button>
                                @endif
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
                        <td colspan="7" class="text-center py-5">
                            <div class="opacity-20 mb-3"><i class="bi bi-person-x display-1"></i></div>
                            <h6 class="fw-bold text-muted">Belum ada user terdaftar</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white border-top px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }} · Total {{ $users->total() }} user</div>
            {{ $users->links() }}
        </div>
    @endif
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
                            <label class="form-label">Password <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="password" name="password" id="createPwd" class="form-control" placeholder="Kosongkan untuk pakai default role">
                            <div class="small mt-1" style="color:#64748b;font-size:0.75rem;">
                                <i class="bi bi-info-circle me-1"></i>Default:
                                <code id="createPwdHint" style="background:#f1f5f9;padding:1px 8px;border-radius:6px;color:#0ea5e9;font-weight:700;">admin123</code>
                                <span class="text-muted" style="font-size:0.7rem;">· wajib diganti saat login pertama</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Level Akses (Role)</label>
                            <select name="role" id="createRole" class="form-select" required>
                                <optgroup label="Eksekutor / Pemohon">
                                    <option value="admin" data-pwd="admin123">Admin</option>
                                    <option value="accounting" data-pwd="acc123">Accounting</option>
                                </optgroup>
                                <optgroup label="Approver / Atasan">
                                    <option value="spv" data-pwd="spv123">Supervisor (SPV)</option>
                                    <option value="kabag" data-pwd="kab123">Kepala Bagian (Kabag)</option>
                                    <option value="manager" data-pwd="man123">Manager</option>
                                </optgroup>
                                <optgroup label="System">
                                    <option value="superadmin" data-pwd="super123">Super Admin</option>
                                </optgroup>
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
                                <optgroup label="Eksekutor / Pemohon">
                                    <option value="admin">Admin</option>
                                    <option value="accounting">Accounting</option>
                                </optgroup>
                                <optgroup label="Approver / Atasan">
                                    <option value="spv">Supervisor (SPV)</option>
                                    <option value="kabag">Kepala Bagian (Kabag)</option>
                                    <option value="manager">Manager</option>
                                </optgroup>
                                <optgroup label="System">
                                    <option value="superadmin">Super Admin</option>
                                </optgroup>
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

{{-- ═══════════════ MODAL DELEGASI TTD ═══════════════ --}}
<div class="modal fade" id="modalDelegate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header-custom" style="background: linear-gradient(135deg,#4f46e5,#7c3aed);">
                <i class="bi bi-arrow-return-left fs-4 text-white"></i>
                <h5 class="modal-title fw-bold text-white mb-0">Kelola Delegasi TTD</h5>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form id="formDelegate" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert border-0 rounded-3 mb-3 d-flex align-items-start gap-2 py-2"
                         style="background:#eef2ff;color:#3730a3;font-size:0.8rem;">
                        <i class="bi bi-info-circle-fill mt-1"></i>
                        <div>
                            Delegasi TTD berlaku otomatis untuk pengajuan BARU. TTD yg ditugaskan ke user ini
                            akan auto-forward ke user pengganti selama window aktif.
                            <b>Pengajuan yg sudah berjalan tidak terpengaruh.</b>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">User yg mendelegasikan</label>
                        <input type="text" id="delOwner" class="form-control" readonly style="background:#f1f5f9;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Delegasi TTD ke <span class="text-danger">*</span></label>
                        <select name="delegate_to_id" id="delTarget" class="form-select" required>
                            <option value="">— Pilih user pengganti —</option>
                            @foreach(\App\Models\User::where('is_active', true)->orderBy('name')->get() as $usr)
                                <option value="{{ $usr->id }}" data-role="{{ $usr->role }}" data-jabatan="{{ $usr->jabatan }}">
                                    {{ $usr->name }}
                                    @if($usr->jabatan) — {{ $usr->jabatan }} @endif
                                    ({{ strtoupper($usr->role) }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted" style="font-size:0.7rem;">Semua TTD approval yg ditugaskan ke user asal akan otomatis diteruskan ke user ini.</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Aktif dari</label>
                            <input type="date" name="delegate_active_from" id="delFrom" class="form-control">
                            <small class="text-muted" style="font-size:0.7rem;">Kosong = mulai hari ini</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Aktif sampai</label>
                            <input type="date" name="delegate_active_until" id="delUntil" class="form-control">
                            <small class="text-muted" style="font-size:0.7rem;">Kosong = tanpa batas akhir</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alasan (Opsional)</label>
                        <input type="text" name="delegate_reason" id="delReason" class="form-control" placeholder="Cuti tahunan / Dinas luar kota / …" maxlength="200">
                    </div>

                    <div id="delActiveNow" class="d-none alert border-0 rounded-3 py-2 mb-0"
                         style="background:#dcfce7;color:#166534;font-size:0.8rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        <b>Delegasi saat ini AKTIF.</b> Semua approval baru akan forward ke pengganti.
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="btnClearDelegate" class="btn btn-outline-danger rounded-3 px-3">
                        <i class="bi bi-x-circle me-1"></i> Cabut Delegasi
                    </button>
                    <button type="submit" class="btn text-white rounded-3 px-4 fw-bold"
                            style="background: linear-gradient(135deg,#4f46e5,#7c3aed);">
                        <i class="bi bi-check-lg me-1"></i> Simpan Delegasi
                    </button>
                </div>
            </form>
            {{-- Form terpisah utk cabut delegasi (HTML tidak boleh nested form) --}}
            <form id="formClearDelegate" method="POST" class="d-none">
                @csrf @method('DELETE')
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

    function openDelegateModal(u) {
        const setUrl = "{{ route('superadmin.users.set-delegate', ':id') }}".replace(':id', u.id);
        const clrUrl = "{{ route('superadmin.users.clear-delegate', ':id') }}".replace(':id', u.id);
        $('#formDelegate').attr('action', setUrl);
        $('#formClearDelegate').attr('action', clrUrl);
        $('#delOwner').val(u.name);
        $('#delTarget').val(u.delegate_to_id || '');
        $('#delFrom').val(u.delegate_active_from || '');
        $('#delUntil').val(u.delegate_active_until || '');
        $('#delReason').val(u.delegate_reason || '');
        $('#delActiveNow').toggleClass('d-none', !u.is_active_now);
        // Exclude self dari opsi
        $('#delTarget option').each(function () {
            $(this).prop('disabled', this.value === String(u.id));
        });
        $('#modalDelegate').modal('show');
    }

    $(document).on('click', '#btnClearDelegate', function () {
        if (confirm('Cabut delegasi TTD untuk user ini?')) {
            $('#formClearDelegate').trigger('submit');
        }
    });

    // Update hint default password mengikuti role yang dipilih
    document.getElementById('createRole')?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const def = opt?.dataset?.pwd || 'user123';
        const hint = document.getElementById('createPwdHint');
        if (hint) hint.textContent = def;
    });
</script>
@endpush
