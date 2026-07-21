{{-- Inovasi block dashboard: Adjustment Sync Status + Approval Velocity + Recent Activity.
     Param: $role, $arsipQuery (Eloquent), $pendingApprovalCount --}}
@php
    $role = $role ?? (auth()->user()->role ?? 'admin');
    $pendingApprovalCount = $pendingApprovalCount ?? 0;

    $now = now();
    try {
        $base = ($arsipQuery ?? \App\Models\Arsip::query())->clone();

        // Adjustment specific
        $adjustTotal = (clone $base)->where('jenis_pengajuan', 'Adjust')->count();
        $adjustPendingAccounting = (clone $base)->where('jenis_pengajuan', 'Adjust')
            ->whereHas('approvals', function ($q) {
                $q->where('role_label', 'Accounting')->where('status', 'pending');
            })->count();
        $adjustFinalApproved = (clone $base)->where('jenis_pengajuan', 'Adjust')
            ->whereDoesntHave('approvals', fn ($q) => $q->where('status', 'pending'))
            ->where('status', 'Done')->count();

        // Approval velocity: avg hours from created to fully_approved (Done) — last 30 days
        $velRows = (clone $base)
            ->where('status', 'Done')
            ->whereNotNull('tgl_arsip')
            ->whereDate('tgl_arsip', '>=', $now->copy()->subDays(30))
            ->get(['created_at', 'tgl_arsip']);
        $avgHours = 0;
        if ($velRows->count()) {
            $sumSec = 0;
            foreach ($velRows as $r) {
                if ($r->created_at && $r->tgl_arsip) {
                    $sumSec += $r->created_at->diffInSeconds($r->tgl_arsip);
                }
            }
            $avgHours = round(($sumSec / $velRows->count()) / 3600, 1);
        }

        // Recent activity
        $recent = (clone $base)
            ->with(['admin:id,name', 'department:id,name'])
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get(['id', 'no_registrasi', 'jenis_pengajuan', 'status', 'ket_process', 'admin_id', 'department_id', 'updated_at']);
    } catch (\Throwable $e) {
        $adjustTotal = 0; $adjustPendingAccounting = 0; $adjustFinalApproved = 0;
        $avgHours = 0;
        $recent = collect();
    }

    $isAdmin = in_array($role, ['admin', 'accounting']);
    $arsipIndexRoute = $isAdmin ? 'admin.arsip.index' : 'superadmin.arsip.index';
@endphp

<div class="row g-3 mb-4">

    {{-- 1. ADJUSTMENT SYNC CARD --}}
    <div class="col-12 col-lg-4">
        <div class="dash-card-adjust h-100 p-3 rounded-4 shadow-sm position-relative overflow-hidden">
            <div class="dash-card-adjust-deco"></div>
            <div class="d-flex align-items-start gap-2 mb-2 position-relative" style="z-index:2;">
                <div class="dash-card-adjust-icon">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-white-50 fw-bold text-uppercase" style="font-size:0.6rem; letter-spacing:0.12em;">
                        Adjustment ↔ Odoo Sync
                    </div>
                    <h4 class="text-white fw-extrabold mb-0 mt-1">{{ number_format($adjustTotal) }}</h4>
                    <small class="text-white-50">total adjustment</small>
                </div>
            </div>
            <div class="d-flex gap-2 position-relative" style="z-index:2;">
                <div class="dash-card-adjust-stat flex-fill">
                    <div class="dash-card-adjust-stat-label">PENDING ACC</div>
                    <div class="dash-card-adjust-stat-val text-warning">{{ number_format($adjustPendingAccounting) }}</div>
                </div>
                <div class="dash-card-adjust-stat flex-fill">
                    <div class="dash-card-adjust-stat-label">SYNCED</div>
                    <div class="dash-card-adjust-stat-val text-success">{{ number_format($adjustFinalApproved) }}</div>
                </div>
            </div>
            <a href="{{ route($arsipIndexRoute, ['jenis' => 'Adjust']) }}"
               class="dash-card-adjust-link mt-2 d-block text-center text-white-50 text-decoration-none small position-relative" style="z-index:2;">
                <i class="bi bi-arrow-right-circle me-1"></i>Lihat semua Adjustment
            </a>
        </div>
    </div>

    {{-- 2. APPROVAL VELOCITY --}}
    <div class="col-12 col-lg-4">
        <div class="dash-card-velocity h-100 p-3 rounded-4 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="dash-card-velocity-icon">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <div>
                    <div class="text-muted fw-bold text-uppercase" style="font-size:0.6rem; letter-spacing:0.12em;">
                        Approval Velocity
                    </div>
                    <small class="text-secondary">rata-rata 30 hari terakhir</small>
                </div>
            </div>
            <div class="text-center my-2">
                <h2 class="fw-extrabold mb-0" style="font-size:2.5rem; letter-spacing:-1px; color:#0f172a;">
                    {{ $avgHours > 0 ? number_format($avgHours, 1) : '—' }}
                    <small class="text-muted" style="font-size:1rem; font-weight:600;">jam</small>
                </h2>
                <small class="text-muted">Pemohon → Done</small>
            </div>
            <div class="dash-card-velocity-bar">
                @php
                    // gauge: < 24jam=fast (green), 24-72=normal (yellow), >72=slow (red)
                    $gaugeColor = $avgHours == 0 ? '#94a3b8'
                        : ($avgHours < 24 ? '#10b981' : ($avgHours <= 72 ? '#f59e0b' : '#ef4444'));
                    $gaugeLabel = $avgHours == 0 ? 'Belum ada data'
                        : ($avgHours < 24 ? '⚡ Cepat (< 1 hari)'
                            : ($avgHours <= 72 ? '🕐 Normal (1-3 hari)' : '⚠️ Lambat (> 3 hari)'));
                    $gaugePct = $avgHours == 0 ? 0 : min(100, ($avgHours / 168) * 100); // benchmark 1 minggu
                @endphp
                <div class="progress" style="height:6px; border-radius:6px;">
                    <div class="progress-bar"
                         style="width:{{ $gaugePct }}%; background:{{ $gaugeColor }};"></div>
                </div>
                <div class="text-center mt-2 small fw-bold" style="color:{{ $gaugeColor }}">
                    {{ $gaugeLabel }}
                </div>
            </div>
        </div>
    </div>

    {{-- 3. APPROVAL INBOX --}}
    <div class="col-12 col-lg-4">
        <div class="dash-card-inbox h-100 p-3 rounded-4 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="dash-card-inbox-icon">
                    <i class="bi bi-inbox-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted fw-bold text-uppercase" style="font-size:0.6rem; letter-spacing:0.12em;">
                        Approval Inbox
                    </div>
                    <small class="text-secondary">menunggu persetujuan Anda</small>
                </div>
                @if($pendingApprovalCount > 0)
                    <span class="badge bg-danger" style="font-size:0.75rem;">{{ $pendingApprovalCount }}</span>
                @endif
            </div>
            @if($pendingApprovalCount > 0)
                <h2 class="fw-extrabold text-danger mb-1" style="font-size:2.5rem; letter-spacing:-1px;">{{ $pendingApprovalCount }}</h2>
                <small class="text-muted">pengajuan butuh tindakan</small>
                @php
                    if ($isAdmin && Route::has('admin.approvals.index')) {
                        $inboxUrl = route('admin.approvals.index');
                    } elseif (!$isAdmin && Route::has('superadmin.approvals.index')) {
                        $inboxUrl = route('superadmin.approvals.index');
                    } else {
                        $inboxUrl = route($arsipIndexRoute);
                    }
                @endphp
                <a href="{{ $inboxUrl }}"
                   class="btn btn-danger w-100 mt-3 fw-bold rounded-pill"
                   style="background: linear-gradient(135deg, #ef4444, #dc2626); border:none;">
                    <i class="bi bi-check2-square me-1"></i>Buka Inbox
                </a>
            @else
                <div class="text-center py-3">
                    <div class="display-3 mb-0" style="line-height:1;">🎉</div>
                    <h5 class="fw-bold text-success mt-2 mb-1">Inbox Kosong</h5>
                    <small class="text-muted">Tidak ada pengajuan menunggu Anda</small>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- RECENT ACTIVITY FEED --}}
@if($recent->count())
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 d-flex align-items-center gap-2 px-4 pt-3 pb-1">
        <i class="bi bi-activity text-primary"></i>
        <h6 class="fw-bold mb-0">Aktivitas Terbaru</h6>
        <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:0.6rem;">LIVE</span>
        <a href="{{ route($arsipIndexRoute) }}" class="ms-auto small fw-bold text-primary text-decoration-none">
            Lihat semua <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body pt-2 pb-3 px-4">
        <div class="dash-activity-feed">
            @foreach($recent as $r)
                @php
                    $jenisColor = match($r->jenis_pengajuan) {
                        'Adjust'        => ['bg' => '#0ea5e9', 'icon' => 'bi-sliders'],
                        'Cancel'        => ['bg' => '#ef4444', 'icon' => 'bi-x-circle'],
                        'Mutasi_Billet' => ['bg' => '#6366f1', 'icon' => 'bi-arrow-repeat'],
                        'Mutasi_Produk' => ['bg' => '#10b981', 'icon' => 'bi-box-seam'],
                        'Internal_Memo' => ['bg' => '#f59e0b', 'icon' => 'bi-file-text'],
                        'Bundel'        => ['bg' => '#ec4899', 'icon' => 'bi-collection'],
                        'Produk_Baru'   => ['bg' => '#8b5cf6', 'icon' => 'bi-plus-square'],
                        default         => ['bg' => '#64748b', 'icon' => 'bi-file-earmark'],
                    };
                    $statColor = match($r->ket_process) {
                        'Done' => 'success', 'Process' => 'info', 'Review' => 'warning',
                        'Pending' => 'secondary', 'Void' => 'danger',
                        default => 'secondary',
                    };
                @endphp
                <div class="dash-activity-item">
                    <div class="dash-activity-icon" style="background:{{ $jenisColor['bg'] }}1a; color:{{ $jenisColor['bg'] }};">
                        <i class="bi {{ $jenisColor['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold text-dark" style="font-size:0.82rem;">{{ str_replace('_', ' ', $r->jenis_pengajuan) }}</span>
                            <span class="badge bg-{{ $statColor }}-subtle text-{{ $statColor }}" style="font-size:0.6rem;">{{ $r->ket_process }}</span>
                            <span class="font-monospace text-primary fw-bold" style="font-size:0.7rem;">{{ $r->no_registrasi ?: 'no-reg' }}</span>
                        </div>
                        <div class="text-muted text-truncate" style="font-size:0.7rem;">
                            {{ $r->admin->name ?? '-' }} · {{ $r->department->name ?? '-' }}
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="text-muted" style="font-size:0.65rem;">{{ $r->updated_at?->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@once
@push('styles')
<style>
    /* ----- Adjustment Sync card ----- */
    .dash-card-adjust {
        background: linear-gradient(135deg, #0891b2 0%, #155e75 100%);
        color: #fff;
    }
    .dash-card-adjust-deco {
        position: absolute; top: -50%; right: -10%;
        width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        pointer-events: none;
    }
    .dash-card-adjust-icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1.2rem;
    }
    .dash-card-adjust-stat {
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 10px;
        padding: 0.5rem;
        text-align: center;
    }
    .dash-card-adjust-stat-label {
        font-size: 0.55rem; font-weight: 800; letter-spacing: 0.1em;
        color: rgba(255,255,255,0.7); margin-bottom: 0.15rem;
    }
    .dash-card-adjust-stat-val {
        font-size: 1.3rem; font-weight: 800; letter-spacing: -0.5px; line-height:1;
    }
    .dash-card-adjust-stat-val.text-warning { color: #fde047 !important; }
    .dash-card-adjust-stat-val.text-success { color: #86efac !important; }
    .dash-card-adjust-link {
        border-top: 1px solid rgba(255,255,255,0.18);
        padding-top: 0.6rem;
    }
    .dash-card-adjust-link:hover { color: #fff !important; }

    /* ----- Velocity card ----- */
    .dash-card-velocity { background: #fff; }
    .dash-card-velocity-icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ddd6fe, #c4b5fd);
        display: flex; align-items: center; justify-content: center;
        color: #6d28d9; font-size: 1.2rem;
    }

    /* ----- Inbox card ----- */
    .dash-card-inbox { background: #fff; }
    .dash-card-inbox-icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        display: flex; align-items: center; justify-content: center;
        color: #b91c1c; font-size: 1.2rem;
    }

    /* ----- Activity feed ----- */
    .dash-activity-feed { display: flex; flex-direction: column; gap: 6px; }
    .dash-activity-item {
        display: flex; align-items: center; gap: 12px;
        padding: 0.65rem 0.5rem;
        border-radius: 12px;
        transition: all 0.18s ease;
    }
    .dash-activity-item:hover {
        background: #f8fafc;
        transform: translateX(3px);
    }
    .dash-activity-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    @media (max-width: 575.98px) {
        .dash-card-adjust h4, .dash-card-velocity h2, .dash-card-inbox h2 { font-size: 1.5rem !important; }
        .dash-card-adjust-stat-val { font-size: 1rem; }
        .dash-activity-icon { width: 28px; height: 28px; font-size: 0.8rem; }
    }
</style>
@endpush
@endonce
