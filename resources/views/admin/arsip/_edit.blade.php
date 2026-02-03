<div class="modal fade" id="modalEditArsip" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">

            {{-- FORM: Tanpa Action, Tanpa @method PUT (Dihandle JS) --}}
            <form id="formEditArsip" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- [PERBAIKAN 1] Tambahkan name="id" --}}
                <input type="hidden" name="id" id="editArsipId">

                {{-- HEADER --}}
                <div class="modal-header bg-warning bg-gradient text-dark py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-50 rounded-circle p-2 me-3">
                            <i class="bi bi-pencil-square fs-4 text-dark"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Perbarui Data Arsip</h5>
                            <small class="text-dark opacity-75">Edit informasi dan detail dokumen</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- BODY --}}
                <div class="modal-body bg-light">
                    
                    {{-- INFO READ-ONLY --}}
                    <div class="card border-0 shadow-sm mb-3 bg-white">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="small fw-bold text-muted text-uppercase">No Registrasi</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light"><i class="bi bi-hash"></i></span>
                                        {{-- [PERBAIKAN 2] Tambahkan name="no_registrasi" --}}
                                        <input type="text" name="no_registrasi" id="editNoRegistrasi" class="form-control border-0 bg-light fw-bold text-primary" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold text-muted text-uppercase">Jenis Pengajuan</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light"><i class="bi bi-tag-fill"></i></span>
                                        <select name="jenis_pengajuan" id="editJenisPengajuan" class="form-control border-0 bg-light fw-bold text-dark" style="pointer-events: none; -webkit-appearance: none; background-color: #f8f9fa;">
                                            <option value="Cancel">Cancel Transaksi</option>
                                            <option value="Adjust">Adjust Stock</option>
                                            <option value="Mutasi_Billet">Mutasi Billet</option>
                                            <option value="Mutasi_Produk">Mutasi Produk</option>
                                            <option value="Internal_Memo">Internal Memo</option>
                                            <option value="Bundel">Bundel Dokumen</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- INFORMASI UTAMA --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="card-title fw-bold text-muted small mb-3 border-bottom pb-2">INFORMASI DASAR</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select name="department_id" id="editDepartment" class="form-select border-0 bg-light">
                                            @foreach($departments as $d)
                                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                                            @endforeach
                                        </select>
                                        <label>Departemen</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select name="unit_id" id="editUnit" class="form-select border-0 bg-light">
                                            @foreach($units as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                        <label>Unit / Bagian</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select name="manager_id" id="editManager" class="form-select border-0 bg-light">
                                            @foreach($managers as $m)
                                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                            @endforeach
                                        </select>
                                        <label>Manager</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-2 d-none" id="editWrapperKategori">
                                    <div class="form-floating">
                                        <select name="kategori" id="editKategori" class="form-select border-danger text-danger bg-white">
                                            <option value="Human">Human Error</option>
                                            <option value="System">System Error</option>
                                            <option value="None">Lainnya</option>
                                        </select>
                                        <label class="text-danger fw-bold">Penyebab Cancel</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DYNAMIC SECTIONS (Logic JS populates this) --}}
                    {{-- Pastikan logic JS Anda mengisi bagian ini dengan benar --}}
                    <div id="sectionAdjustEdit" class="d-none dynamic-section-edit mb-3">
                        <div class="card border-0 shadow-sm">
                             <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <span class="fw-bold small">ITEM ADJUSTMENT</span>
                                <button type="button" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3" onclick="addAdjustRowEdit()">
                                    <i class="bi bi-plus-circle"></i> Tambah
                                </button>
                            </div>
                            <div class="card-body p-0 table-responsive">
                                <table class="table table-sm table-striped mb-0 align-middle">
                                    <thead class="table-light text-muted small">
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

                    <div id="sectionMutasiEdit" class="d-none dynamic-section-edit mb-3">
                        {{-- Wrapper Asal & Tujuan (Sama seperti kode Anda) --}}
                        <div class="card border-0 shadow-sm border-start border-4 border-danger mb-2">
                             <div class="card-header bg-white pt-2 pb-1 border-bottom-0 d-flex justify-content-between">
                                <span class="fw-bold text-danger small">ASAL (KELUAR)</span>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill py-0" onclick="addMutasiRowEdit('asal')"><i class="bi bi-plus"></i></button>
                            </div>
                            <div class="card-body p-0 table-responsive">
                                <table class="table table-sm mb-0 align-middle">
                                    <thead class="table-light text-muted small">
                                        <tr>
                                            <th class="ps-3" width="90">Kode</th>
                                            <th>Item Produk</th>
                                            <th width="70">Qty</th>
                                            <th>Lot</th>
                                            <th width="60">Pjg</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="wrapperAsalEdit"></tbody>
                                </table>
                            </div>
                        </div>
                         <div class="card border-0 shadow-sm border-start border-4 border-success">
                             <div class="card-header bg-white pt-2 pb-1 border-bottom-0 d-flex justify-content-between">
                                <span class="fw-bold text-success small">TUJUAN (MASUK)</span>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill py-0" onclick="addMutasiRowEdit('tujuan')"><i class="bi bi-plus"></i></button>
                            </div>
                            <div class="card-body p-0 table-responsive">
                                <table class="table table-sm mb-0 align-middle">
                                    <thead class="table-light text-muted small">
                                        <tr>
                                            <th class="ps-3" width="90">Kode</th>
                                            <th>Item Produk</th>
                                            <th width="70">Qty</th>
                                            <th>Lot</th>
                                            <th width="60">Pjg</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="wrapperTujuanEdit"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="sectionBundelEdit" class="d-none dynamic-section-edit mb-3">
                         <div class="card border-0 shadow-sm border-start border-4 border-info">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold text-info small">DOKUMEN BUNDEL</span>
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="addBundelRowEdit()">
                                    <i class="bi bi-plus-circle"></i> Tambah
                                </button>
                            </div>
                            <div class="card-body p-0 table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
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

                    <div id="sectionNoTransEdit" class="d-none dynamic-section-edit mb-3">
                        <div class="card border-0 shadow-sm bg-danger bg-opacity-10 border-danger border-start border-4">
                            <div class="card-body">
                                <h6 class="fw-bold text-danger mb-2 small">NO TRANSAKSI DICANCEL</h6>
                                <textarea name="no_transaksi" id="editNoTransaksi" class="form-control border-danger" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- KETERANGAN & FILE --}}
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-floating">
                                <textarea name="keterangan" id="editKeterangan" class="form-control border-0 shadow-sm" style="height: 100px"></textarea>
                                <label>Keterangan / Alasan</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm bg-white">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <label class="fw-bold small text-muted d-block">GANTI BUKTI SCAN</label>
                                        <small class="text-info fst-italic" id="linkBuktiSaatIni"></small>
                                    </div>
                                    <div class="w-50">
                                        <input type="file" name="bukti_scan" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="modal-footer bg-white border-top-0 py-3">
                    <button type="button" class="btn btn-light text-muted fw-bold rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning fw-bold rounded-pill px-5 shadow-sm">
                        <i class="bi bi-save-fill me-2"></i>Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>