@extends('layouts.app')

@section('title','Pengajuan')
@section('page-title', '📁 Pengajuan Saya')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
    
    body { font-family: 'Outfit', sans-serif; background-color: #f4f7fa; color: #0f172a; }
    
    /* Global Typography & Clarity - High Contrast */
    .text-secondary { color: #334155 !important; font-weight: 600; }
    .text-muted { color: #64748b !important; }
    .small, small { font-size: 0.8rem; font-weight: 600; }
    .fw-extrabold { font-weight: 800 !important; }
    
    /* Responsive Table Utility */
    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .table-responsive::-webkit-scrollbar { height: 6px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* Table internal styling */
    .table thead th {
        font-size: 0.725rem;
        letter-spacing: 0.1em;
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        text-transform: uppercase;
        color: #475569;
        font-weight: 800;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f1f5f9;
        transition: background-color 0.2s ease;
    }

    /* Premium Stats Cards Refinements */
    .stat-card-main {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
    
    .mesh-gradient {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: 
            radial-gradient(at 0% 0%, rgba(255,255,255,0.15) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(255,255,255,0.1) 0px, transparent 50%);
        z-index: 1;
    }
    
    .pattern-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        z-index: 1;
        opacity: 0.2;
    }

    .glass-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .mini-stat-card {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .mini-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        border-color: #e2e8f0;
    }

    /* Table Item Cards Premium Styling */
    .item-detail-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 6px;
        transition: border-color 0.2s;
    }
    .item-detail-card:hover { border-color: #cbd5e1; }
    
    .note-detail-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 5px solid #0ea5e9 !important;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    
    .text-primary-dark { color: #0f172a !important; }
</style>
@endpush

@section('content')

{{-- ================= FILTER SECTION ================= --}}
<div class="card border-0 shadow-sm mb-4 animate-on-scroll" style="border-radius: 12px;">
    <div class="card-body p-4">
        <form method="GET" class="row g-3">
            {{-- ROW 1 --}}
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">🔍 Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-0 px-3" placeholder="No Dok, Transaksi..." style="border-radius: 0 8px 8px 0;">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">📅 Dari</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control bg-light border-0 px-3" style="border-radius: 8px;">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">📅 Sampai</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control bg-light border-0 px-3" style="border-radius: 8px;">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">📄 Jenis</label>
                <select name="jenis_pengajuan" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                    <option value="">Semua Jenis</option>
                    @foreach(['Cancel','Adjust','Mutasi_Billet','Mutasi_Produk','Internal_Memo','Bundel'] as $jp)
                        <option value="{{ $jp }}" {{ (request('jenis_pengajuan')==$jp || request('jenis')==$jp)?'selected':'' }}>{{ str_replace('_', ' ', $jp) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                 <label class="form-label small fw-bold text-secondary mb-1">⚙️ Status</label>
                 <select name="ket_process" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                     <option value="">Semua Status</option>
                     @foreach(['Review','Process','Done','Pending','Void'] as $st)
                         <option value="{{ $st }}" {{ request('ket_process')==$st?'selected':'' }}>{{ $st }}</option>
                     @endforeach
                 </select>
            </div>

            {{-- ROW 2 --}}
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">🏢 Departemen</label>
                <select name="department_id" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                    <option value="">Semua</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">📦 Unit</label>
                <select name="unit_id" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                    <option value="">Semua</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id')==$u->id?'selected':'' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">👤 Manager</label>
                <select name="manager_id" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                    <option value="">Semua</option>
                    @foreach($managers as $m)
                        <option value="{{ $m->id }}" {{ request('manager_id')==$m->id?'selected':'' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1">
                <label class="form-label small fw-bold text-secondary mb-1">🏷️ Kategori</label>
                <select name="kategori" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                    <option value="">Semua</option>
                    <option value="Human" {{ request('kategori')=='Human'?'selected':'' }}>Human</option>
                    <option value="System" {{ request('kategori')=='System'?'selected':'' }}>System</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary fw-bold shadow-sm flex-fill d-flex align-items-center justify-content-center" style="background: #4f46e5; border-color: #4f46e5; border-radius: 10px; height: 38px;">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <a href="{{ route('admin.arsip.index') }}" class="btn btn-white border bg-white shadow-sm d-flex align-items-center justify-content-center" style="border-radius: 10px; width: 42px; height: 38px;" title="Reset">
                    <i class="bi bi-arrow-counterclockwise text-secondary fw-bold"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ================= STATS OVERVIEW ================= --}}
@php
    $fJenis = request('jenis') ?? request('jenis_pengajuan');
    $sConfig = [
        'title' => 'SUBMISI SAYA', 'icon' => 'bi-grid-fill', 'color' => '#21d1a5ff',
        'bg' => 'linear-gradient(135deg, #16dac9ff 0%, #13cde6ff 100%)',
    ];
    if($fJenis == 'Cancel') { $sConfig = [ 'title' => 'TOTAL CANCEL', 'icon' => 'bi-x-octagon-fill', 'color' => '#ef4444', 'bg' => 'linear-gradient(135deg, #ef4444 0%, #f87171 100%)' ]; }
    elseif($fJenis == 'Adjust') { $sConfig = [ 'title' => 'TOTAL ADJUST', 'icon' => 'bi-sliders', 'color' => '#0ea5e9', 'bg' => 'linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%)' ]; }
@endphp

@php
    $tinyStats = [
        ['label' => 'REVIEW', 'key' => 'Review', 'icon' => 'bi-clock-history', 'color' => '#3b82f6'],
        ['label' => 'ON PROCESS', 'key' => 'Process', 'icon' => 'bi-hourglass-split', 'color' => '#f59e0b'],
        ['label' => 'PENDING', 'key' => 'Pending', 'icon' => 'bi-pause-circle-fill', 'color' => '#64748b'],
        ['label' => 'DONE', 'key' => 'Done', 'icon' => 'bi-check-circle-fill', 'color' => '#10b981'],
        ['label' => 'VOID / REJECT', 'key' => 'Void', 'icon' => 'bi-slash-circle-fill', 'color' => '#ef4444'],
    ];
@endphp

<div class="row g-3 mb-4 animate-on-scroll">
    {{-- Main Stat Card - Premium Refined --}}
    <div class="col-xl-3 col-lg-4 col-md-12">
        <a href="{{ request()->fullUrlWithQuery(['ket_process' => null]) }}" class="text-decoration-none">
            <div class="card border-0 stat-card-main text-white h-100 p-1" style="background: {{ $sConfig['bg'] }}; min-height: 140px;">
                <div class="mesh-gradient"></div>
                <div class="pattern-overlay"></div>
                
                <div class="card-body p-4 d-flex align-items-center gap-4 position-relative" style="z-index: 2;">
                    <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 64px; height: 64px; backdrop-filter: blur(4px);">
                        <i class="bi {{ $sConfig['icon'] }} fs-1 text-white"></i>
                    </div>
                    <div>
                        <h1 class="fw-extrabold mb-0 lh-1" style="font-size: 2.5rem; letter-spacing: -2px;">{{ number_format($stats['total'] ?? 0) }}</h1>
                        <p class="mb-0 small fw-extrabold opacity-75 mt-2 letter-spacing-1 text-uppercase text-white" style="font-size: 0.72rem;">{{ $sConfig['title'] }}</p>
                    </div>
                    <div class="position-absolute top-0 end-0 p-3">
                        <div class="glass-badge">ACTIVE</div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-9 col-lg-8 col-md-12">
        <div class="row g-3 h-100">
            @foreach($tinyStats as $ts)
            <div class="col-md col-sm-6">
                <a href="{{ request()->fullUrlWithQuery(['ket_process' => $ts['key']]) }}" class="text-decoration-none h-100">
                    <div class="card mini-stat-card border-0 shadow-sm h-100" style="background: {{ $ts['color'] }}08;">
                        <div class="card-body p-3 text-center d-flex flex-column align-items-center justify-content-center">
                            <div class="bg-white shadow-xs rounded-circle d-flex align-items-center justify-content-center mb-2" 
                                 style="width: 44px; height: 44px;">
                                <i class="bi {{ $ts['icon'] }}" style="font-size: 1.2rem; color: {{ $ts['color'] }};"></i>
                            </div>
                            <h4 class="fw-extrabold text-primary-dark mb-0 lh-1">{{ number_format($stats[$ts['key']] ?? 0) }}</h4>
                            <div class="text-secondary fw-extrabold text-uppercase mt-2" style="font-size: 0.55rem; letter-spacing: 1px; opacity: 0.8;">{{ $ts['label'] }}</div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ================= HEADER & ACTIONS ================= --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-table me-2 text-primary"></i>Daftar Pengajuan</h5>
        <div class="text-muted small">
            Showing <span class="fw-bold">{{ $arsips->firstItem() ?? 0 }}</span> - <span class="fw-bold">{{ $arsips->lastItem() ?? 0 }}</span> of <span class="fw-bold">{{ $arsips->total() }}</span> data
        </div>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center bg-white rounded-pill px-3 py-1 shadow-sm border">
            <small class="text-secondary fw-bold me-2" style="font-size: 0.75rem;">SHOW:</small>
            <select id="perPageSelect" class="form-select form-select-sm border-0 bg-transparent fw-bold text-primary py-0 ps-0 pe-4" style="width: auto; cursor: pointer; box-shadow: none;">
                @foreach([10, 25, 50, 100, 250, 500, 1000] as $size)
                    <option value="{{ $size }}" {{ request('per_page') == $size ? 'selected' : '' }}>{{ $size }} Rows</option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary rounded-pill shadow px-4 py-2 fw-extrabold d-flex align-items-center" 
                data-bs-toggle="modal" data-bs-target="#modalTambahArsip"
                style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border: none;">
            <i class="bi bi-plus-circle-fill me-2 fs-5"></i>BUAT BARU
        </button>
    </div>
</div>

{{-- ================= DATA TABLE ================= --}}
<div class="card border-0 shadow-sm animate-on-scroll" style="border-radius: 12px; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th style="width: 250px;">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'no_registrasi', 'dir' => (request('sort')=='no_registrasi' && request('dir')=='asc') ? 'desc' : 'asc']) }}" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                <span class="text-uppercase fw-bold">No. Reg / Transaksi</span>
                                @if(request('sort')=='no_registrasi')
                                    <i class="bi bi-sort-{{ request('dir')=='asc' ? 'alpha-down' : 'alpha-up-alt' }} text-primary"></i>
                                @else
                                    <i class="bi bi-hash opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 150px;">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'tgl_pengajuan', 'dir' => (request('sort')=='tgl_pengajuan' && request('dir')=='asc') ? 'desc' : 'asc']) }}" class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                <span class="text-uppercase fw-bold">Tgl Pengajuan</span>
                                @if(request('sort')=='tgl_pengajuan')
                                    <i class="bi bi-sort-numeric-{{ request('dir')=='asc' ? 'down' : 'up-alt' }} text-primary"></i>
                                @else
                                    <i class="bi bi-calendar-event opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 200px;"><span class="text-uppercase fw-bold">Departemen & Unit</span></th>
                        <th class="text-center" style="width: 100px;"><span class="text-uppercase fw-bold">QTY</span></th>
                        <th style="width: 320px;"><span class="text-uppercase fw-bold">Detail Dokumen</span></th>
                        <th class="text-center" style="width: 150px;">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'ket_process', 'dir' => (request('sort')=='ket_process' && request('dir')=='asc') ? 'desc' : 'asc']) }}" class="text-secondary text-decoration-none d-flex align-items-center justify-content-center gap-2">
                                <span class="text-uppercase fw-bold">Status</span>
                                @if(request('sort')=='ket_process')
                                    <i class="bi bi-sort-alpha-{{ request('dir')=='asc' ? 'down' : 'up-alt' }} text-primary"></i>
                                @else
                                    <i class="bi bi-info-circle opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-end pe-4" style="width: 120px;"><span class="text-uppercase fw-bold">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($arsips as $a)
                    <tr class="transition-hover" style="font-size: 0.85rem; border-bottom: 1px solid #f1f5f9;">
                        <td class="ps-4 text-center fw-bold text-muted">
                            {{ ($arsips->currentPage() - 1) * $arsips->perPage() + $loop->iteration }}
                        </td>
                        
                        <td class="ps-3">
                            <div class="d-flex flex-column gap-1">
                                {{-- No Registrasi styling matching Superadmin --}}
                                 @if($a->no_registrasi)
                                     <div class="d-flex align-items-center mb-1">
                                         <div class="px-3 py-1 rounded-pill fw-bold font-monospace bg-white border border-info border-opacity-50 text-info shadow-sm" 
                                              style="font-size: 0.75rem; letter-spacing: 0.5px; border-width: 2px !important;">
                                             <i class="bi bi-bookmark-fill me-2 opacity-50"></i>{{ $a->no_registrasi }}
                                         </div>
                                     </div>
                                 @endif

                                 {{-- SYSTEM GENERATED NO DOC --}}
                                 @if($a->no_doc)
                                     <div class="d-flex align-items-center mb-1 ps-2">
                                          <div class="text-primary fw-extrabold font-monospace" style="font-size: 0.82rem; border-left: 3px solid #0d6efd; padding-left: 8px;">
                                              {{ strtoupper($a->no_doc) }}
                                          </div>
                                     </div>
                                 @endif
    
                                 {{-- No Transaksi Hierarchy with Monospace & Arrows --}}
                                 @if($a->no_transaksi)
                                     <div class="d-flex flex-column gap-1 ps-2">
                                         @if(!empty($a->no_transaksi_rows))
                                             @foreach($a->no_transaksi_rows as $group)
                                                 @foreach($group as $line)
                                                     @php $isInduk = $loop->first; @endphp
                                                     <div class="d-flex align-items-center {{ $isInduk ? 'mb-1' : 'ms-3 mb-1' }}">
                                                         <i class="bi {{ $isInduk ? 'bi-file-earmark-text' : 'bi-arrow-return-right' }} hierarchy-connector"></i>
                                                         <span class="text-secondary fw-bold font-monospace" style="font-size: 0.78rem; letter-spacing: -0.2px;">{{ $line }}</span>
                                                     </div>
                                                 @endforeach
                                             @endforeach
                                         @else
                                             <div class="d-flex align-items-center">
                                                 <i class="bi bi-file-earmark-text hierarchy-connector"></i>
                                                 <span class="text-secondary fw-bold font-monospace" style="font-size: 0.78rem;">{{ $a->no_transaksi }}</span>
                                             </div>
                                         @endif
                                     </div>
                                 @endif
                            </div>
                        </td>

                        <td>
                            <div class="text-dark fw-bold" style="font-size: 0.9rem;">
                                {{ optional($a->tgl_pengajuan)->format('d M Y') }}
                            </div>
                            <div class="text-muted fw-bold mt-1" style="font-size: 0.72rem;">
                                <i class="bi bi-clock me-1 opacity-50 text-primary"></i>{{ optional($a->tgl_pengajuan)->format('H:i') }} WIB
                            </div>
                        </td>
    
                        <td>
                            <div class="fw-bold text-dark lh-sm mb-1 text-truncate" style="max-width: 160px; font-size: 0.88rem;">{{ $a->department->name ?? '-' }}</div>
                            <span class="bg-light text-secondary px-2 py-0 rounded fw-bold" style="font-size: 0.68rem; border: 1px solid #cbd5e1;">{{ $a->unit->name ?? '-' }}</span>
                        </td>

                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <span class="fw-bold text-success" style="font-size: 0.85rem;">+{{ $a->total_qty_in + 0 }}</span>
                                <span class="fw-bold text-danger" style="font-size: 0.85rem;">-{{ $a->total_qty_out + 0 }}</span>
                            </div>
                        </td>
    
                        <td class="ps-3 pe-2">
                            <div class="d-flex flex-column gap-2" style="max-width: 320px;">
                                @php $itemsFound = false; @endphp
                                
                                {{-- BUNDEL --}}
                                @if($a->bundelItems && $a->bundelItems->count() > 0)
                                    @php $itemsFound = true; @endphp
                                    @foreach($a->bundelItems as $item)
                                        <div class="d-flex flex-column">
                                            <div class="item-label-prefix">In - Dokumen Bundel</div>
                                            <div class="item-detail-card">
                                                <div class="lh-1 pe-2">
                                                    <div class="fw-bold text-dark font-monospace mb-1" style="font-size: 0.82rem;">{{ $item->no_doc }}</div>
                                                    <small class="text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 700;">Bundel Archive</small>
                                                </div>
                                                <div class="qty-bubble in">+{{ $item->qty + 0 }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- MUTASI --}}
                                @if($a->mutasiItems && $a->mutasiItems->count() > 0)
                                    @php $itemsFound = true; @endphp
                                    @foreach($a->mutasiItems as $item)
                                        @php 
                                            $isOut = $item->type == 'asal';
                                            $qClass = $isOut ? 'out' : 'in';
                                            $qPrefix = $isOut ? '-' : '+';
                                            $typeLabel = $isOut ? 'Out - Mutasi Asal' : 'In - Mutasi Tujuan';
                                        @endphp
                                        <div class="d-flex flex-column">
                                            <div class="item-label-prefix">{{ $typeLabel }}</div>
                                            <div class="item-detail-card">
                                                <div class="lh-1 pe-2">
                                                    <div class="fw-extrabold text-dark font-monospace mb-1" style="font-size: 0.82rem;">{{ $item->product_code }}</div>
                                                    <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">{{ $item->product_name }}</div>
                                                    @if($item->panjang || $item->location)
                                                        <div class="mt-1 d-flex gap-1" style="font-size: 0.62rem;">
                                                            @if($item->panjang) <span class="badge bg-secondary opacity-75">P: {{ $item->panjang }}</span> @endif
                                                            @if($item->location) <span class="badge bg-light text-dark border" title="{{ $item->location }}"><i class="bi bi-geo-alt"></i> Loc</span> @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="qty-bubble {{ $qClass }}">{{ $qPrefix }}{{ $item->qty + 0 }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- ADJUST --}}
                                @if($a->adjustItems && $a->adjustItems->count() > 0)
                                    @php $itemsFound = true; @endphp
                                    @foreach($a->adjustItems as $item)
                                        @php 
                                            $isOut = ($item->qty_out ?? 0) > 0;
                                            $qClass = $isOut ? 'out' : 'in';
                                            $qPrefix = $isOut ? '-' : '+';
                                            $qty = $isOut ? $item->qty_out : ($item->qty_in ?? ($item->qty_adjust ?? 0));
                                            $typeLabel = $isOut ? 'Out - Adjustment' : 'In - Adjustment';
                                        @endphp
                                        <div class="d-flex flex-column">
                                            <div class="item-label-prefix">{{ $typeLabel }}</div>
                                            <div class="item-detail-card">
                                                <div class="lh-1 pe-2">
                                                    <div class="fw-extrabold text-dark font-monospace mb-1" style="font-size: 0.82rem;">{{ $item->product_code }}</div>
                                                    <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">{{ $item->product_name }}</div>
                                                </div>
                                                <div class="qty-bubble {{ $qClass }}">{{ $qPrefix }}{{ $qty + 0 }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- PEMOHON --}}
                                @if($a->pemohon)
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="badge bg-light text-primary border border-primary border-opacity-10 py-1 px-2" style="font-size: 0.65rem;">
                                            <i class="bi bi-people-fill me-1"></i> {{ Str::limit($a->pemohon, 30) }}
                                        </div>
                                    </div>
                                @endif

                                {{-- Premium Digital Memo Card for fallback keterangan --}}
                                @if(!$itemsFound && $a->keterangan)
                                    <div class="mt-1">
                                        <div class="text-muted italic fw-medium" style="font-size: 0.75rem; line-height: 1.2;">
                                            <i class="bi bi-chat-left-dots me-1"></i> "{{ Str::limit($a->keterangan, 50) }}"
                                        </div>
                                    </div>
                                @elseif(!$itemsFound)
                                    <div class="text-muted small text-center opacity-50">- No Data -</div>
                                @endif
                            </div>
                        </td>

                        <td class="text-center">
                            <div class="d-flex flex-column gap-2 align-items-center">
                                {{-- Optimized Status Pills for Clarity --}}
                                @php
                                    $kpC = match($a->ket_process) {
                                        'Review'  => ['bg' => '#fefce8', 'text' => '#854d0e', 'border' => '#fde047', 'dot' => '#facc15'],
                                        'Process' => ['bg' => '#f0f9ff', 'text' => '#075985', 'border' => '#7dd3fc', 'dot' => '#38bdf8'], 
                                        'Done'    => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#86efac', 'dot' => '#22c55e'],
                                        'Pending' => ['bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1', 'dot' => '#64748b'],
                                        'Void'    => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fca5a5', 'dot' => '#ef4444'],
                                        default   => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0', 'dot' => '#94a3b8'],
                                    };
                                @endphp
                                <div class="px-3 py-1 rounded-pill fw-bold d-flex align-items-center gap-2 shadow-xs" 
                                     style="font-size: 0.72rem; background: {{ $kpC['bg'] }}; color: {{ $kpC['text'] }}; border: 1.5px solid {{ $kpC['border'] }};">
                                    <div class="rounded-circle shadow-sm" style="width: 6px; height: 6px; background-color: {{ $kpC['dot'] }};"></div>
                                    {{ strtoupper($a->ket_process ?? '-') }}
                                </div>
                            </div>
                        </td>

                        <td class="text-end pe-4">
                            <div class="d-flex gap-2 justify-content-end">
                                {{-- VIEW BUKTI SCAN --}}
                                <button class="btn btn-sm btn-info text-white shadow-sm rounded-3 p-2 d-flex align-items-center" 
                                        onclick="showBukti('{{ $a->bukti_scan ? url('/preview-file/'.$a->bukti_scan) : '#' }}')" 
                                        {{ !$a->bukti_scan ? 'disabled' : '' }} title="View Bukti Scan">
                                    <i class="bi bi-eye-fill"></i>
                                 </button>
                                 @if(!in_array($a->status, ['Done', 'Reject', 'Void']) && !in_array($a->ket_process, ['Done', 'Void']))
                                    <button class="btn btn-sm btn-warning text-dark shadow-sm rounded-3 p-2 d-flex align-items-center px-3 fw-bold" 
                                            onclick="editArsip({{ $a->id }})" title="Edit Data">
                                        <i class="bi bi-pencil-fill me-2"></i> EDIT
                                    </button>
                                 @endif
                             </div>
                        </td>
                    </tr>   
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-light rounded-circle p-4 mb-3">
                                    <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                                </div>
                                <h6 class="text-secondary fw-bold">Belum Ada Data Pengajuan</h6>
                                <p class="text-muted small mb-0">Klik tombol "Buat Baru" untuk memulai.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($arsips->hasPages())
        <div class="card-footer bg-white border-top border-light p-3">
             {{ $arsips->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

{{-- Include Modal Partial --}}
@include('admin.arsip._create')
@include('admin.arsip._edit')
@include('superadmin.arsip._view')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    // =========================================================================
    // 0. PAGINATION SIZE
    // =========================================================================
    $('#perPageSelect').on('change', function() {
        let perPage = $(this).val();
        let url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        window.location.href = url.toString();
    });

    // =========================================================================
    // A. LOGIKA TAMPILAN & TAMBAH DATA BARU (CREATE)
    // =========================================================================
    
    // Check if element exists to avoid errors on other pages
    const $jenisSelect  = $('#jenisPengajuanTambahAdmin');
    if(!$jenisSelect.length) return;

    const $wrapKategori = $('#wrapperKategori');

    // 1. SHOW/HIDE SECTION
    $jenisSelect.on('change', function() {
        const val = $(this).val();
        
        // Reset tampilan
        $('.dynamic-section').addClass('d-none');
        // Reset Inputs inside dynamic sections
        $('.dynamic-section input').prop('required', false).val('');
        $('.dynamic-section textarea').prop('required', false).val('');
        
        // Clear dynamic rows
        $('tbody.dynamic-row-container').empty(); 

        if (val === 'Cancel') {
            $wrapKategori.removeClass('d-none');
            $('#sectionNoTrans').removeClass('d-none');
            $('#sectionNoTrans textarea').prop('required', true);
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
        else {
            $wrapKategori.addClass('d-none');
        }
    });

    // Helper Random Index
    function getIndex() { return Math.floor(Math.random() * 100000); }

    // 2. TAMBAH BARIS ITEM (CREATE)
    // -- ADJUST --
    $('#btnAddAdjust').on('click', function() {
        let idx = getIndex();
        $('#wrapperAdjust').append(`
            <tr>
                <td><input type="text" name="adjust[${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode" required></td>
                <td><input type="text" name="adjust[${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Barang" required></td>
                <td><input type="number" step="any" name="adjust[${idx}][qty_in]" class="form-control form-control-sm border-0 bg-light text-success fw-bold" value="0" style="min-width: 80px;"></td>
                <td><input type="number" step="any" name="adjust[${idx}][qty_out]" class="form-control form-control-sm border-0 bg-light text-danger fw-bold" value="0" style="min-width: 80px;"></td>
                <td><input type="text" name="adjust[${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot"></td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    });

    // -- MUTASI --
    window.addMutasiRow = function(targetId, prefixName) {
        let idx = getIndex();
        let locations = @json(\App\Models\ArsipMutasiItem::getLocations());
        let locationOptions = '<option value="">-- Lokasi --</option>';
        locations.forEach(loc => {
            locationOptions += `<option value="${loc}">${loc}</option>`;
        });

        $(`#${targetId}`).append(`
            <tr>
                <td><input type="text" name="${prefixName}[${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode" required style="width: 80px;"></td>
                <td><input type="text" name="${prefixName}[${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Produk" required style="min-width: 150px;"></td>
                <td><input type="number" step="any" name="${prefixName}[${idx}][qty]" class="form-control form-control-sm border-0 bg-light fw-bold text-center" value="1" required style="width: 70px;"></td>
                <td><input type="text" name="${prefixName}[${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot" style="width: 90px;"></td>
                <td><input type="text" name="${prefixName}[${idx}][panjang]" class="form-control form-control-sm border-0 bg-light" placeholder="Pjg" style="width: 80px;"></td>
                <td>
                    <select name="${prefixName}[${idx}][location]" class="form-select form-select-sm border-0 bg-light" style="width: 150px;">
                        ${locationOptions}
                    </select>
                </td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    }
    // Bind click events
    $('#btnAddAsal').on('click', () => window.addMutasiRow('wrapperAsal', 'mutasi_asal'));
    $('#btnAddTujuan').on('click', () => window.addMutasiRow('wrapperTujuan', 'mutasi_tujuan'));

    // -- BUNDEL --
    $('#btnAddBundel').on('click', function() {
        let idx = getIndex();
        $('#wrapperBundel').append(`
            <tr>
                <td><input type="text" name="bundel[${idx}][no_doc]" class="form-control form-control-sm border-0 bg-light" placeholder="No Dokumen" required></td>
                <td><input type="number" step="any" name="bundel[${idx}][qty]" class="form-control form-control-sm border-0 bg-light fw-bold" value="1" required></td>
                <td><input type="text" name="bundel[${idx}][keterangan]" class="form-control form-control-sm border-0 bg-light" placeholder="Keterangan"></td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    });

    // -- HAPUS BARIS --
    $(document).on('click', '.btnRemove', function() { $(this).closest('tr').remove(); });
    
    // Trigger change saat load agar form create bersih
    if($jenisSelect.length) $jenisSelect.trigger('change');


    // =========================================================================
    // B. LOGIKA SIMPAN PERUBAHAN (EDIT / UPDATE)
    // =========================================================================
    
    $('#formEditArsip').on('submit', function(e) {
        e.preventDefault(); // STOP submit bawaan browser

        let id = $('#editArsipId').val(); // Ambil ID
        
        if(!id) {
            alert("Error: ID Arsip tidak ditemukan! Silakan refresh halaman.");
            return;
        }

        // Susun URL Update
        let baseUrl = window.location.origin; 
        let urlUpdate = baseUrl + '/admin/arsip/' + id; 

        // Siapkan Data (FormData menangani file upload)
        let formData = new FormData(this);
        formData.append('_method', 'PUT'); // Method Spoofing untuk Laravel

        $.ajax({
            url: urlUpdate,
            type: 'POST', // POST with _method=PUT
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('button[type="submit"]', '#formEditArsip').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
            },
            success: function(response) {
                $('#modalEditArsip').modal('hide');
                alert('Data Berhasil Diupdate!');
                location.reload(); 
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                let pesan = xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText;
                alert('Gagal Update: ' + pesan);
                $('button[type="submit"]', '#formEditArsip').prop('disabled', false).text('Simpan Perubahan');
            }
        });
    });

}); // End Document Ready


// =========================================================================
// C. FUNGSI GLOBAL UNTUK EDIT MODAL (Diakses dari tombol tabel)
// =========================================================================

// Helper Add Row Edit
window.addAdjustRowEdit = function(code = '', name = '', qty_in = 0, qty_out = 0, lot = '') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    let html = `
        <tr>
            <td class="ps-3"><input type="text" name="detail_barang[adjust][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode" value="${code}" required style="width: 80px;"></td>
            <td><input type="text" name="detail_barang[adjust][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Barang" value="${name}" required></td>
            <td><input type="number" step="any" name="detail_barang[adjust][${idx}][qty_in]" class="form-control form-control-sm text-center border-0 bg-light" value="${qty_in}" style="min-width: 80px;"></td>
            <td><input type="number" step="any" name="detail_barang[adjust][${idx}][qty_out]" class="form-control form-control-sm text-center border-0 bg-light" value="${qty_out}" style="min-width: 80px;"></td>
            <td><input type="text" name="detail_barang[adjust][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot" value="${lot}"></td>
            <td class="text-end pe-2"><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>`;
    $('#wrapperAdjustEdit').append(html);
};

window.addMutasiRowEdit = function(type, code = '', name = '', qty = 1, lot = '', panjang = '', location = '') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    let color = type === 'asal' ? 'danger' : 'success';
    let key = type === 'asal' ? 'mutasi_asal' : 'mutasi_tujuan';

    let locations = @json(\App\Models\ArsipMutasiItem::getLocations());
    let locationOptions = '<option value="">-- Lokasi --</option>';
    locations.forEach(loc => {
        let selected = (loc === location) ? 'selected' : '';
        locationOptions += `<option value="${loc}" ${selected}>${loc}</option>`;
    });

    let html = `
        <tr>
            <td><input type="text" name="detail_barang[${key}][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode" value="${code}" required style="width: 80px;"></td>
            <td><input type="text" name="detail_barang[${key}][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Produk" value="${name}" required style="min-width: 150px;"></td>
            <td><input type="number" step="any" name="detail_barang[${key}][${idx}][qty]" class="form-control form-control-sm text-center border-0 bg-light" value="${qty}" style="width: 70px;"></td>
            <td><input type="text" name="detail_barang[${key}][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot" value="${lot}" style="width: 90px;"></td>
            <td><input type="text" name="detail_barang[${key}][${idx}][panjang]" class="form-control form-control-sm border-0 bg-light" placeholder="Pjg" value="${panjang}" style="width: 80px;"></td>
            <td>
                <select name="detail_barang[${key}][${idx}][location]" class="form-select form-select-sm border-0 bg-light" style="width: 150px;">
                    ${locationOptions}
                </select>
            </td>
            <td class="text-end pe-2"><button type="button" class="btn btn-link text-${color} p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>`;
    if(type === 'asal') $('#wrapperAsalEdit').append(html);
    else $('#wrapperTujuanEdit').append(html);
};

window.addBundelRowEdit = function(no_doc = '', qty = 1, ket = '') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    let html = `
        <tr>
            <td class="ps-3"><input type="text" name="detail_barang[bundel][${idx}][no_doc]" class="form-control form-control-sm border-0 bg-light" placeholder="No Dokumen" value="${no_doc}" required></td>
            <td><input type="number" step="any" name="detail_barang[bundel][${idx}][qty]" class="form-control form-control-sm text-center border-0 bg-light" value="${qty}" style="min-width: 80px;"></td>
            <td><input type="text" name="detail_barang[bundel][${idx}][keterangan]" class="form-control form-control-sm border-0 bg-light" placeholder="Ket..." value="${ket}"></td>
            <td class="text-end pe-2"><button type="button" class="btn btn-link text-info p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>`;
    $('#wrapperBundelEdit').append(html);
};

// FUNGSI UTAMA EDIT (AJAX CALL)
window.editArsip = function(id) {
    // 1. Reset Form Edit
    $('#formEditArsip')[0].reset();
    $('.dynamic-section-edit').addClass('d-none'); 
    $('#wrapperAdjustEdit, #wrapperAsalEdit, #wrapperTujuanEdit, #wrapperBundelEdit').empty(); 
    
    // 2. Set ID ke Hidden Input
    $('#editArsipId').val(id); 
    
    // 3. Ambil Data dari Server
    let urlShow = "{{ route('admin.arsip.edit', ':id') }}"; 
    urlShow = urlShow.replace(':id', id);

    $.ajax({
    url: urlShow,
    type: "GET",
    success: function(response) {
        let data = response.data;

        // Reset Tampilan
        $('#sectionNoTransEdit').addClass('d-none'); 
        $('#editWrapperKategori').addClass('d-none');
        $('#sectionBundelEdit').addClass('d-none');
        $('#sectionAdjustEdit').addClass('d-none');
        $('#sectionMutasiEdit').addClass('d-none');
        
        $('#editNoTransaksi').prop('required', false);

        // Update Action URL
        let urlUpdate = "{{ route('admin.arsip.update', ':id') }}";
        urlUpdate = urlUpdate.replace(':id', data.id);
        $('#formEditArsip').attr('action', urlUpdate);

        // Fill Inputs
        $('#editNoRegistrasi').val(data.no_registrasi);
        $('#editJenisPengajuan').val(data.jenis_pengajuan); 
        $('#editDepartment').val(data.department_id);
        $('#editUnit').val(data.unit_id);
        $('#editManager').val(data.manager_id);
        $('#editPemohon').val(data.pemohon);
        $('#editKeterangan').val(data.keterangan);
        
        // Link Bukti Scan
        // Link Bukti Scan
        if(data.bukti_scan) {
            $('#linkBuktiSaatIni').html(
                `<a href="/preview-file/${data.bukti_scan}" target="_blank" class="text-decoration-none fw-bold small">
                    <i class="bi bi-file-earmark-pdf text-danger"></i> Lihat File
                </a>`
            );
        } else {
            $('#linkBuktiSaatIni').text('Belum ada file.');
        }

        // Logic display based on Jenis
        let jenis = data.jenis_pengajuan;

        if(jenis === 'Cancel') {
            $('#sectionNoTransEdit').removeClass('d-none');
            $('#editWrapperKategori').removeClass('d-none');
            $('#editNoTransaksi').val(data.no_transaksi).prop('required', true);
            $('#editKategori').val(data.kategori); 
        } 
        else if(jenis === 'Bundel') {
            $('#sectionBundelEdit').removeClass('d-none');
            if(data.bundel_items) {
                data.bundel_items.forEach(item => {
                    addBundelRowEdit(item.no_doc, item.qty, item.keterangan); 
                });
            }
        }
        else if(jenis === 'Adjust') {
            $('#sectionAdjustEdit').removeClass('d-none');
            if(data.adjust_items) {
                data.adjust_items.forEach(item => {
                    let code = item.product_code || '';
                    let nama = item.product_name || item.no_doc || '';
                    let qty_in = item.qty_in || 0;
                    let qty_out = item.qty_out || 0;
                    let lot = item.lot || item.keterangan || '';
                    addAdjustRowEdit(code, nama, qty_in, qty_out, lot);
                });
            }
        }
        else if(jenis && jenis.includes('Mutasi')) {
            $('#sectionMutasiEdit').removeClass('d-none');
            if(data.mutasi_items) {
                data.mutasi_items.forEach(item => {
                    let type = (item.type === 'asal') ? 'asal' : 'tujuan';
                    let code = item.product_code || '';
                    let nama = item.product_name || item.no_doc || '';
                    let qty = item.qty || 0;
                    let lot = item.lot || item.keterangan || '';
                    let panjang = item.panjang || '';
                    let location = item.location || '';
                    addMutasiRowEdit(type, code, nama, qty, lot, panjang, location);
                });
            }
        }

        $('#modalEditArsip').modal('show');
    },
    error: function(xhr) {
        console.error("Error:", xhr);
        alert('Gagal mengambil data. Silakan coba lagi.');
    }
});
}
</script>
@endpush