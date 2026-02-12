<div class="modal fade" id="modalTambahArsip" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            
            <form method="POST" action="{{ route('admin.arsip.store') }}" enctype="multipart/form-data" id="formTambahArsip">
                @csrf

                {{-- HEADER --}}
                <div class="modal-header bg-primary bg-gradient text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="bi bi-folder-plus fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Buat Pengajuan Baru</h5>
                            <small class="text-white-50">Isi formulir di bawah dengan lengkap</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- BODY --}}
                <div class="modal-body bg-light">
                    
                    {{-- 1. PILIH JENIS PENGAJUAN (DRIVER) --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="fw-bold small text-primary mb-1">LANGKAH 1: Pilih Jenis Transaksi</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white"><i class="bi bi-tags-fill"></i></span>
                                        <select name="jenis_pengajuan" id="jenisPengajuanTambahAdmin" class="form-select form-select-lg fw-bold" required>
                                            <option value="" disabled selected>-- Pilih Jenis Pengajuan --</option>
                                            <option value="Mutasi_Produk">Mutasi Produk</option>
                                            <option value="Mutasi_Billet">Mutasi Billet</option>
                                            <option value="Adjust">Adjustment</option>
                                            <option value="Bundel">Bundel</option>
                                            <option value="Cancel">Cancel</option>
                                            <option value="Internal_Memo">Internal Memo</option>
                                        </select>
                                    </div>
                                </div>
                                
                                {{-- Kategori Error (Muncul jika Cancel) --}}
                                <div class="col-md-12 d-none" id="wrapperKategori">
                                    <div class="alert alert-danger d-flex align-items-center p-2 mb-0" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                                        <div class="w-100">
                                            <label class="small fw-bold">Penyebab Cancel:</label>
                                            <select name="kategori" class="form-select form-select-sm border-danger text-dark fw-bold">
                                                <option value="Human">Human Error</option>
                                                <option value="System">System Error</option>
                                                <option value="None">Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. INFORMASI DASAR --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="card-title fw-bold text-muted small mb-3 border-bottom pb-2">INFORMASI PEMOHON</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select name="department_id" class="form-select bg-white border border-secondary border-opacity-25" id="deptSelect" required>
                                            <option value="">Pilih...</option>
                                            @foreach($departments as $d)
                                                <option value="{{ $d->id }}">{{ $d->name }}</option> 
                                            @endforeach
                                        </select>
                                        <label for="deptSelect">Departemen</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select name="unit_id" class="form-select bg-white border border-secondary border-opacity-25" id="unitSelect" required>
                                            <option value="">Pilih...</option>
                                            @foreach($units as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="unitSelect">Unit / Bagian</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select name="manager_id" class="form-select bg-white border border-secondary border-opacity-25" id="mgrSelect" required>
                                            <option value="">Pilih...</option>
                                            @foreach($managers as $m)
                                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="mgrSelect">Manager Approval</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="form-floating">
                                        <textarea name="pemohon" class="form-control border-0 bg-light" placeholder="Nama-nama Pemohon" style="height: 60px"></textarea>
                                        <label class="small fw-bold text-muted text-uppercase">Nama-nama Pemohon (Gunakan baris baru)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. DYNAMIC SECTIONS --}}
                    
                    {{-- SECTION ADJUST --}}
                    <div id="sectionAdjust" class="d-none dynamic-section mb-3">
                        <div class="card border-0 shadow-sm overflow-hidden">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <span class="fw-bold small"><i class="bi bi-sliders me-1"></i> ITEM ADJUSTMENT</span>
                                <button type="button" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3" id="btnAddAdjust">
                                    <i class="bi bi-plus-circle"></i> Tambah Item
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0 align-middle">
                                        <thead class="table-light text-muted small text-uppercase">
                                            <tr>
                                                <th class="ps-3" width="120">Kode</th>
                                                <th>Item Produk</th>
                                                <th width="100" class="text-center">Qty In</th>
                                                <th width="100" class="text-center">Qty Out</th>
                                                <th>Lot/Ket</th>
                                                <th width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="wrapperAdjust"></tbody>
                                    </table>
                                </div>
                                <div class="p-3 text-center text-muted small fst-italic" id="emptyAdjustMsg">
                                    Belum ada item ditambahkan.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION MUTASI --}}
                    <div id="sectionMutasi" class="d-none dynamic-section mb-3">
                        <div class="row g-3">
                            {{-- ASAL --}}
                            <div class="col-12">
                                <div class="card border-0 shadow-sm border-start border-4 border-danger">
                                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between">
                                        <span class="fw-bold text-danger"><i class="bi bi-box-arrow-right me-1"></i> BARANG KELUAR (ASAL)</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" id="btnAddAsal"><i class="bi bi-plus"></i> Tambah</button>
                                    </div>
                                    <div class="card-body p-0 table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3" width="120">Kode</th>
                                                    <th>Nama Produk</th>
                                                    <th width="80">Qty</th>
                                                    <th>Lot</th>
                                                    <th width="80">Pjg</th>
                                                    <th width="150">Lokasi</th>
                                                    <th width="40"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="wrapperAsal"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- TUJUAN --}}
                            <div class="col-12">
                                <div class="card border-0 shadow-sm border-start border-4 border-success">
                                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between">
                                        <span class="fw-bold text-success"><i class="bi bi-box-arrow-in-left me-1"></i> BARANG MASUK (TUJUAN)</span>
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill" id="btnAddTujuan"><i class="bi bi-plus"></i> Tambah</button>
                                    </div>
                                    <div class="card-body p-0 table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3" width="120">Kode</th>
                                                    <th>Nama Produk</th>
                                                    <th width="80">Qty</th>
                                                    <th>Lot</th>
                                                    <th width="80">Pjg</th>
                                                    <th width="150">Lokasi</th>
                                                    <th width="40"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="wrapperTujuan"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION BUNDEL --}}
                    <div id="sectionBundel" class="d-none dynamic-section mb-3">
                         <div class="card border-0 shadow-sm border-start border-4 border-info">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold text-info"><i class="bi bi-files me-1"></i> DOKUMEN BUNDEL</span>
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" id="btnAddBundel">
                                    <i class="bi bi-plus-circle"></i> Tambah Dokumen
                                </button>
                            </div>
                            <div class="card-body p-0 table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">No Dokumen / Item</th>
                                            <th width="80">Qty</th>
                                            <th>Keterangan</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="wrapperBundel"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION NO TRANSAKSI (CANCEL) --}}
                    <div id="sectionNoTrans" class="d-none dynamic-section mb-3">
                        <div class="card border-0 shadow-sm bg-white bg-opacity-20 border-danger border-start border-4">
                            <div class="card-body">
                                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-x-circle me-1"></i> PEMBATALAN TRANSAKSI</h6>
                                <div class="form-floating">
                                    <textarea name="no_transaksi" class="form-control border-danger" placeholder="No Transaksi" style="height: 80px"></textarea>
                                    <label>Masukkan No Transaksi yang dibatalkan ( Beri Enter untuk membedakan No Transaksi)
                                        <br>Contoh:
                                        <br>MO/26/02/0001
                                        <br>INK/PR/54654
                                        <br>(Enter)
                                        <br>INT/215464
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. KETERANGAN & UPLOAD --}}
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <textarea name="keterangan" class="form-control border-0 shadow-sm" placeholder="Keterangan" style="height: 100px"></textarea>
                                <label><i class="bi bi-chat-text me-1"></i> Keterangan / Alasan Pengajuan</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm bg-white">
                                <div class="card-body">
                                    <label class="form-label fw-bold small text-muted">UPLOAD BUKTI SCAN <span class="text-danger">*</span></label>
                                    <input type="file" name="bukti_scan" class="form-control" required>
                                    <div class="form-text small text-muted"><i class="bi bi-info-circle me-1"></i> Format: PDF (Max 5MB)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="modal-footer bg-white border-top-0 py-3">
                    <button type="button" class="btn btn-light text-muted fw-bold rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-pill px-5 shadow-sm">
                        <i class="bi bi-send-fill me-2"></i>Kirim Pengajuan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>