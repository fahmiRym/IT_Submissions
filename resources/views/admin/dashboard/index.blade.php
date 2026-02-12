@extends('layouts.app')

@section('title','Dashboard Admin')
@section('page-title','📊 Dashboard Pengajuan')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
    
    body { font-family: 'Outfit', sans-serif; background-color: #f4f7fa; color: #0f172a; }
    
    /* Global Typography & Clarity - High Contrast */
    .text-secondary { color: #334155 !important; font-weight: 600; }
    .text-muted { color: #64748b !important; }
    .fw-extrabold { font-weight: 800; }
    
    /* Premium Stat Cards */
    .stat-card-premium {
        border-radius: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        overflow: hidden;
    }
    .stat-card-premium:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px -10px rgba(0,0,0,0.15);
    }
    .stat-icon-circle {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        backdrop-filter: blur(4px);
    }

    /* Table Enhancements */
    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #475569;
        font-weight: 800;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        padding: 1.25rem 1rem;
    }
    
    .hierarchy-connector {
        color: #6366f1;
        opacity: 0.8;
        font-size: 1rem;
        margin-right: 8px;
    }

    .item-detail-card-mini {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.03);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 5px;
    }
    
    .qty-bubble-mini {
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 0.7rem;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .qty-bubble-mini.in { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .qty-bubble-mini.out { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stat-card-premium { margin-bottom: 1rem; }
        .display-stats-large { font-size: 2rem !important; }
    }
</style>
@endpush

@section('content')

{{-- STATS OVERVIEW --}}
<div class="row g-4 mb-5 animate-on-scroll">
    @php
        $dashStats = [
            ['label' => 'TOTAL PENGAJUAN', 'val' => $total, 'icon' => 'bi-collection-fill', 'bg' => 'linear-gradient(135deg, #1e293b 0%, #334155 100%)', 'color' => '#ffffff'],
            ['label' => 'NEED REVIEW', 'val' => $Review, 'icon' => 'bi-clock-history', 'bg' => 'linear-gradient(135deg, #fefce8 0%, #fef9c3 100%)', 'color' => '#854d0e', 'icon_bg' => '#fde047'],
            ['label' => 'IN PROCESS', 'val' => $process, 'icon' => 'bi-gear-fill', 'bg' => 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)', 'color' => '#075985', 'icon_bg' => '#7dd3fc'],
            ['label' => 'COMPLETED', 'val' => $done, 'icon' => 'bi-check-circle-fill', 'bg' => 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)', 'color' => '#166534', 'icon_bg' => '#86efac'],
        ];
    @endphp

@foreach($dashStats as $ds)
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stat-card-premium shadow-sm h-100" style="background: {{ $ds['bg'] }}; border: 1px solid {{ $ds['icon_bg'] ?? 'transparent' }};">
            <div class="card-body p-4 d-flex align-items-center gap-4">
                <div class="stat-icon-circle shadow-lg" style="background: {{ $ds['icon_bg'] ?? 'rgba(255,255,255,0.2)' }}; color: {{ $ds['color'] }};">
                    <i class="bi {{ $ds['icon'] }} fs-2"></i>
                </div>
                <div>
                    <h1 class="fw-extrabold mb-0 display-stats-large" style="color: {{ $ds['color'] }}; letter-spacing: -2px; font-size: 2.75rem;">{{ number_format($ds['val']) }}</h1>
                    <p class="mb-0 fw-extrabold opacity-75 text-uppercase mt-2" style="font-size: 0.7rem; color: {{ $ds['color'] }}; letter-spacing: 1px;">{{ $ds['label'] }}</p>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- RIWAYAT TABLE --}}
<div class="card border-0 shadow-sm animate-on-scroll" style="border-radius: 16px; overflow: hidden;">
    <div class="card-header bg-white border-bottom border-light py-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-extrabold text-dark mb-1"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pengajuan</h5>
            <p class="text-muted small mb-0">Daftar aktivitas pengajuan terbaru Anda</p>
        </div>
        <a href="{{ route('admin.arsip.index') }}" class="btn btn-light rounded-pill px-4 fw-bold text-primary shadow-sm">
            Lihat Semua <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>

    <div class="card-body p-0 mt-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Tgl Pengajuan</th>
                        <th>No Reg / Transaksi</th>
                        <th>Jenis Pengajuan</th>
                        <th>Status</th>
                        <th class="text-center">Items & Qty</th>
                        <th class="text-end pe-4">Detail</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($arsips as $a)
                    <tr class="transition-hover">
                        <td class="ps-4">
                            <div class="fw-extrabold text-dark" style="font-size: 0.9rem;">{{ optional($a->tgl_pengajuan)->format('d M Y') }}</div>
                            <div class="small text-muted fw-bold">
                                <i class="bi bi-clock me-1 text-primary opacity-75"></i> {{ optional($a->created_at)->format('H:i') }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                @if($a->no_registrasi)
                                    <div class="px-2 py-0 rounded border border-info border-opacity-50 text-info font-monospace fw-bold shadow-sm" style="font-size: 0.72rem; width: fit-content; background: #f0f9ff;">
                                        {{ $a->no_registrasi }}
                                    </div>
                                @endif
                                
                                @if($a->no_transaksi)
                                    <div class="d-flex align-items-center mt-1">
                                        <i class="bi bi-file-earmark-text hierarchy-connector"></i>
                                        <span class="text-primary fw-extrabold font-monospace" style="font-size: 0.75rem;">{{ $a->no_transaksi }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                             @php
                                $jc = match($a->jenis_pengajuan) {
                                    'Adjust' => ['bg' => '#f0f9ff', 'text' => '#0ea5e9'],
                                    'Cancel' => ['bg' => '#fef2f2', 'text' => '#ef4444'],
                                    'Bundel' => ['bg' => '#f0fdf4', 'text' => '#10b981'],
                                    default  => ['bg' => '#f5f3ff', 'text' => '#6366f1'],
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-pill fw-bold" style="font-size: 0.68rem; background: {{ $jc['bg'] }}; color: {{ $jc['text'] }}; border: 1px solid {{ $jc['text'] }}20;">
                                {{ strtoupper(str_replace('_', ' ', $a->jenis_pengajuan)) }}
                            </span>
                        </td>
                        
                        <td>
                             @php
                                $kpC = match($a->ket_process) {
                                    'Review'  => ['bg' => '#fefce8', 'text' => '#854d0e', 'border' => '#fde047', 'dot' => '#facc15'],
                                    'Process' => ['bg' => '#f0f9ff', 'text' => '#075985', 'border' => '#7dd3fc', 'dot' => '#38bdf8'], 
                                    'Done'    => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#86efac', 'dot' => '#22c55e'],
                                    default   => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1', 'dot' => '#64748b'],
                                };
                            @endphp
                             <div class="px-3 py-1 rounded-pill fw-bold d-flex align-items-center gap-2" 
                                  style="font-size: 0.68rem; background: {{ $kpC['bg'] }}; color: {{ $kpC['text'] }}; border: 1.5px solid {{ $kpC['border'] }}; width: fit-content;">
                                <div class="rounded-circle" style="width: 6px; height: 6px; background-color: {{ $kpC['dot'] }};"></div>
                                {{ strtoupper($a->ket_process) }}
                             </div>
                        </td>
                        
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <div class="d-flex gap-2">
                                    <span class="badge rounded-pill bg-success fw-extrabold px-2" style="font-size: 0.75rem;">+{{ (int)$a->total_qty_in }}</span>
                                    <span class="badge rounded-pill bg-danger fw-extrabold px-2" style="font-size: 0.75rem;">-{{ (int)$a->total_qty_out }}</span>
                                </div>
                                <small class="text-muted fw-bold mt-1" style="font-size: 0.6rem;">TOTAL QTY</small>
                            </div>
                        </td>

                        <td class="text-end pe-4">
                            <div class="d-flex flex-column gap-1 align-items-end" style="min-width: 250px;">
                                @php $itemsFound = false; @endphp
                                
                                {{-- Simple Detail View for Dashboard --}}
                                @if($a->adjustItems->count() > 0)
                                    @php $itemsFound = true; @endphp
                                    @foreach($a->adjustItems->take(2) as $item)
                                        <div class="item-detail-card-mini w-100">
                                            <div class="lh-1 text-start">
                                                <div class="fw-bold text-dark font-monospace" style="font-size: 0.7rem;">{{ $item->product_code }}</div>
                                            </div>
                                            <div class="qty-bubble-mini {{ $item->qty_out > 0 ? 'out' : 'in' }}">
                                                {{ $item->qty_out > 0 ? '-'.(int)$item->qty_out : '+'.(int)$item->qty_in }}
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                @if(str_contains($a->jenis_pengajuan, 'Mutasi') && $a->mutasiItems->count() > 0)
                                    @php $itemsFound = true; @endphp
                                    @foreach($a->mutasiItems->take(2) as $item)
                                        <div class="item-detail-card-mini w-100">
                                            <div class="lh-1 text-start">
                                                <div class="fw-bold text-dark font-monospace" style="font-size: 0.7rem;">{{ $item->product_code }}</div>
                                            </div>
                                            <div class="qty-bubble-mini {{ $item->type == 'asal' ? 'out' : 'in' }}">
                                                {{ $item->type == 'asal' ? '-'.(int)$item->qty : '+'.(int)$item->qty }}
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                @if(!$itemsFound && $a->keterangan)
                                    <div class="text-muted small fst-italic text-truncate" style="max-width: 200px;">"{{ $a->keterangan }}"</div>
                                @endif

                                @if($a->adjustItems->count() > 2 || $a->mutasiItems->count() > 2)
                                    <small class="text-primary fw-bold mt-1" style="font-size: 0.65rem;">+{{ max($a->adjustItems->count(), $a->mutasiItems->count()) - 2 }} lainnya</small>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center opacity-50">
                                <i class="bi bi-inbox fs-1 mb-2"></i>
                                <p class="fw-bold">Belum ada riwayat pengajuan</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 bg-light bg-opacity-50">
             {{ $arsips->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection

