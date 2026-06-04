@extends('layouts.app')

@section('title', 'Persetujuan Saya')
@section('page-title', 'Persetujuan Saya')

@section('content')
@php $rp = auth()->user()->role === 'superadmin' ? 'superadmin' : 'admin'; @endphp

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3 d-flex align-items-center gap-2">
        <i class="bi bi-inbox-fill fs-4 text-primary"></i>
        <div>
            <h6 class="fw-bold mb-0">Menunggu Persetujuan Anda</h6>
            <small class="text-muted">{{ $arsips->count() }} pengajuan perlu Anda tindak (setujui = tanda tangan digital).</small>
        </div>
    </div>
</div>

@if(session('success')) <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div> @endif

@forelse($arsips as $a)
    @php $cur = $a->currentApproval(); @endphp
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace">{{ $a->no_registrasi }}</span>
                        <span class="badge bg-light text-dark border">{{ str_replace('_',' ',$a->jenis_pengajuan) }}</span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Tahap Anda: {{ $cur->role_label ?? '-' }}</span>
                    </div>
                    <div class="small text-muted mb-1">
                        Pengaju: <span class="fw-bold text-dark">{{ $a->admin->name ?? '-' }}</span> ·
                        {{ $a->department->name ?? '-' }} / {{ $a->unit->name ?? '-' }} ·
                        {{ optional($a->tgl_pengajuan)->format('d/m/Y') }}
                    </div>
                    @if($a->keterangan)
                        <div class="small text-muted fst-italic">"{{ \Illuminate\Support\Str::limit($a->keterangan, 120) }}"</div>
                    @endif
                    <div class="mt-3">
                        @include('partials._approval_timeline', ['arsip' => $a])
                    </div>
                </div>
                <div class="col-lg-5 d-flex flex-column justify-content-center">
                    @if(!auth()->user()->hasSignature())
                        <div class="alert alert-warning border-0 small mb-2">
                            <i class="bi bi-exclamation-triangle me-1"></i>Atur specimen tanda tangan di
                            <a href="{{ route($rp.'.profile') }}" class="fw-bold">Profil</a> sebelum menyetujui.
                        </div>
                    @endif
                    <div class="d-flex gap-2">
                        <form action="{{ route($rp.'.arsip.approve', $a->id) }}" method="POST" class="flex-fill"
                              onsubmit="return confirm('Setujui & tanda tangani tahap {{ $cur->role_label ?? '' }}?')">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold shadow-sm" {{ auth()->user()->hasSignature() ? '' : 'disabled' }}>
                                <i class="bi bi-check2-circle me-1"></i>Setujui & TTD
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger rounded-pill fw-bold" data-bs-toggle="collapse" data-bs-target="#reject{{ $a->id }}">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="collapse mt-2" id="reject{{ $a->id }}">
                        <form action="{{ route($rp.'.arsip.reject', $a->id) }}" method="POST"
                              onsubmit="return confirm('Tolak pengajuan ini?')">
                            @csrf
                            <textarea name="note" class="form-control form-control-sm mb-2" rows="2" placeholder="Alasan penolakan (opsional)"></textarea>
                            <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold">Konfirmasi Tolak</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5">
            <i class="bi bi-check2-all fs-1 text-success opacity-50"></i>
            <h6 class="fw-bold mt-3">Tidak ada yang menunggu</h6>
            <p class="text-muted small mb-0">Semua pengajuan untuk Anda sudah ditindak.</p>
        </div>
    </div>
@endforelse
@endsection
