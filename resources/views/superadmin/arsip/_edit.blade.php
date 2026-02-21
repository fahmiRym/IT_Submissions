{{-- NOTE: This view is allocated inside the modal-body of #modalEditArsip in index.blade.php --}}
<div class="row g-4">
    {{-- LEFT COLUMN: INFO UTAMA --}}
    <div class="col-lg-4">
        <h6 class="fw-bold text-warning mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h6>
        
        {{-- HIDDEN ID --}}
        <input type="hidden" name="id" id="editArsipId">

        {{-- 1. PILIH USER & NO REGISTRASI --}}
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Pengaju (Admin)</label>
            <select name="user_id" id="editUserId" class="form-select bg-light border-0" required>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold text-info">No Registrasi</label>
            <input type="text" name="no_registrasi" id="editNoRegistrasi" class="form-control bg-white border-info border-opacity-50 text-dark fw-bold font-monospace" placeholder="Otomatis jika kosong...">
            <small class="text-muted" style="font-size: 0.65rem;">* Edit nomor registrasi jika diperlukan.</small>
        </div>

        {{-- 2. TANGGAL --}}
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Tanggal Pengajuan</label>
            <input type="date" name="tgl_pengajuan" id="editTglPengajuan" class="form-control bg-light border-0" required>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Tanggal Arsip</label>
            <input type="date" name="tgl_arsip" id="editTglArsip" class="form-control bg-light border-0">
        </div>

        {{-- 3. JENIS PENGAJUAN --}}
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Jenis Pengajuan</label>
            <select name="jenis_pengajuan" id="editJenisPengajuan" class="form-select bg-white border-warning border-opacity-50 text-dark fw-bold" required>
                <option value="Cancel">Cancel</option>
                <option value="Adjust">Adjust</option>
                <option value="Mutasi_Billet">Mutasi Billet</option>
                <option value="Mutasi_Produk">Mutasi Produk</option>
                <option value="Bundel">Bundel</option>
                <option value="Internal_Memo">Internal Memo</option>
            </select>
        </div>

        {{-- 4. KATEGORI (Dynamic Show/Hide handled by JS) --}}
        <div class="mb-3 d-none dynamic-section-edit" id="editWrapperKategori">
            <label class="form-label small fw-bold text-danger">Kategori Error</label>
            <select name="kategori" id="editKategori" class="form-select bg-white border-danger border-opacity-50 text-dark fw-bold">
                <option value="Human">Human Error</option>
                <option value="System">System Error</option>
                <option value="None">None</option>
            </select>
        </div>

        {{-- 5. DEPARTEMEN & UNIT --}}
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Departemen</label>
            <select name="department_id" id="editDepartment" class="form-select bg-light border-0" required>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label small fw-bold text-secondary">Unit</label>
                <select name="unit_id" id="editUnit" class="form-select bg-light border-0" required>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <label class="form-label small fw-bold text-secondary">Manager</label>
                <select name="manager_id" id="editManager" class="form-select bg-light border-0" required>
                    @foreach($managers as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Nama Pemohon</label>
            <textarea name="pemohon" id="editPemohon" class="form-control bg-light border-0" rows="2" placeholder="Nama-nama pemohon..."></textarea>
        </div>

        {{-- STATUS CONTROLS (Superadmin Special) --}}
        <div class="p-3 rounded-3 bg-gradient bg-light border border-warning border-opacity-25 mb-3 shadow-sm">
            <div class="d-flex align-items-center mb-2 pb-2 border-bottom border-warning border-opacity-25">
                <i class="bi bi-shield-lock text-warning me-2"></i>
                <h6 class="small fw-bold text-secondary mb-0">Status & Verifikasi</h6>
            </div>
            
            <div class="mb-2">
                <label class="form-label text-xs fw-bold text-muted mb-1">Status Utama (Flow)</label>
                <select name="status" id="editStatus" class="form-select form-select-sm bg-white border-warning border-opacity-25 text-dark fw-bold">
                    <option value="Check">Check (Verifikasi Awal)</option>
                    <option value="Process">Process (Sedang Diproses)</option>
                    <option value="Pending">Pending (Ditunda/Revisi)</option>
                    <option value="Done">Done (Selesai)</option>
                    <option value="Reject">Reject (Ditolak)</option>
                    <option value="Void">Void (Dibatalkan)</option>
                </select>
            </div>

            <div class="row g-2">
                <div class="col-12">
                     <label class="form-label text-xs fw-bold text-muted mb-1">Ket. Proses Pengerjaan</label>
                     <select name="ket_process" id="editKetProcess" class="form-select form-select-sm bg-white border-warning border-opacity-25 text-primary fw-semibold">
                        <option value="Review">Review (Sedang Diulas)</option>
                        <option value="Process">Process (Dikerjakan)</option>
                        <option value="Pending">Pending (Tertunda)</option>
                        <option value="Partial Done">Partial Done (Sebagian)</option>
                        <option value="Done">Done (Selesai)</option>
                        <option value="Void">Void (Batal)</option>
                     </select>
                </div>
                <div class="col-6">
                    <label class="form-label text-xs fw-bold text-muted mb-1">Berita Acara (BA)</label>
                    <select name="ba" id="editBa" class="form-select form-select-sm bg-white border-warning border-opacity-25">
                        <option value="Process">Process</option>
                        <option value="Done">Done (Ada)</option>
                        <option value="Void">Void</option>
                        <option value="None">None (Tidak Ada)</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label text-xs fw-bold text-muted mb-1">Fisik Arsip</label>
                    <select name="arsip" id="editArsipStatus" class="form-select form-select-sm bg-white border-warning border-opacity-25">
                        <option value="Pending">Pending (Belum)</option>
                        <option value="Process">Process</option>
                        <option value="Done">Done (Disimpan)</option>
                        <option value="None">None</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN: DETAIL & ITEMS --}}
    <div class="col-lg-8">
        <h6 class="fw-bold text-warning mb-3"><i class="bi bi-list-check me-2"></i>Detail Pengajuan</h6>

        {{-- A. NO TRANSAKSI (Cancel/Memo) --}}
        <div class="mb-4 dynamic-section-edit" id="sectionNoTransEdit">
            <label class="form-label small fw-bold text-secondary">No. Transaksi / Referensi</label>
            <textarea name="no_transaksi" id="editNoTransaksi" class="form-control bg-light border-0" rows="3"></textarea>
        </div>

         {{-- B. BUNDEL SECTION --}}
         <div class="mb-4 d-none dynamic-section-edit" id="sectionBundelEdit">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label small fw-bold text-secondary mb-0">Daftar Dokumen</label>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="window.addBundelRowEdit()">
                    <i class="bi bi-plus-lg me-1"></i> Tambah
                </button>
            </div>
            <div class="table-responsive rounded-3 border border-light">
                <table class="table table-sm table-borderless mb-0 align-middle">
                    <tbody id="wrapperBundelEdit"></tbody>
                </table>
            </div>
        </div>

        {{-- C. ADJUST SECTION --}}
        <div class="mb-4 d-none dynamic-section-edit" id="sectionAdjustEdit">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label small fw-bold text-secondary mb-0">Adjust Items</label>
                <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3" onclick="window.addAdjustRowEdit()">
                    <i class="bi bi-plus-lg me-1"></i> Tambah
                </button>
            </div>
            <div class="table-responsive rounded-3 border border-light">
                <table class="table table-sm table-borderless mb-0 align-middle">
                    <thead class="bg-light text-secondary">
                        <tr class="text-xs">
                            <th class="ps-3">Kode</th>
                            <th>Nama</th>
                            <th class="text-center">In</th>
                            <th class="text-center">Out</th>
                            <th>Lot</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="wrapperAdjustEdit"></tbody>
                </table>
            </div>
        </div>

        {{-- D. MUTASI SECTION --}}
        <div class="mb-4 d-none dynamic-section-edit" id="sectionMutasiEdit">
            <div class="row g-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-danger mb-0">DARI (Out)</label>
                        <button type="button" class="btn btn-sm btn-danger bg-opacity-10 text-danger rounded-pill px-3 border-0" onclick="window.addMutasiRowEdit('asal')">
                            Add Source
                        </button>
                    </div>
                    <div class="table-responsive rounded-3 border border-danger border-opacity-25 bg-danger bg-opacity-10 p-2">
                        <table class="table table-sm table-borderless mb-0 align-middle">
                            <thead class="text-xs text-danger fw-bold">
                                <tr>
                                    <th width="90">Kode</th>
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
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-success mb-0">KE (In)</label>
                        <button type="button" class="btn btn-sm btn-success bg-opacity-10 text-success rounded-pill px-3 border-0" onclick="window.addMutasiRowEdit('tujuan')">
                            Add Target
                        </button>
                    </div>
                    <div class="table-responsive rounded-3 border border-success border-opacity-25 bg-success bg-opacity-10 p-2">
                        <table class="table table-sm table-borderless mb-0 align-middle">
                            <thead class="text-xs text-success fw-bold">
                                <tr>
                                    <th width="90">Kode</th>
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

        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Keterangan</label>
            <textarea name="keterangan" id="editKeterangan" class="form-control bg-light border-0" rows="2"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Bukti Scan</label>
            <input type="file" name="bukti_scan" class="form-control bg-light border-0 mb-1" accept=".pdf,.jpg,.jpeg,.png">
            <div id="linkBuktiSaatIni" class="mt-1 ps-1"></div>
        </div>

    </div>
</div>