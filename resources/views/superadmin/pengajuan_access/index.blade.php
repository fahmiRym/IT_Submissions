@extends('layouts.app')

@section('title', 'Akses Pengajuan Per Role')
@section('page-title', '🔐 Akses Pengajuan Per Role')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body, .card, .table, button, input, select { font-family: 'Outfit', sans-serif !important; }

    .info-banner {
        background: linear-gradient(135deg, #eef2ff, #ede9fe);
        border: 1px solid #c4b5fd;
        border-radius: 16px;
        padding: 14px 18px;
        color: #4c1d95;
        font-size: 0.85rem;
        display: flex; align-items: start; gap: 12px;
        margin-bottom: 1.25rem;
    }
    .info-banner i { font-size: 1.15rem; color: #7c3aed; flex-shrink: 0; margin-top: 2px; }
    .info-banner b { color: #312e81; }

    .matrix-card { border:none; border-radius:20px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.05); background:white; }
    .matrix-card-head {
        padding:1.4rem 1.75rem; border-bottom:1px solid #f1f5f9;
        display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;
    }
    .matrix-card-head .h-title { font-size:1.05rem; font-weight:800; color:#1e293b; margin:0; }
    .matrix-card-head .h-sub   { font-size:0.78rem; color:#94a3b8; margin:2px 0 0; font-weight:500; }
    .btn-save-all {
        background: linear-gradient(135deg, #4f46e5, #3730a3);
        color: white; border: none; border-radius: 12px;
        padding: 0.7rem 1.4rem; font-weight: 800; font-size: 0.88rem;
        letter-spacing: 0.3px;
        display: inline-flex; align-items: center; gap: 8px;
        box-shadow: 0 8px 20px rgba(79,70,229,0.35); transition: all 0.25s;
    }
    .btn-save-all:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(79,70,229,0.45); color: white; }

    .table thead th { background:#f8fafc; color:#64748b; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.07em; padding:1rem 0.85rem; border-bottom:2px solid #f1f5f9; text-align:center; }
    .table thead th.left { text-align:left; padding-left:1.75rem; }
    .table tbody tr { border-bottom:1px solid #f8fafc; transition:background 0.15s; }
    .table tbody tr:hover { background:#fafbff; }
    .table tbody td { padding:1.1rem 0.85rem; vertical-align:middle; }

    .role-cell { display:flex; align-items:center; gap:14px; padding-left:1.75rem; }
    .role-icon-wrap {
        width:46px; height:46px; border-radius:13px;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0; font-size:1.3rem;
    }
    .role-name { font-weight:800; font-size:0.95rem; color:#0f172a; line-height:1.2; }
    .role-meta { font-size:0.7rem; color:#94a3b8; margin-top:2px; }
    .role-meta .pill { background:#eef2ff; color:#3730a3; padding:2px 7px; border-radius:6px; font-weight:700; }

    /* Checkbox card per jenis */
    .jenis-check {
        position: relative;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
        cursor: pointer;
        padding: 10px 6px;
        border-radius: 13px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        transition: all .2s;
        width: 100%;
        min-height: 76px;
        font-size: 0.65rem;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        text-align: center;
    }
    .jenis-check input { position: absolute; opacity: 0; pointer-events: none; }
    .jenis-check .ji { font-size: 1.25rem; }
    .jenis-check:hover { border-color: #c7d2fe; background: white; transform: translateY(-1px); }
    .jenis-check.checked { background: white; color: #1e293b; box-shadow: 0 4px 10px rgba(79,70,229,0.12); }
    .jenis-check.checked .ji { transform: scale(1.08); }
    .jenis-check .check-tick {
        position: absolute; top: 5px; right: 5px;
        width: 16px; height: 16px; border-radius: 5px;
        background: #fff; border: 1.5px solid #cbd5e1;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.6rem; color: white; font-weight: 900;
    }
    .jenis-check.checked .check-tick { background: #4f46e5; border-color: #4f46e5; }
    .jenis-check.checked .check-tick::after { content: '✓'; }

    .row-actions { display:flex; flex-direction:column; gap:5px; padding-right:1.75rem; }
    .btn-mini {
        background: transparent; border: 1px solid #e2e8f0; border-radius: 8px;
        padding: 0.32rem 0.6rem; font-size: 0.68rem; font-weight: 700;
        color: #475569; display: inline-flex; align-items: center; justify-content: center; gap: 4px;
        transition: all .2s;
    }
    .btn-mini:hover { background: #f1f5f9; color: #0f172a; }
    .btn-mini.danger:hover { background: #fef2f2; color: #dc2626; border-color: #fca5a5; }

    .access-count {
        display:inline-flex; align-items:center; gap:5px;
        padding:4px 10px; border-radius:9px;
        font-size:0.72rem; font-weight:800; letter-spacing:0.04em;
    }
    .access-count.full { background:#dcfce7; color:#15803d; }
    .access-count.zero { background:#fee2e2; color:#991b1b; }
    .access-count.partial { background:#dbeafe; color:#1e3a8a; }

    .save-floating-hint {
        background: rgba(79,70,229,0.08);
        color: #4338ca;
        padding: 6px 11px;
        border-radius: 9px;
        font-size: 0.72rem;
        font-weight: 700;
        display: none;
        align-items: center;
        gap: 5px;
    }
    .save-floating-hint.show { display: inline-flex; animation: pulseHint 1.5s ease-in-out infinite; }
    @keyframes pulseHint { 0%,100% { opacity: 0.8; } 50% { opacity: 1; } }

    @media (max-width: 991.98px) {
        .jenis-check { font-size: 0.6rem; min-height: 64px; padding: 8px 4px; }
        .role-cell { padding-left: 0.85rem; }
        .role-icon-wrap { width: 38px; height: 38px; font-size: 1.05rem; }
    }
</style>
@endpush

@section('content')

<div class="info-banner">
    <i class="bi bi-shield-lock-fill"></i>
    <div>
        <b>Sharing Access Per Role</b> — atur jenis pengajuan apa saja yang boleh diakses oleh setiap role.
        Cukup centang &amp; klik SIMPAN sekali. Semua user dengan role tsb otomatis ikut perubahannya.
        Untuk akses pengecualian per arsip (mis. accounting butuh tahu 1 Cancel tertentu) gunakan tombol
        <i class="bi bi-share-fill text-info"></i> Bagikan di list pengajuan.
        <span class="d-block mt-1 text-muted" style="font-size:0.75rem;">Superadmin tidak tampil di sini karena selalu punya akses penuh.</span>
    </div>
</div>

@if(session('success'))
    <div class="alert border-0 rounded-3 d-flex align-items-start gap-2 small mb-3" style="background:#dcfce7;color:#15803d;">
        <i class="bi bi-check-circle-fill mt-1"></i><div>{{ session('success') }}</div>
    </div>
@endif

<form method="POST" action="{{ route('superadmin.pengajuan-access.update-bulk') }}" id="matrixForm">
    @csrf @method('PUT')

    <div class="matrix-card">
        <div class="matrix-card-head">
            <div>
                <p class="h-title">Matrix Role × Jenis Pengajuan</p>
                <p class="h-sub">{{ count($roleList) }} role · {{ count($jenisList) }} jenis pengajuan</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="save-floating-hint" id="hintUnsaved">
                    <i class="bi bi-exclamation-circle-fill"></i> Ada perubahan belum disimpan
                </span>
                <button type="submit" class="btn-save-all">
                    <i class="bi bi-floppy-fill"></i> SIMPAN SEMUA PERUBAHAN
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="left" style="min-width:260px;">Role</th>
                        @foreach($jenisList as $k => $info)
                            <th width="110">
                                <div class="d-flex flex-column align-items-center" style="gap:3px;">
                                    <i class="{{ $info['icon'] }}" style="color:{{ $info['color'] }};font-size:1.05rem;"></i>
                                    <span style="font-size:0.6rem;">{{ $info['label'] }}</span>
                                </div>
                            </th>
                        @endforeach
                        <th width="120">Status</th>
                        <th width="130" style="padding-right:1.75rem;">Quick Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($roleList as $roleKey => $roleInfo)
                    @php
                        $grants = $accessMap[$roleKey] ?? [];
                        $count = count($grants);
                        $total = count($jenisList);
                        $statusClass = $count === 0 ? 'zero' : ($count >= $total ? 'full' : 'partial');
                        $userCount = (int) ($userCounts[$roleKey] ?? 0);
                    @endphp
                    <tr data-role="{{ $roleKey }}">
                        <td>
                            <div class="role-cell">
                                <div class="role-icon-wrap" style="background:{{ $roleInfo['color'] }}1a;color:{{ $roleInfo['color'] }};">
                                    <i class="{{ $roleInfo['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="role-name">{{ $roleInfo['label'] }}</div>
                                    <div class="role-meta">
                                        <span class="pill">{{ strtoupper($roleKey) }}</span>
                                        · {{ $userCount }} user aktif
                                    </div>
                                </div>
                            </div>
                        </td>

                        @foreach($jenisList as $jenisKey => $jenisInfo)
                            @php $checked = in_array($jenisKey, $grants, true); @endphp
                            <td class="text-center">
                                <label class="jenis-check {{ $checked ? 'checked' : '' }}">
                                    <input type="checkbox"
                                           name="matrix[{{ $roleKey }}][]"
                                           value="{{ $jenisKey }}"
                                           {{ $checked ? 'checked' : '' }}>
                                    <span class="check-tick"></span>
                                    <i class="{{ $jenisInfo['icon'] }} ji" style="color:{{ $jenisInfo['color'] }};"></i>
                                </label>
                            </td>
                        @endforeach

                        <td class="text-center">
                            <span class="access-count {{ $statusClass }}">
                                <i class="bi bi-{{ $count === 0 ? 'lock-fill' : ($count >= $total ? 'unlock-fill' : 'sliders') }}"></i>
                                <span class="access-count-val">{{ $count }}</span>/{{ $total }}
                            </span>
                        </td>

                        <td>
                            <div class="row-actions">
                                <button type="button" class="btn-mini btn-toggle-all" data-role="{{ $roleKey }}" data-action="grant">
                                    <i class="bi bi-check-all"></i> Centang Semua
                                </button>
                                <button type="button" class="btn-mini danger btn-toggle-all" data-role="{{ $roleKey }}" data-action="revoke">
                                    <i class="bi bi-x-circle"></i> Hapus Semua
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
(function () {
    const $form = document.getElementById('matrixForm');
    const $hint = document.getElementById('hintUnsaved');

    function refreshRow(tr) {
        const checked = tr.querySelectorAll('input[type="checkbox"]:checked').length;
        const total = tr.querySelectorAll('input[type="checkbox"]').length;
        const $count = tr.querySelector('.access-count');
        const $val = tr.querySelector('.access-count-val');
        $val.textContent = checked;
        $count.classList.remove('zero','partial','full');
        const $icon = $count.querySelector('i');
        if (checked === 0) { $count.classList.add('zero'); $icon.className = 'bi bi-lock-fill'; }
        else if (checked >= total) { $count.classList.add('full'); $icon.className = 'bi bi-unlock-fill'; }
        else { $count.classList.add('partial'); $icon.className = 'bi bi-sliders'; }
    }

    function markDirty() { $hint.classList.add('show'); }

    document.querySelectorAll('.jenis-check').forEach(label => {
        const input = label.querySelector('input[type="checkbox"]');
        input.addEventListener('change', () => {
            label.classList.toggle('checked', input.checked);
            refreshRow(label.closest('tr'));
            markDirty();
        });
    });

    // Quick action: centang/hapus semua untuk 1 role
    document.querySelectorAll('.btn-toggle-all').forEach(btn => {
        btn.addEventListener('click', () => {
            const role = btn.dataset.role;
            const action = btn.dataset.action; // grant | revoke
            const tr = document.querySelector(`tr[data-role="${role}"]`);
            tr.querySelectorAll('.jenis-check').forEach(lab => {
                const inp = lab.querySelector('input[type="checkbox"]');
                inp.checked = (action === 'grant');
                lab.classList.toggle('checked', inp.checked);
            });
            refreshRow(tr);
            markDirty();
        });
    });

    // Warn kalau navigate away dgn perubahan belum di-save
    let formSubmitted = false;
    $form.addEventListener('submit', () => { formSubmitted = true; });
    window.addEventListener('beforeunload', (e) => {
        if (!formSubmitted && $hint.classList.contains('show')) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
})();
</script>
@endpush
