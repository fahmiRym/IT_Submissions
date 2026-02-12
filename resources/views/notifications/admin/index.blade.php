@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-title', '🔔 Notifikasi Saya')

@push('styles')
<style>
    .notif-card {
        border-left: 4px solid transparent;
        transition: all 0.2s;
    }
    .notif-card:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
    }
    .notif-unread {
        background-color: #f0f9ff;
    }
    .notif-read {
        background-color: #fff;
    }
    
    /* Status Colors for Border */
    .border-info { border-left-color: #0dcaf0 !important; }
    .border-success { border-left-color: #198754 !important; }
    .border-danger { border-left-color: #dc3545 !important; }
    .border-warning { border-left-color: #ffc107 !important; }

    .icon-box {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
</style>
@endpush

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-9">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">🔔 Notifikasi Saya</h5>
                <p class="text-muted small mb-0">Update status pengajuan arsip Anda.</p>
            </div>
            @if($paginatedNotifications->count() > 0)
            <span class="badge bg-light text-primary border rounded-pill px-3 py-2">
                Total: {{ $paginatedNotifications->total() }}
            </span>
            @endif
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="list-group list-group-flush">
                @forelse($paginatedNotifications as $n)
                    @php
                        // Determine type based on message content or title
                        $type = 'info';
                        $icon = 'bi-info-circle-fill';
                        $color = 'text-info';
                        $bgIcon = 'bg-info bg-opacity-10';
                        $borderClass = 'border-info';

                        if (Str::contains(strtolower($n->title), ['revisi', 'perbaikan'])) {
                            $type = 'warning'; $icon = 'bi-exclamation-triangle-fill'; $color = 'text-warning'; $bgIcon = 'bg-warning bg-opacity-10'; $borderClass = 'border-warning';
                        } elseif (Str::contains(strtolower($n->title), ['tolak', 'ditolak'])) {
                            $type = 'danger'; $icon = 'bi-x-circle-fill'; $color = 'text-danger'; $bgIcon = 'bg-danger bg-opacity-10'; $borderClass = 'border-danger';
                        } elseif (Str::contains(strtolower($n->title), ['setuju', 'disetujui', 'selesai'])) {
                            $type = 'success'; $icon = 'bi-check-circle-fill'; $color = 'text-success'; $bgIcon = 'bg-success bg-opacity-10'; $borderClass = 'border-success';
                        }
                    @endphp

                    <div class="list-group-item p-4 notif-card {{ $n->is_read ? 'notif-read' : 'notif-unread' }} {{ $borderClass }}">
                        <div class="d-flex gap-3">
                            
                            {{-- ICON --}}
                            <div class="flex-shrink-0">
                                <div class="icon-box {{ $bgIcon }} {{ $color }}">
                                    <i class="bi {{ $icon }}"></i>
                                </div>
                            </div>

                            {{-- CONTENT --}}
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold mb-0 text-dark">{{ $n->title }}</h6>
                                    <small class="text-muted text-nowrap ms-2">
                                        <i class="bi bi-clock me-1"></i>{{ $n->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                
                                <p class="text-secondary mb-2 small">{{ $n->message }}</p>

                                {{-- ARSIP DETAIL IF AVAILABLE --}}
                                @if($n->arsip)
                                    <div class="bg-light rounded p-2 border d-inline-block mt-1 mb-2">
                                        <small class="d-block fw-bold text-dark">
                                            <i class="bi bi-file-text me-1"></i> Detail Berkas:
                                        </small>
                                        <div class="d-flex flex-wrap gap-2 text-xs mt-1">
                                            @if($n->arsip->no_doc)
                                                <span class="badge bg-white text-dark border font-monospace">Doc: {{ $n->arsip->no_doc }}</span>
                                            @endif
                                            @if($n->arsip->no_registrasi)
                                                <span class="badge bg-white text-dark border font-monospace">Reg: {{ $n->arsip->no_registrasi }}</span>
                                            @endif
                                            
                                            {{-- Status Badge dynamic color based on ket_process --}}
                                            @php
                                                $statusColor = match($n->arsip->ket_process) {
                                                    'Done' => 'success',
                                                    'Process' => 'info',
                                                    'Review' => 'warning',
                                                    'Partial Done' => 'primary',
                                                    'Void' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} border border-{{ $statusColor }}">
                                                {{ ucfirst($n->arsip->ket_process) }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                {{-- ACTIONS --}}
                                <div class="d-flex gap-2 mt-2">
                                    @if(!$n->is_read)
                                        <form method="POST" action="{{ route('admin.notifications.read', $n->id) }}">
                                            @csrf @method('PUT')
                                            <button class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3">
                                                <i class="bi bi-check2-all me-1"></i> Tandai Dibaca
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small fst-italic"><i class="bi bi-check2-all"></i> Dibaca</span>
                                    @endif

                                    @if($n->arsip_id)
                                        <a href="{{ route('admin.arsip.index', ['search' => $n->arsip->no_registrasi ?? $n->arsip->no_doc]) }}" 
                                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            Lihat Arsip <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="bi bi-bell-slash fs-1 text-muted opacity-25"></i>
                        </div>
                        <h6 class="fw-bold text-muted">Belum ada notifikasi</h6>
                        <p class="text-muted small">Anda akan menerima pemberitahuan di sini saat ada update.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $paginatedNotifications->links('pagination::bootstrap-5') }}
        </div>

    </div>
</div>

@endsection
