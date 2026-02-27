<div class="modal fade" id="modalTambahArsip" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
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
                            <small class="text-white-50">Lengkapi formulir pengajuan dokumen</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- BODY --}}
                <div class="modal-body bg-light p-4">
                    <div class="row g-4">
                        {{-- LEFT COLUMN: INFORMASI DASAR --}}
                        <div class="col-lg-4">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Utama</h6>
                            
                            {{-- 1. JENIS PENGAJUAN --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Jenis Transaksi</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-primary text-white border-0"><i class="bi bi-tags-fill"></i></span>
                                    <select name="jenis_pengajuan" id="jenisPengajuanTambahAdmin" class="form-select border-0 fw-bold" required>
                                        <option value="" disabled selected>-- Pilih Jenis --</option>
                                        <option value="Mutasi_Produk">Mutasi Produk</option>
                                        <option value="Mutasi_Billet">Mutasi Billet</option>
                                        <option value="Adjust">Adjustment</option>
                                        <option value="Bundel">Bundel</option>
                                        <option value="Cancel">Cancel</option>
                                        <option value="Internal_Memo">Internal Memo</option>
                                    </select>
                                </div>
                            </div>

                            {{-- PREVIEW FORMAT (Like Superadmin) --}}
                            <div class="border rounded-3 p-3 bg-white mb-3 shadow-sm border-primary border-opacity-10">
                                <div class="fw-bold small mb-2 text-primary">📄 Contoh Isi No Transaksi Cancel</div>
                                <pre class="mb-0 text-dark" style="font-size: 0.65rem;">
MO/R-ANO/26/01/00069
INT/ET/2638201

MO/R-ANO/25/12/18863
INK/AN/251557638
INK/PK/2944428

SJF/25/12/0135

BPB-25/12/0327
                                </pre>
                            </div>

                            {{-- 2. TANGGAL (New Field) --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Tanggal Pengajuan</label>
                                <input type="date" name="tgl_pengajuan" value="{{ date('Y-m-d') }}" class="form-control bg-white border-0 shadow-sm" required>
                                <small class="text-muted" style="font-size: 0.7rem;">Sistem akan mencatat jam saat ini otomatis.</small>
                            </div>

                            {{-- 3. DEPARTEMEN & UNIT --}}
                            <div class="mb-2">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Departemen</label>
                                <select name="department_id" class="form-select bg-white border-0 shadow-sm" required>
                                    <option value="">Pilih Departemen...</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option> 
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">Unit</label>
                                    <select name="unit_id" class="form-select bg-white border-0 shadow-sm" required>
                                        <option value="">Pilih...</option>
                                        @foreach($units as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">Manager</label>
                                    <select name="manager_id" class="form-select bg-white border-0 shadow-sm" required>
                                        <option value="">Pilih...</option>
                                        @foreach($managers as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Nama Pemohon</label>
                                <textarea name="pemohon" class="form-control bg-white border-0 shadow-sm" placeholder="Nama-nama Pemohon..." rows="2"></textarea>
                            </div>

                             {{-- Kategori Error (Muncul jika Cancel) --}}
                             <div class="mb-3 d-none" id="wrapperKategori">
                                <div class="p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25">
                                    <label class="small fw-bold text-danger mb-1 text-uppercase">Penyebab Cancel Transaksi</label>
                                    <select name="kategori" class="form-select form-select-sm border-danger text-dark fw-bold">
                                        <option value="Human">Human Error</option>
                                        <option value="System">System Error</option>
                                        <option value="None">Lainnya / None</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN: DETAIL & ITEMS --}}
                        <div class="col-lg-8">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-list-check me-2"></i>Detail Dokumen & Item</h6>

                            {{-- SECTION NO TRANSAKSI (CANCEL) --}}
                            <div id="sectionNoTrans" class="d-none dynamic-section mb-4">
                                <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                                    <div class="card-body">
                                        <label class="fw-bold text-danger small text-uppercase mb-2">No. Transaksi / Referensi (Beri Enter per Transaksi)</label>
                                        <textarea name="no_transaksi" class="form-control border-light bg-light" placeholder="Contoh:&#10;MO/26/02/0001&#10;DC/26/02/0100" style="height: 120px"></textarea>
                                    </div>
                                </div>
                            </div>

                             {{-- SECTION ADJUST --}}
                            <div id="sectionAdjust" class="d-none dynamic-section mb-4">
                                <div class="card border-0 shadow-sm overflow-hidden border-start border-4 border-info">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <span class="fw-bold text-info"><i class="bi bi-sliders me-1"></i> ITEM ADJUSTMENT</span>
                                            <span id="badgeCountAdjust" class="badge bg-light border text-secondary ms-2 d-none" style="font-size:0.65rem;"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3" id="btnAddAdjust">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah Item
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped mb-0 align-middle">
                                                <thead class="bg-light text-muted small text-uppercase">
                                                    <tr>
                                                        <th class="ps-3" width="110">Kode</th>
                                                        <th>Item Produk</th>
                                                        <th width="80" class="text-center">IN</th>
                                                        <th width="80" class="text-center">OUT</th>
                                                        <th>Lot/Ket</th>
                                                        <th width="40"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="wrapperAdjust"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION MUTASI --}}
                            <div id="sectionMutasi" class="d-none dynamic-section mb-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm border-start border-4 border-danger">
                                            <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="fw-bold text-danger small"><i class="bi bi-box-arrow-right me-1"></i> ASAL (OUT)</span>
                                                    <span id="badgeCountAsal" class="badge bg-light border text-secondary ms-2 d-none" style="font-size:0.65rem;"></span>
                                                </div>
                                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill flex-shrink-0" id="btnAddAsal"><i class="bi bi-plus"></i></button>
                                            </div>
                                            <div class="card-body p-0 table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="bg-light text-muted small">
                                                        <tr>
                                                            <th width="90" class="ps-2">Kode</th>
                                                            <th>Nama Produk</th>
                                                            <th width="70" class="text-center">Qty</th>
                                                            <th width="100">Lot</th>
                                                            <th width="90">PJG</th>
                                                            <th width="160">Lokasi</th>
                                                            <th width="40"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="wrapperAsal"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm border-start border-4 border-success">
                                            <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="fw-bold text-success small"><i class="bi bi-box-arrow-in-left me-1"></i> TUJUAN (IN)</span>
                                                    <span id="badgeCountTujuan" class="badge bg-light border text-secondary ms-2 d-none" style="font-size:0.65rem;"></span>
                                                </div>
                                                <button type="button" class="btn btn-xs btn-outline-success rounded-pill flex-shrink-0" id="btnAddTujuan"><i class="bi bi-plus"></i></button>
                                            </div>
                                            <div class="card-body p-0 table-responsive">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="bg-light text-muted small">
                                                        <tr>
                                                            <th width="90" class="ps-2">Kode</th>
                                                            <th>Nama Produk</th>
                                                            <th width="70" class="text-center">Qty</th>
                                                            <th width="100">Lot</th>
                                                            <th width="90">Pjg</th>
                                                            <th width="160">Lokasi</th>
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
                            <div id="sectionBundel" class="d-none dynamic-section mb-4">
                                <div class="card border-0 shadow-sm border-start border-4 border-info">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <span class="fw-bold text-info small"><i class="bi bi-files me-1"></i> BUNDEL DOKUMEN</span>
                                            <span id="badgeCountBundel" class="badge bg-light border text-secondary ms-2 d-none" style="font-size:0.65rem;"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" id="btnAddBundel">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah
                                        </button>
                                    </div>
                                    <div class="card-body p-0 table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="bg-light text-muted small">
                                                <tr>
                                                    <th class="ps-3">No Dokumen / Item</th>
                                                    <th class="text-center" width="120">Qty</th>
                                                    <th>Keterangan</th>
                                                    <th width="40"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="wrapperBundel"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- COMMON FIELDS --}}
                            <div class="card border-0 shadow-sm bg-white mb-3">
                                <div class="card-body">
                                    <div class="form-floating mb-3">
                                        <textarea name="keterangan" class="form-control border-light" placeholder="Keterangan" style="height: 80px"></textarea>
                                        <label class="text-muted"><i class="bi bi-chat-text me-1"></i> Keterangan / Alasan Pengajuan</label>
                                    </div>
                                    
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Upload Bukti Scan (Opsional jika ingin Draft)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-file-earmark-pdf text-danger"></i></span>
                                        <input type="file" name="bukti_scan" accept=".pdf" class="form-control border-white bg-light shadow-none">
                                    </div>
                                    <div class="form-text small opacity-75 mt-1 fst-italic">Format: PDF (Max 5MB)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="modal-footer bg-white border-top-0 py-3 px-4">
                    <button type="button" class="btn btn-light text-muted fw-bold rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-pill px-5 shadow-sm overflow-hidden position-relative">
                        <span class="position-relative" style="z-index: 2;">
                            <i class="bi bi-send-fill me-2 text-white-50"></i>KIRIM PENGAJUAN
                        </span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>