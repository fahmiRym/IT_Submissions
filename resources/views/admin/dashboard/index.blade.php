@extends('layouts.app')

@section('title','Dashboard Admin')
@section('page-title','Dashboard Pengajuan')

@section('content')



{{-- STATS OVERVIEW --}}
<div class="row g-4 mb-4 animate-on-scroll">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-4 h-100">
            <div class="card-body">
                <h6 class="text-muted fw-bold mb-3">Total Pengajuan</h6>
                <h1 class="display-4 fw-bold text-dark mb-0">{{ $total }}</h1>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-4 h-100">
            <div class="card-body">
                <h6 class="text-muted fw-bold mb-3">Review</h6>
                <h1 class="display-4 fw-bold text-info mb-0">{{ $Review }}</h1>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-4 h-100">
            <div class="card-body">
                <h6 class="text-muted fw-bold mb-3">Process</h6>
                <h1 class="display-4 fw-bold text-warning mb-0">{{ $process }}</h1>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-4 h-100">
            <div class="card-body">
                <h6 class="text-muted fw-bold mb-3">Done</h6>
                <h1 class="display-4 fw-bold text-success mb-0">{{ $done }}</h1>
            </div>
        </div>
    </div>
</div>

{{-- RIWAYAT TABLE --}}
<div class="card border-0 shadow-sm animate-on-scroll" style="animation-delay: 0.1s;">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <h6 class="fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pengajuan Saya</h6>
    </div>

    <div class="card-body p-0 mt-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase text-xs fw-bold">
                    <tr>
                        <th class="ps-4">Tgl Pengajuan</th>
                        <th>No Registrasi</th>
                        <th>Jenis Pengajuan</th>
                        <th>Dept & Unit</th>
                        <th>Status</th>
                        <th class="text-center">Qty In</th>
                        <th class="text-center">Qty Out</th>
                        <th class="text-end pe-4">Keterangan / Detail</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($arsips as $a)
                    <tr class="transition-hover border-bottom">
                        <td class="ps-4 text-nowrap text-secondary text-sm">
                            <div class="fw-bold text-dark">{{ optional($a->tgl_pengajuan)->format('d M Y') }}</div>
                            <div class="small text-muted"><i class="bi bi-clock me-1"></i> {{ optional($a->created_at)->format('H:i') }} WIB</div>
                        </td>
                        <td>
                            @if($a->no_registrasi)
                                <span class="badge bg-light text-primary border border-primary border-opacity-25 shadow-sm font-monospace text-xs">
                                    {{ $a->no_registrasi }}
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border">-</span>
                            @endif
                        </td>
                        <td>
                             @php
                                $jc = 'secondary';
                                if($a->jenis_pengajuan == 'Adjust') $jc = 'info';
                                if(str_contains($a->jenis_pengajuan, 'Mutasi')) $jc = 'primary';
                                if($a->jenis_pengajuan == 'Cancel') $jc = 'danger';
                            @endphp
                            <span class="badge bg-{{ $jc }} bg-opacity-10 text-{{ $jc }} text-dark border-0">
                                {{ str_replace('_', ' ', $a->jenis_pengajuan) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark text-sm">{{ $a->department->name ?? '-' }}</span>
                                <small class="text-muted">{{ $a->unit->name ?? '-' }}</small>
                            </div>
                        </td>
                        
                        <td>
                             @php
                                $colors = [
                                    'Review' => 'info', 'Process' => 'warning', 'Done' => 'success',
                                    'Partial Done' => 'primary', 'Pending' => 'secondary', 'Void' => 'danger'
                                ];
                                $color = $colors[$a->ket_process] ?? 'secondary';
                            @endphp
                             <span class="badge rounded-pill bg-{{ $color }} text-{{ $color }} bg-opacity-10 border border-{{ $color }} border-opacity-25 px-3">
                                {{ $a->ket_process }}
                             </span>
                        </td>
                        
                         {{-- QTY LOGIC --}}
                         @php
                            $showQty = in_array($a->jenis_pengajuan, ['Adjust', 'Mutasi_Billet', 'Mutasi_Produk', 'Bundel']);
                         @endphp
                         
                         @if($showQty)
                            <td class="text-center fw-bold text-success">{{ (int)$a->total_qty_in }}</td>
                            <td class="text-center fw-bold text-danger">{{ (int)$a->total_qty_out }}</td>
                         @else
                            <td class="text-center text-muted">-</td>
                            <td class="text-center text-muted">-</td>
                         @endif

                        <td class="text-end pe-4 text-wrap" style="max-width: 300px;">
                            {{-- 1. Keterangan Utama --}}
                            @if($a->jenis_pengajuan == 'Cancel')
                                @if($a->kategori)
                                    <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 mb-1">
                                        {{ $a->kategori }}
                                    </div>
                                @endif
                                <div class="text-muted small fst-italic">{{ $a->keterangan }}</div>
                            @else
                                @if($a->keterangan)
                                    <div class="text-muted small fst-italic border-bottom pb-1 mb-1">{{ $a->keterangan }}</div>
                                @endif

                                {{-- No Dokumen (Hasil Arsip) --}}
                                @if(!empty($a->no_doc_rows))
                                    @foreach($a->no_doc_rows as $group)
                                        @foreach($group as $line)
                                            <div class="mb-1">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.65rem;">
                                                    <i class="bi bi-file-earmark-check"></i> {{ $line }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @endforeach
                                @endif

                                {{-- 2. Detail Items (Adjust) --}}
                                @if($a->jenis_pengajuan == 'Adjust' && $a->adjustItems->count() > 0)
                                    <div class="d-flex flex-column gap-1 text-start">
                                        @foreach($a->adjustItems as $item)
                                            <div class="d-flex justify-content-between align-items-center bg-light rounded px-2 py-1 border border-light">
                                                <div class="d-flex flex-column" style="font-size: 0.75rem; line-height: 1.2;">
                                                    <span class="fw-bold text-dark">{{ $item->product_code }}</span>
                                                    <span class="text-muted text-truncate" style="max-width: 150px;">{{ $item->product_name }}</span>
                                                </div>
                                                <div class="d-flex gap-1" style="font-size: 0.7rem;">
                                                    @if($item->qty_in > 0) <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-1">+{{ (int)$item->qty_in }}</span> @endif
                                                    @if($item->qty_out > 0) <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-1">-{{ (int)$item->qty_out }}</span> @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                
                                {{-- 3. Detail Items (Mutasi) --}}
                                @elseif(str_contains($a->jenis_pengajuan, 'Mutasi') && $a->mutasiItems->count() > 0)
                                    <div class="d-flex flex-column gap-1 text-start">
                                        @foreach($a->mutasiItems as $item)
                                            <div class="d-flex justify-content-between align-items-center bg-light rounded px-2 py-1 border border-light">
                                                <div class="d-flex flex-column" style="font-size: 0.75rem; line-height: 1.2;">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge {{ $item->type == 'asal' ? 'bg-danger' : 'bg-success' }} p-0 me-1" style="width: 6px; height: 6px; border-radius: 50%;"> </span>
                                                        <span class="fw-bold text-dark">{{ $item->product_code }}</span>
                                                    </div>
                                                    <span class="text-muted text-truncate" style="max-width: 150px;">{{ $item->product_name }}</span>
                                                </div>
                                                <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-1" style="font-size: 0.7rem;">{{ (int)$item->qty }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                {{-- 4. Detail Items (Bundel) --}}
                                @elseif($a->jenis_pengajuan == 'Bundel' && $a->bundelItems->count() > 0)
                                    <div class="d-flex flex-column gap-1 text-start">
                                        @foreach($a->bundelItems as $item)
                                            <div class="d-flex justify-content-between align-items-center bg-light rounded px-2 py-1 border border-light">
                                                <div class="d-flex flex-column" style="font-size: 0.75rem; line-height: 1.2;">
                                                    <span class="fw-bold text-dark">{{ $item->no_doc }}</span>
                                                    @if($item->keterangan) <span class="text-muted text-xs fst-italic">{{ $item->keterangan }}</span> @endif
                                                </div>
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-1" style="font-size: 0.7rem;">{{ (int)$item->qty }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted opacity-50 mb-2"></i>
                            <p class="text-muted fw-medium">Belum ada pengajuan arsip</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-3 border-top border-light">
             {{ $arsips->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
