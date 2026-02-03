@extends('layouts.app')

@section('title', 'Daftar Arsip')
@section('page-title', '📁 Manajemen Arsip')

@push('styles')
<style>
    /* Table specific small overrides */
    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    .badge-soft {
        padding: 0.4em 0.8em;
        font-weight: 600;
        border-radius: 9999px;
    }
    .transition-hover:hover {
        background-color: #f8fafc;
    }
</style>
@endpush

@section('content')

{{-- ================= FILTER SECTION ================= --}}
<div class="card border-0 shadow-sm mb-4 animate-on-scroll" style="border-radius: 12px;">
    <div class="card-body p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">🔍 Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-0" placeholder="User, No Dok, Transaksi...">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">🏢 Departemen</label>
                <select name="department_id" class="form-select bg-light border-0">
                    <option value="">-- Semua Departemen --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">📂 Kategori</label>
                <select name="kategori" class="form-select bg-light border-0">
                    <option value="">Semua</option>
                    <option value="Human" {{ request('kategori')=='Human'?'selected':'' }}>Human Error</option>
                    <option value="System" {{ request('kategori')=='System'?'selected':'' }}>System Error</option>
                    <option value="None" {{ request('kategori')=='None'?'selected':'' }}>None/Adjust</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">📄 Jenis Pengajuan</label>
                <select name="jenis_pengajuan" class="form-select bg-light border-0">
                    <option value="">Semua</option>
                    @foreach(['Cancel','Adjust','Mutasi_Billet','Mutasi_Produk','Internal_Memo','Bundel'] as $jp)
                        <option value="{{ $jp }}" {{ request('jenis_pengajuan')==$jp?'selected':'' }}>{{ str_replace('_', ' ', $jp) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100 fw-bold shadow-sm" style="background: #4f46e5; border-color: #4f46e5;">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================= STATS OVERVIEW ================= --}}
@php
    $fJenis = request('jenis') ?? request('jenis_pengajuan');
    
    // Default Configuration for Card 1 (Total)
    $sConfig = [
        'title' => 'TOTAL PENGAJUAN',
        'icon'  => 'bi-grid-fill',
        'color' => '#1e293b', // Dark Slate
    ];

    if($fJenis == 'Cancel') {
        $sConfig = [ 'title' => 'TOTAL CANCEL', 'icon' => 'bi-x-octagon-fill', 'color' => '#ef4444' ];
    } elseif($fJenis == 'Adjust') {
        $sConfig = [ 'title' => 'TOTAL ADJUSTMENT', 'icon' => 'bi-sliders', 'color' => '#0ea5e9' ];
    } elseif($fJenis == 'Mutasi_Billet') {
        $sConfig = [ 'title' => 'TOTAL MUTASI BILLET', 'icon' => 'bi-arrow-left-right', 'color' => '#6366f1' ];
    } elseif($fJenis == 'Mutasi_Produk') {
        $sConfig = [ 'title' => 'TOTAL MUTASI PRODUK', 'icon' => 'bi-box-seam-fill', 'color' => '#10b981' ];
    } elseif($fJenis == 'Internal_Memo') {
        $sConfig = [ 'title' => 'TOTAL MEMO', 'icon' => 'bi-file-text-fill', 'color' => '#f59e0b' ];
    } elseif($fJenis == 'Bundel') {
        $sConfig = [ 'title' => 'TOTAL BUNDEL', 'icon' => 'bi-collection-fill', 'color' => '#8b5cf6' ];
    }
@endphp

<div class="row g-4 mb-4 animate-on-scroll" style="animation-delay: 0.1s;">
    {{-- CARD 1: TOTAL TYPE (Dynamic) --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
             <div class="position-absolute start-0 top-0 bottom-0 bg-dark" style="width: 4px; background-color: {{ $sConfig['color'] }} !important;"></div>
            <div class="card-body d-flex align-items-center p-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center text-white me-3" 
                     style="width: 48px; height: 48px; background-color: {{ $sConfig['color'] }};">
                    <i class="bi {{ $sConfig['icon'] }} fs-4"></i>
                </div>
                <div>
                     <h6 class="text-secondary small fw-bold mb-0 text-uppercase" style="font-size: 0.7rem;">{{ $sConfig['title'] }}</h6>
                     <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
    
    {{-- CARD 2: REVIEW (Diganti sesuai request) --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center p-3">
                 <div class="rounded-3 d-flex align-items-center justify-content-center text-white me-3" 
                      style="width: 48px; height: 48px; background-color: #64748b;">
                    {{-- Icon Clock untuk Review --}}
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                     <h6 class="text-secondary small fw-bold mb-0 text-uppercase" style="font-size: 0.7rem;">Review</h6>
                     {{-- Menggunakan key 'Review' dari variable stats --}}
                     <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['Review'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 3: PROCESS --}}
    <div class="col-md-3">
         <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center p-3">
                 <div class="rounded-3 d-flex align-items-center justify-content-center text-white me-3" 
                      style="width: 48px; height: 48px; background-color: #f59e0b;">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
                <div>
                     <h6 class="text-secondary small fw-bold mb-0 text-uppercase" style="font-size: 0.7rem;">SEDANG PROSES</h6>
                     <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['process'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 4: DONE --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center p-3">
                 <div class="rounded-3 d-flex align-items-center justify-content-center text-white me-3" 
                      style="width: 48px; height: 48px; background-color: #10b981;">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                </div>
                <div>
                     <h6 class="text-secondary small fw-bold mb-0 text-uppercase" style="font-size: 0.7rem;">SELESAI</h6>
                     <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['done'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= HEADER & ACTIONS ================= --}}
<div class="d-flex justify-content-between align-items-center mb-4 animate-on-scroll" style="animation-delay: 0.2s;">
    <div>
        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-table me-2 text-primary"></i>Data Arsip</h5>
        <small class="text-muted">Kelola dan monitoring seluruh data arsip organisasi.</small>
    </div>
    <button class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahArsip">
        <i class="bi bi-plus-lg me-2"></i>Buat Baru
    </button>
</div>

{{-- ================= DATA TABLE ================= --}}
<div class="card border-0 shadow-sm animate-on-scroll" style="border-radius: 12px; animation-delay: 0.3s; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4" width="50">#</th>
                        <th width="120">Tgl Pengajuan</th>
                        <th width="120">Tgl Arsip</th>
                        <th>No Registrasi</th>
                        <th>Jenis Pengajuan</th>
                        <th>Pengaju</th>
                        <th>Dept & Unit</th>
                        <th>Status Process</th>
                        
                        @php
                            $jenisFilter = request('jenis') ?? request('jenis_pengajuan');
                            $showQty = in_array($jenisFilter, ['Adjust', 'Mutasi_Billet', 'Mutasi_Produk', 'Bundel']);
                        @endphp
                        
                        @if($showQty || !$jenisFilter)
                            <th class="text-center" width="80">Qty In</th>
                            <th class="text-center" width="80">Qty Out</th>
                        @else
                            <th style="min-width: 250px; max-width: 350px;">Keterangan</th>
                        @endif
                        
                        <th style="min-width: 280px;">Detail Dokumen</th>
                        <th width="150" class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arsips as $a)
                    <tr class="transition-hover">
                        <td class="ps-4 text-center fw-bold text-muted">{{ $a->id }}</td>
                        
                        <td>
                            <div class="fw-semibold text-dark">{{ optional($a->tgl_pengajuan)->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ optional($a->tgl_pengajuan)->diffForHumans() }}</small>
                        </td>
                        <td>
                            @if($a->tgl_arsip)
                                <div class="text-success fw-semibold"><i class="bi bi-calendar-check me-1"></i>{{ $a->tgl_arsip->format('d/m/Y') }}</div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
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
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 shadow-sm" style="width:32px; height:32px; font-size: 0.8rem; font-weight:bold;">
                                    {{ substr($a->admin->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark text-sm">{{ $a->admin->name ?? 'Unknown' }}</div>
                                    <div class="small text-muted" style="font-size: 0.75rem;">{{ $a->manager->name ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
    
                        <td>
                            <div class="fw-semibold text-dark text-sm">{{ $a->department->name ?? '-' }}</div>
                            <div class="badge bg-light text-secondary border border-secondary border-opacity-25">{{ $a->unit->name ?? '-' }}</div>
                        </td>
    
                        <td>
                            @php
                                $colors = [
                                    'Review' => 'info', 'Process' => 'warning', 'Done' => 'success',
                                    'Partial Done' => 'primary', 'Pending' => 'secondary', 'Void' => 'danger'
                                ];
                                $color = $colors[$a->ket_process] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }} text-{{ $color }} bg-opacity-10 border border-{{ $color }} border-opacity-25 rounded-pill px-3">
                                {{ $a->ket_process ?? '-' }}
                            </span> 
                            
                            @if($a->kategori && $a->kategori !== 'None')
                                 <div class="mt-1">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" title="{{ $a->keterangan }}">
                                        {{ $a->kategori }}
                                    </span>
                                 </div>
                            @endif
                        </td>
    
                        @if($showQty || !$jenisFilter)
                            <td class="text-center fw-bold text-success">{{ $a->total_qty_in ?? 0 }}</td>
                            <td class="text-center fw-bold text-danger">{{ $a->total_qty_out ?? 0 }}</td>
                        @else
                            <td class="text-wrap" style="max-width: 300px;">
                                @if($a->keterangan)
                                    <span class="text-dark small"><i class="bi bi-card-text me-1 text-muted"></i>{{ $a->keterangan }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        @endif
    
                        <td class="text-wrap" style="max-width: 350px;">
                             {{-- A. Jika Ada Keterangan (Tampilkan disini jika kolom Keterangan hidden) --}}
                             @if($showQty && $a->keterangan)
                                <div class="mb-2 text-muted fst-italic small border-bottom pb-1">
                                    <i class="bi bi-info-circle me-1"></i> {{ $a->keterangan }}
                                </div>
                             @endif

                            {{-- B. Detail Barang (Cek Relasi Tabel Terlebih Dahulu) --}}
                            @if($a->bundelItems && $a->bundelItems->count() > 0)
                                <div class="small">
                                    @foreach($a->bundelItems as $item)
                                        <div class="d-flex justify-content-between border-bottom border-light mb-1 pb-1">
                                            <span class="text-dark fw-semibold"><i class="bi bi-file-earmark me-1"></i> {{ $item->no_doc }}</span>
                                            <span class="badge bg-secondary text-white">{{ (int)$item->qty }}</span>
                                        </div>
                                        @if($item->keterangan) <div class="text-muted text-xs ms-3 italic">Note: {{ $item->keterangan }}</div> @endif
                                    @endforeach
                                </div>
                            @elseif($a->mutasiItems && $a->mutasiItems->count() > 0)
                                <div class="small">
                                    @if($a->mutasiItems->where('type', 'asal')->count() > 0)
                                        <div class="text-danger fw-bold text-xs mb-1">DARI (OUT):</div>
                                        @foreach($a->mutasiItems->where('type', 'asal') as $item)
                                             <div class="ms-1 mb-1 border-start border-danger ps-2">
                                                {{ $item->product_name }} <span class="fw-bold">({{ (int)$item->qty }})</span>
                                             </div>
                                        @endforeach
                                    @endif
                                    @if($a->mutasiItems->where('type', 'tujuan')->count() > 0)
                                        <div class="text-success fw-bold text-xs mb-1 mt-2">KE (IN):</div>
                                        @foreach($a->mutasiItems->where('type', 'tujuan') as $item)
                                             <div class="ms-1 mb-1 border-start border-success ps-2">
                                                {{ $item->product_name }} <span class="fw-bold">({{ (int)$item->qty }})</span>
                                             </div>
                                        @endforeach
                                    @endif
                                </div>
                            @elseif($a->adjustItems && $a->adjustItems->count() > 0)
                                <div class="small">
                                    @foreach($a->adjustItems as $item)
                                        <div class="mb-1 border-bottom border-light pb-1">
                                            <div>{{ $item->product_name }}</div> 
                                            <div class="d-flex gap-2 mt-1">
                                                @if($item->qty_in > 0) <span class="badge bg-success bg-opacity-10 text-success">+{{ (int)$item->qty_in }}</span> @endif
                                                @if($item->qty_out > 0) <span class="badge bg-danger bg-opacity-10 text-danger">-{{ (int)$item->qty_out }}</span> @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($a->detail_barang)
                                {{-- Fallback ke JSON jika relasi kosong --}}
                                @php $details = is_string($a->detail_barang) ? json_decode($a->detail_barang, true) : $a->detail_barang; @endphp
                                @if(!empty($details['bundel']))
                                    <div class="small">
                                        @foreach($details['bundel'] as $item)
                                            <div class="d-flex justify-content-between border-bottom border-light mb-1 pb-1">
                                                <span class="text-dark fw-semibold"><i class="bi bi-file-earmark me-1"></i> {{ $item['no_doc'] ?? '-' }}</span>
                                                <span class="badge bg-secondary text-white">{{ $item['qty'] ?? 1 }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(!empty($details['mutasi_asal']) || !empty($details['mutasi_tujuan']))
                                    <div class="small">
                                        @if(!empty($details['mutasi_asal']))
                                            <div class="text-danger fw-bold text-xs mb-1">DARI (OUT):</div>
                                            @foreach($details['mutasi_asal'] as $it)
                                                <div class="ms-1 border-start border-danger ps-2">{{ $it['product_name'] ?? $it['nama_produk'] ?? '-' }} ({{ $it['qty'] ?? 0 }})</div>
                                            @endforeach
                                        @endif
                                    </div>
                                @elseif(!empty($details['adjust']))
                                    <div class="small">
                                        @foreach($details['adjust'] as $it)
                                            <div class="mb-1">
                                                {{ $it['product_name'] ?? $it['nama_produk'] ?? '-' }}
                                                <span class="text-success">+{{ $it['qty_in'] ?? 0 }}</span>
                                                <span class="text-danger">-{{ $it['qty_out'] ?? 0 }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
    
                            {{-- C. Tampilan No Document (Hasil Arsip Sistem) --}}
                            @if(!empty($a->no_doc_rows))
                                <div class="mt-2 mb-1">
                                    @foreach($a->no_doc_rows as $group)
                                        @foreach($group as $line)
                                            <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-xs mb-1 d-block text-start">
                                                <i class="bi bi-file-earmark-check me-1"></i> {{ $line }}
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            @endif
    
                            {{-- D. Tampilan No Transaksi Asal (BPB, SJ, dsb) --}}
                            @if(!empty($a->no_transaksi_rows))
                                <div class="small mt-2">
                                @foreach($a->no_transaksi_rows as $group)
                                    @if(isset($group[0]) && is_string($group[0]))
                                        <div class="text-secondary fw-bold text-xs"><i class="bi bi-link-45deg"></i> {{ strtoupper($group[0]) }}</div>
                                        @foreach(array_slice($group, 1) as $sub)
                                            @if(is_string($sub)) 
                                                <div class="text-muted ms-2 ps-2 my-1" style="font-size: 0.75rem;">↳ {{ strtoupper($sub) }}</div>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                                </div>
                            @elseif(empty($a->detail_barang) && empty($a->no_doc))
                                <span class="text-muted text-xs font-italic">- Tidak ada detail -</span>
                            @endif
                        </td>
    
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm" role="group">
                                {{-- BUKTI --}}
                                @if($a->bukti_scan)
                                    <button class="btn btn-sm btn-info text-white"
                                        onclick="showBukti('{{ route('preview.file', $a->bukti_scan) }}')" 
                                        title="Lihat Bukti">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-light text-muted border" disabled><i class="bi bi-eye-slash"></i></button>
                                @endif
    
                                 {{-- EDIT (AJAX) --}}
                                 <button class="btn btn-sm btn-warning text-dark border-0 hover-bg-slate" 
                                    onclick="editArsip({{ $a->id }})" title="Edit Data">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                
                                {{-- ARSIP SISTEM --}}
                                <button class="btn btn-sm btn-success text-white btn-arsip-sistem"
                                    data-id="{{ $a->id }}" data-bs-toggle="modal" data-bs-target="#modalArsipSistem" title="Arsip Sistem">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-light rounded-circle p-4 mb-3">
                                    <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                                </div>
                                <h6 class="text-secondary fw-bold">Data Arsip Tidak Ditemukan</h6>
                                <p class="text-muted small mb-0">Coba ubah filter pencarian Anda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 px-2">
    {{ $arsips->links('pagination::bootstrap-5') }}
</div>

{{-- INCLUDE MODALS --}}
@include('superadmin.arsip._create')
@include('superadmin.arsip._view')
@include('superadmin.arsip._arsip_sistem')

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEditArsip" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-gradient-warning text-white border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square fs-4"></i>
                    <div>
                         <h5 class="modal-title fw-bold">Edit Data Arsip</h5>
                         <small class="text-white-50">Edit data pengajuan (Superadmin Access)</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formEditArsip" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body p-4 bg-light">
                    <div id="alertEditSuper" class="alert alert-danger d-none py-2 px-3 small border-0 shadow-sm rounded-3"></div>
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            @include('superadmin.arsip._edit')
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-medium text-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // =========================================================================
    // A. LOGIKA TAMPILAN & TAMBAH DATA BARU (CREATE)
    // =========================================================================
    
    // Check if element exists
    const $jenisSelect  = $('#jenisPengajuanTambah');
    const $wrapKategori = $('#wrapperKategori');

    // 1. SHOW/HIDE SECTION
    if($jenisSelect.length) {
        $jenisSelect.on('change', function() {
            const val = $(this).val();
            
            // Reset tampilan
            $('.dynamic-section').addClass('d-none');
            // Reset Inputs (Optional: clear value logic can be added here)
            
            // Clear dynamic rows
            $('tbody.dynamic-row-container').empty(); 

            if (val === 'Cancel') {
                $wrapKategori.removeClass('d-none');
                $('#sectionNoTrans').removeClass('d-none');
            } 
            else if (val === 'Adjust') {
                $wrapKategori.addClass('d-none');
                $('#sectionAdjust').removeClass('d-none');
            } 
            else if (val && val.includes('Mutasi')) {
                $wrapKategori.addClass('d-none');
                $('#sectionMutasi').removeClass('d-none');
            } 
            else if (val === 'Bundel') {
                $wrapKategori.addClass('d-none');
                $('#sectionBundel').removeClass('d-none');
            } 
            else if (val === 'Internal_Memo') {
                 $wrapKategori.addClass('d-none');
                 $('#sectionNoTrans').removeClass('d-none');
            }
            else {
                $wrapKategori.addClass('d-none');
            }
        });

        // Trigger change on load
        $jenisSelect.trigger('change');
    }

    // Helper Random Index
    function getIndex() { return Math.floor(Math.random() * 100000); }

    // 2. TAMBAH BARIS ITEM (CREATE)
    // -- ADJUST --
    $('#btnAddAdjust').on('click', function() {
        let idx = getIndex();
        $('#wrapperAdjust').append(`
            <tr>
                <td><input type="text" name="detail_barang[adjust][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode"></td>
                <td><input type="text" name="detail_barang[adjust][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Barang" required></td>
                <td><input type="number" name="detail_barang[adjust][${idx}][qty_in]" class="form-control form-control-sm border-0 bg-light text-success fw-bold" value="0"></td>
                <td><input type="number" name="detail_barang[adjust][${idx}][qty_out]" class="form-control form-control-sm border-0 bg-light text-danger fw-bold" value="0"></td>
                <td><input type="text" name="detail_barang[adjust][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot"></td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    });

    // -- MUTASI --
    window.addMutasiRow = function(targetId, type) {
        let idx = getIndex();
        $(`#${targetId}`).append(`
            <tr>
                <td><input type="text" name="detail_barang[mutasi_${type}][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode"></td>
                <td><input type="text" name="detail_barang[mutasi_${type}][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Produk" required></td>
                <td><input type="number" name="detail_barang[mutasi_${type}][${idx}][qty]" class="form-control form-control-sm border-0 bg-light fw-bold" value="0" required></td>
                <td><input type="text" name="detail_barang[mutasi_${type}][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot"></td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    }
    $('#btnAddAsal').on('click', () => window.addMutasiRow('wrapperAsal', 'asal'));
    $('#btnAddTujuan').on('click', () => window.addMutasiRow('wrapperTujuan', 'tujuan'));

    // -- BUNDEL --
    $('#btnAddBundel').on('click', function() {
        let idx = getIndex();
        $('#wrapperBundel').append(`
            <tr>
                <td><input type="text" name="detail_barang[bundel][${idx}][no_doc]" class="form-control form-control-sm border-0 bg-light" placeholder="No Dokumen" required></td>
                <td><input type="number" name="detail_barang[bundel][${idx}][qty]" class="form-control form-control-sm border-0 bg-light fw-bold" value="1" required></td>
                <td><input type="text" name="detail_barang[bundel][${idx}][keterangan]" class="form-control form-control-sm border-0 bg-light" placeholder="Keterangan"></td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    });

    // -- HAPUS BARIS --
    $(document).on('click', '.btnRemove', function() { $(this).closest('tr').remove(); });


    // =========================================================================
    // B. LOGIKA EDIT (AJAX)
    // =========================================================================
    
    // Logic Show/Hide Fields di Edit
    $('#editJenisPengajuan').on('change', function() {
        const val = $(this).val();
        
        // Reset Inputs
        $('.dynamic-section-edit').addClass('d-none');
        
        // IMPORTANT: Kita tidak mengosongkan tbody disini agar data lama tidak hilang saat user main-main ganti jenis (UI only)
        // Tapi idealnya bisa di-warning. Untuk simpelnya kita show/hide saja.
        
        if (val === 'Cancel') {
            $('#editWrapperKategori').removeClass('d-none');
            $('#sectionNoTransEdit').removeClass('d-none');
        } 
        else if (val === 'Adjust') {
            $('#sectionAdjustEdit').removeClass('d-none');
        } 
        else if (val && val.includes('Mutasi')) {
            $('#sectionMutasiEdit').removeClass('d-none');
        } 
        else if (val === 'Bundel') {
            $('#sectionBundelEdit').removeClass('d-none');
        }
        else if (val === 'Internal_Memo') {
             $('#sectionNoTransEdit').removeClass('d-none');
        }
    });

    window.editArsip = function(id) {
        // 1. Reset Form
        $('#formEditArsip')[0].reset();
        $('.dynamic-section-edit').addClass('d-none');
        $('#wrapperAdjustEdit, #wrapperAsalEdit, #wrapperTujuanEdit, #wrapperBundelEdit').empty();
        $('#editArsipId').val(id);
        $('#linkBuktiSaatIni').text('');

        // 2. Fetch Data
        $.ajax({
            url: "/superadmin/arsip/" + id + "/edit", // Pastikan Route ini ADA
            type: "GET",
            beforeSend: function() {
                // Bisa kasih loading spinner
            },
            success: function(response) {
                let data = response.data;
                
                // 3. Set URL Update
                $('#formEditArsip').attr('action', "/superadmin/arsip/" + id);
                
                // 4. Fill Data
                $('#editUserId').val(data.admin_id).trigger('change'); // Jika pakai select2
                $('#editTglPengajuan').val(data.tgl_pengajuan ? data.tgl_pengajuan.substring(0, 10) : '');
                $('#editTglArsip').val(data.tgl_arsip ? data.tgl_arsip.substring(0, 10) : '');
                
                $('#editJenisPengajuan').val(data.jenis_pengajuan);
                $('#editKategori').val(data.kategori);
                $('#editDepartment').val(data.department_id);
                $('#editUnit').val(data.unit_id);
                $('#editManager').val(data.manager_id);
                $('#editNoTransaksi').val(data.no_transaksi);
                $('#editKeterangan').val(data.keterangan);
                
                $('#editStatus').val(data.status);
                $('#editKetProcess').val(data.ket_process);
                $('#editBa').val(data.ba);
                $('#editArsipStatus').val(data.arsip);

                // 5. Bukti Scan
                if(data.bukti_scan) {
                     $('#linkBuktiSaatIni').html(
                        `<a href="/preview-file/${data.bukti_scan}" target="_blank" class="text-decoration-none fw-bold small text-primary">
                            <i class="bi bi-file-earmark-pdf"></i> Lihat File Saat Ini
                        </a>`
                    );
                }

                // 6. trigger change untuk nampilin section yg benar
                $('#editJenisPengajuan').trigger('change');

                // 7. Fill Detail Items
                // Perlu handling field JSON vs Relasi. Controller edit with relations.
                
                // A. Bundel
                if(data.bundel_items && data.bundel_items.length > 0) {
                    data.bundel_items.forEach(item => {
                        window.addBundelRowEdit(item.no_doc, item.qty, item.keterangan);
                    });
                }
                
                // B. Adjust
                if(data.adjust_items && data.adjust_items.length > 0) {
                    data.adjust_items.forEach(item => {
                        window.addAdjustRowEdit(item.product_code, item.product_name, item.qty_in, item.qty_out, item.lot);
                    });
                }

                // C. Mutasi
                if(data.mutasi_items && data.mutasi_items.length > 0) {
                    data.mutasi_items.forEach(item => {
                        let type = (item.type === 'asal') ? 'asal' : 'tujuan';
                        window.addMutasiRowEdit(type, item.product_code, item.product_name, item.qty, item.lot);
                    });
                }

                // Fallback Legacy JSON (If needed)
                if((!data.bundel_items || data.bundel_items.length==0) && (!data.adjust_items || data.adjust_items.length==0) && (!data.mutasi_items || data.mutasi_items.length==0)) {
                    // Try parsing details column if exist
                }

                $('#modalEditArsip').modal('show');
            },
            error: function(xgb) {
                alert("Gagal mengambil data arsip.");
            }
        });
    }

    // 3. ARSIP SISTEM
    $('.btn-arsip-sistem').on('click', function() {
         const id = $(this).data('id');
         const f = document.getElementById('formArsipSistem');
         if(f) f.action = `/superadmin/arsip/${id}/arsip-sistem`;
    });

});

// =========================================================================
// C. HELPERS UNTUK EDIT ROW
// =========================================================================

window.addAdjustRowEdit = function(code='', name='', qtyIn=0, qtyOut=0, lot='') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    // Note: Name input hrs sesuai dengan Controller update method expectation. 
    // Di Superadmin ArsipController::update, 'detail_barang' diambil array.
    // Kita samakan structure dengan create: items[adjust][...] atau detail_barang[adjust]...
    // Cek Controller update.. dia merge $request->detail_barang.
    // Jadi name harus: detail_barang[adjust][idx][...]
    
    $('#wrapperAdjustEdit').append(`
        <tr>
            <td><input type="text" name="detail_barang[adjust][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" value="${code ?? ''}"></td>
            <td><input type="text" name="detail_barang[adjust][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" value="${name ?? ''}" required></td>
            <td><input type="number" name="detail_barang[adjust][${idx}][qty_in]" class="form-control form-control-sm border-0 bg-light text-center" value="${qtyIn}"></td>
            <td><input type="number" name="detail_barang[adjust][${idx}][qty_out]" class="form-control form-control-sm border-0 bg-light text-center" value="${qtyOut}"></td>
            <td><input type="text" name="detail_barang[adjust][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" value="${lot ?? ''}"></td>
            <td><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>
    `);
};

window.addMutasiRowEdit = function(type, code='', name='', qty=0, lot='') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    let wrapper = (type === 'asal') ? '#wrapperAsalEdit' : '#wrapperTujuanEdit';
    let key = (type === 'asal') ? 'mutasi_asal' : 'mutasi_tujuan'; // Match controller logic for JSON
    
    // Namun controller pakai 'items' atau 'detail_barang'?
    // ArsipController::store pakai $request->detail_barang['adjust']
    // Jadi kita pakai detail_barang[...]

    $(wrapper).append(`
        <tr>
            <td><input type="text" name="detail_barang[${key}][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" value="${code ?? ''}"></td>
            <td><input type="text" name="detail_barang[${key}][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" value="${name ?? ''}" required></td>
            <td><input type="number" name="detail_barang[${key}][${idx}][qty]" class="form-control form-control-sm border-0 bg-light text-center" value="${qty}"></td>
            <td><input type="text" name="detail_barang[${key}][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" value="${lot ?? ''}"></td>
            <td><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>
    `);
};

window.addBundelRowEdit = function(no_doc='', qty=1, ket='') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    $('#wrapperBundelEdit').append(`
        <tr>
            <td><input type="text" name="detail_barang[bundel][${idx}][no_doc]" class="form-control form-control-sm border-0 bg-light" value="${no_doc ?? ''}" required></td>
            <td><input type="number" name="detail_barang[bundel][${idx}][qty]" class="form-control form-control-sm border-0 bg-light text-center" value="${qty}"></td>
            <td><input type="text" name="detail_barang[bundel][${idx}][keterangan]" class="form-control form-control-sm border-0 bg-light" value="${ket ?? ''}"></td>
            <td><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>
    `);
};

</script>
@endpush