<div class="modal fade" id="modalEditArsip" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">

            {{-- FORM: Dihandle AJAX di index.blade.php --}}
            <form id="formEditArsip" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="editArsipId">

                {{-- HEADER --}}
                <div class="modal-header bg-warning bg-gradient text-dark py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-50 rounded-circle p-2 me-3 shadow-sm text-dark">
                            <i class="bi bi-pencil-square fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Perbarui Pengajuan</h5>
                            <small class="text-dark opacity-75">Sesuaikan informasi detail dokumen Anda</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- BODY --}}
                <div class="modal-body bg-light p-4">
                    <div class="row g-4">
                        {{-- LEFT COLUMN: INFO UTAMA --}}
                        <div class="col-lg-4">
                            <h6 class="fw-bold text-warning mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Utama</h6>
                            
                            {{-- READ-ONLY INFO --}}
                            <div class="p-3 rounded-3 bg-white border border-warning border-opacity-25 shadow-sm mb-4">
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted text-uppercase mb-1">No Registrasi</label>
                                    <input type="text" id="editNoRegistrasi" class="form-control border-0 bg-light fw-bold text-primary font-monospace" readonly>
                                </div>
                                <div class="">
                                    <label class="small fw-bold text-muted text-uppercase mb-1">Jenis Transaksi</label>
                                    <select name="jenis_pengajuan" id="editJenisPengajuan" class="form-select border-0 bg-light fw-bold text-dark" style="pointer-events: none; -webkit-appearance: none;">
                                        <option value="Cancel">Cancel Transaksi</option>
                                        <option value="Adjust">Adjust Stock</option>
                                        <option value="Mutasi_Billet">Mutasi Billet</option>
                                        <option value="Mutasi_Produk">Mutasi Produk</option>
                                        <option value="Internal_Memo">Internal Memo</option>
                                        <option value="Bundel">Bundel Dokumen</option>
                                    </select>
                                </div>
                            </div>

                            {{-- EDITABLE FIELDS --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Departemen</label>
                                <select name="department_id" id="editDepartment" class="form-select border-0 shadow-sm bg-white" required>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">Unit</label>
                                    <select name="unit_id" id="editUnit" class="form-select border-0 shadow-sm bg-white" required>
                                        @foreach($units as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary text-uppercase">Manager</label>
                                    <select name="manager_id" id="editManager" class="form-select border-0 shadow-sm bg-white" required>
                                        @foreach($managers as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Penyusun / Pemohon</label>
                                <textarea name="pemohon" id="editPemohon" class="form-control border-0 shadow-sm bg-white" rows="2" placeholder="Nama-nama..."></textarea>
                            </div>

                            {{-- Kategori (Cancel Only) --}}
                            <div class="mb-3 d-none dynamic-section-edit" id="editWrapperKategori">
                                <div class="p-3 rounded-3 bg-danger bg-opacity-10 shadow-sm">
                                    <label class="small fw-bold text-danger text-uppercase mb-1">Alasan Pembatalan</label>
                                    <select name="kategori" id="editKategori" class="form-select form-select-sm border-danger-subtle fw-bold">
                                        <option value="Human">Human Error</option>
                                        <option value="System">System Error</option>
                                        <option value="None">Lainnya / None</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN: DETAIL & ITEMS --}}
                        <div class="col-lg-8">
                            <h6 class="fw-bold text-warning mb-3"><i class="bi bi-list-check me-2"></i>Daftar Dokumen & Item Terdata</h6>

                            {{-- NO TRANSAKSI (CANCEL) --}}
                            <div id="sectionNoTransEdit" class="d-none dynamic-section-edit mb-4">
                                <div class="card border-0 shadow-sm border-start border-4 border-danger">
                                    <div class="card-body">
                                        <label class="fw-bold text-danger small text-uppercase mb-2">No. Transaksi Dicancel</label>
                                        <textarea name="no_transaksi" id="editNoTransaksi" class="form-control bg-light border-0" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- ADJUST --}}
                            <div id="sectionAdjustEdit" class="d-none dynamic-section-edit mb-4">
                                <div class="card border-0 shadow-sm overflow-hidden border-start border-4 border-primary">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                        <span class="fw-bold text-primary small">ITEM ADJUSTMENT</span>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addAdjustRowEdit()">
                                            <i class="bi bi-plus"></i> Tambah
                                        </button>
                                    </div>
                                    <div class="card-body p-0 table-responsive">
                                        <table class="table table-sm table-striped mb-0 align-middle">
                                            <thead class="bg-light text-muted small">
                                                <tr>
                                                    <th class="ps-3" width="90">Kode</th>
                                                    <th>Item Produk</th>
                                                    <th width="80" class="text-center">IN</th>
                                                    <th width="80" class="text-center">OUT</th>
                                                    <th>Lot/Ket</th>
                                                    <th width="40"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="wrapperAdjustEdit"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- MUTASI --}}
                            <div id="sectionMutasiEdit" class="d-none dynamic-section-edit mb-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm border-start border-4 border-danger mb-2">
                                             <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                                <span class="fw-bold text-danger small">ASAL (OUT)</span>
                                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill" onclick="addMutasiRowEdit('asal')"><i class="bi bi-plus"></i></button>
                                            </div>
                                            <div class="card-body p-0 table-responsive">
                                                <table class="table table-sm mb-0 align-middle">
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
                                                    <tbody id="wrapperAsalEdit"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm border-start border-4 border-success">
                                             <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                                <span class="fw-bold text-success small">TUJUAN (IN)</span>
                                                <button type="button" class="btn btn-xs btn-outline-success rounded-pill" onclick="addMutasiRowEdit('tujuan')"><i class="bi bi-plus"></i></button>
                                            </div>
                                            <div class="card-body p-0 table-responsive">
                                                <table class="table table-sm mb-0 align-middle">
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
                                                    <tbody id="wrapperTujuanEdit"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BUNDEL --}}
                            <div id="sectionBundelEdit" class="d-none dynamic-section-edit mb-4">
                                 <div class="card border-0 shadow-sm border-start border-4 border-info">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                        <span class="fw-bold text-info small">DOKUMEN BUNDEL</span>
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="addBundelRowEdit()">
                                            <i class="bi bi-plus"></i> Tambah
                                        </button>
                                    </div>
                                    <div class="card-body p-0 table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="bg-light text-muted small">
                                                <tr>
                                                    <th class="ps-3">No Dokumen</th>
                                                    <th width="80">Qty</th>
                                                    <th>Keterangan</th>
                                                    <th width="40"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="wrapperBundelEdit"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- ATTACHMENTS & COMMON --}}
                             <div class="card border-0 shadow-sm bg-white mb-2">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted text-uppercase mb-1">Keterangan Tambahan</label>
                                        <textarea name="keterangan" id="editKeterangan" class="form-control bg-light border-0" rows="3" placeholder="Alasan atau catatan khusus..."></textarea>
                                    </div>
                                    
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light border border-dashed border-secondary border-opacity-25">
                                        <div>
                                            <label class="fw-bold small text-muted d-block text-uppercase mb-1">Bukti Scan (PDF)</label>
                                            <span id="linkBuktiSaatIni"></span>
                                        </div>
                                        <div class="w-50">
                                            <input type="file" name="bukti_scan" accept=".pdf" class="form-control form-control-sm border-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="modal-footer bg-white border-top-0 py-3 px-4">
                    <button type="button" class="btn btn-light text-muted fw-bold rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold rounded-pill px-5 shadow-sm text-dark">
                        <i class="bi bi-save-fill me-2 opacity-50"></i>SIMPAN PERUBAHAN
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>