@extends('layouts.app')

@section('title', 'Master Data Produk')
@section('page-title', 'Manajemen Produk')

@section('content')
<div class="row g-4">
    {{-- FORM TAMBAH (SIDEBAR LEFT) --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px;">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Tambah Produk Baru</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.products.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">KODE PRODUK</label>
                        <input type="text" name="code" class="form-control bg-light border-0 py-2 rounded-3" placeholder="Contoh: PRD-001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">NAMA PRODUK</label>
                        <input type="text" name="name" class="form-control bg-light border-0 py-2 rounded-3" placeholder="Nama lengkap produk..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">KATEGORI</label>
                        <input type="text" name="category" class="form-control bg-light border-0 py-2 rounded-3" placeholder="Elektronik, Sparepart, dll...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">SATUAN</label>
                        <input type="text" name="unit" class="form-control bg-light border-0 py-2 rounded-3" placeholder="PCS, Unit, Box, dll...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">KETERANGAN</label>
                        <textarea name="description" class="form-control bg-light border-0 rounded-3" rows="3" placeholder="Deskripsi opsional..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                        <i class="bi bi-save2-fill me-2"></i>SIMPAN PRODUK
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- TABEL LIST (RIGHT) --}}
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-collection-fill text-primary me-2"></i>Daftar Master Produk</h6>
                <form action="{{ route('superadmin.products.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm bg-light border-0 rounded-pill px-3" placeholder="Cari nama/kode...">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr style="font-size: 0.75rem;">
                            <th class="ps-4 text-muted fw-bold">PRODUK</th>
                            <th class="text-muted fw-bold">KATEGORI</th>
                            <th class="text-muted fw-bold">SATUAN</th>
                            <th class="text-center text-muted fw-bold">STATUS</th>
                            <th class="text-end pe-4 text-muted fw-bold">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-box-seam-fill"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $p->name }}</div>
                                        <div class="font-monospace text-muted" style="font-size: 0.7rem;">{{ $p->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $p->category ?? '-' }}</span></td>
                            <td><span class="fw-semibold text-secondary">{{ $p->unit ?? '-' }}</span></td>
                            <td class="text-center">
                                <form action="{{ route('superadmin.products.toggle', $p->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm border-0 bg-transparent">
                                        @if($p->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1">
                                                <i class="bi bi-x-circle-fill me-1"></i>Non-Aktif
                                            </span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center mx-auto" style="width: 32px; height: 32px;" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                        <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#editModal{{ $p->id }}"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Produk</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('superadmin.products.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash3-fill me-2"></i>Hapus Permanen</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>

                                {{-- MODAL EDIT --}}
                                <div class="modal fade" id="editModal{{ $p->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4 shadow">
                                            <div class="modal-header border-0 pb-0 pt-4 px-4">
                                                <h5 class="modal-title fw-bold">Edit Produk</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('superadmin.products.update', $p->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body p-4 text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">KODE PRODUK</label>
                                                        <input type="text" name="code" class="form-control bg-light border-0 py-2 rounded-3" value="{{ $p->code }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">NAMA PRODUK</label>
                                                        <input type="text" name="name" class="form-control bg-light border-0 py-2 rounded-3" value="{{ $p->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">KATEGORI</label>
                                                        <input type="text" name="category" class="form-control bg-light border-0 py-2 rounded-3" value="{{ $p->category }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">SATUAN</label>
                                                        <input type="text" name="unit" class="form-control bg-light border-0 py-2 rounded-3" value="{{ $p->unit }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">KETERANGAN</label>
                                                        <textarea name="description" class="form-control bg-light border-0 rounded-3" rows="3">{{ $p->description }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                                                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="opacity-25 mb-3"><i class="bi bi-box-seam-fill display-1"></i></div>
                                <div class="text-muted">Belum ada produk terdaftar.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
