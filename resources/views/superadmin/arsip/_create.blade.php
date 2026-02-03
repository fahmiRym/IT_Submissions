<div class="modal fade" id="modalTambahArsip" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            
            {{-- HEADER --}}
            <div class="modal-header bg-gradient-primary text-white border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-folder-plus fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Buat Pengajuan Baru</h5>
                        <small class="text-white-50">Isi formulir di bawah untuk membuat arsip baru (Mode Superadmin)</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('superadmin.arsip.store') }}" method="POST" enctype="multipart/form-data" id="formTambahArsip">
                @csrf
                <div class="modal-body p-4 bg-light">
                    
                    {{-- ALERT ERROR --}}
                    @if($errors->any())
                        <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
                            <ul class="mb-0 small">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-4">
                        
                        {{-- LEFT COLUMN: INFO UTAMA --}}
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h6>
                                    
                                    {{-- 1. PILIH USER (Superadmin Only) --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">Pilih Pengaju (Admin) <span class="text-danger">*</span></label>
                                        <select name="user_id" class="form-select bg-light border-0" required>
                                            <option value="">-- Pilih Admin --</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 2. TANGGAL PENGAJUAN (Superadmin Only) --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">Tanggal Pengajuan <span class="text-danger">*</span></label>
                                        <input type="date" name="tgl_pengajuan" class="form-control bg-light border-0" value="{{ old('tgl_pengajuan', date('Y-m-d')) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">Tanggal Arsip (Opsional)</label>
                                        <input type="date" name="tgl_arsip" class="form-control bg-light border-0" value="{{ old('tgl_arsip') }}">
                                        <div class="form-text text-xs">Diisi jika sudah selesai/diarsipkan.</div>
                                    </div>

                                    {{-- 3. JENIS PENGAJUAN --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">Jenis Pengajuan <span class="text-danger">*</span></label>
                                        <select name="jenis_pengajuan" id="jenisPengajuanTambah" class="form-select bg-primary bg-opacity-10 border-primary border-opacity-25 text-primary fw-bold" required>
                                            <option value="">-- Pilih Jenis --</option>
                                            <option value="Cancel">Cancel (Pembatalan)</option>
                                            <option value="Adjust">Adjust (Penyesuaian Stok)</option>
                                            <option value="Mutasi_Billet">Mutasi Billet</option>
                                            <option value="Mutasi_Produk">Mutasi Produk</option>
                                            <option value="Bundel">Bundel Dokumen</option>
                                            <option value="Internal_Memo">Internal Memo</option>
                                        </select>
                                    </div>

                                    {{-- 4. KATEGORI (Cancel Only) - HIDDEN DEFAULT --}}
                                    <div class="mb-3 d-none dynamic-section" id="wrapperKategori">
                                        <label class="form-label small fw-bold text-danger">Kategori Error <span class="text-danger">*</span></label>
                                        <select name="kategori" class="form-select bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger">
                                            <option value="">-- Pilih Kategori --</option>
                                            <option value="Human">Human Error</option>
                                            <option value="System">System Error</option>
                                            <option value="None">None</option>
                                        </select>
                                    </div>

                                    {{-- 5. LOKASI / DEPT --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">Departemen <span class="text-danger">*</span></label>
                                        <select name="department_id" class="form-select bg-light border-0" required>
                                            @foreach($departments as $d)
                                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-secondary">Unit <span class="text-danger">*</span></label>
                                            <select name="unit_id" class="form-select bg-light border-0" required>
                                                @foreach($units as $u)
                                                    <option value="{{ $u->id }}" {{ old('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-secondary">Manager <span class="text-danger">*</span></label>
                                            <select name="manager_id" class="form-select bg-light border-0" required>
                                                @foreach($managers as $m)
                                                    <option value="{{ $m->id }}" {{ old('manager_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    {{-- STATUS FINAL (Superadmin Only) --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">Status Awal</label>
                                        <select name="status" class="form-select bg-warning bg-opacity-10 border-warning border-opacity-25 text-dark fw-bold">
                                            <option value="Check">Check (Baru)</option>
                                            <option value="Process">Process</option>
                                            <option value="Done">Done (Selesai)</option>
                                            <option value="Reject">Reject</option>
                                            <option value="Void">Void</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN: DETAIL BARANG / FILE --}}
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-list-check me-2"></i>Detail Pengajuan</h6>

                                    {{-- A. NO TRANSAKSI (Utk Cancel, Memo) --}}
                                    <div class="mb-4 dynamic-section" id="sectionNoTrans">
                                        <label class="form-label small fw-bold text-secondary">No. Transaksi / Dokumen Referensi <span class="text-danger">*</span></label>
                                        <textarea name="no_transaksi" class="form-control bg-light border-0" rows="3" placeholder="Contoh: BPB-2301-001 (Satu per baris)"></textarea>
                                        <div class="form-text text-xs"><i class="bi bi-exclamation-circle me-1"></i> Masukkan nomor dokumen yang akan diproses.</div>
                                    </div>

                                    {{-- B. BUNDEL SECTION --}}
                                    <div class="mb-4 d-none dynamic-section" id="sectionBundel">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label small fw-bold text-secondary mb-0">Daftar Dokumen dalam Bundel</label>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btnAddBundel">
                                                <i class="bi bi-plus-lg me-1"></i> Tambah
                                            </button>
                                        </div>
                                        <div class="table-responsive rounded-3 border border-light">
                                            <table class="table table-sm table-borderless mb-0 align-middle">
                                                <tbody id="wrapperBundel" class="dynamic-row-container">
                                                    {{-- JS generated rows --}}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- C. ADJUST SECTION --}}
                                    <div class="mb-4 d-none dynamic-section" id="sectionAdjust">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label small fw-bold text-secondary mb-0">Item Adjustment</label>
                                            <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3" id="btnAddAdjust">
                                                <i class="bi bi-plus-lg me-1"></i> Tambah Item
                                            </button>
                                        </div>
                                        <div class="alert alert-info py-2 small border-0"><i class="bi bi-info-circle me-1"></i> Masukkan Qty In atau Out sesuai kebutuhan penyesuaian.</div>
                                        <div class="table-responsive rounded-3 border border-light">
                                            <table class="table table-sm table-borderless mb-0 align-middle">
                                                <thead class="bg-light text-secondary">
                                                    <tr class="text-xs">
                                                        <th class="ps-3" width="15%">Kode</th>
                                                        <th>Nama Barang</th>
                                                        <th width="10%" class="text-center">In</th>
                                                        <th width="10%" class="text-center">Out</th>
                                                        <th width="15%">Lot</th>
                                                        <th width="5%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="wrapperAdjust" class="dynamic-row-container">
                                                    {{-- JS generated rows --}}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- D. MUTASI SECTION --}}
                                    <div class="mb-4 d-none dynamic-section" id="sectionMutasi">
                                        <div class="row g-3">
                                            {{-- ASAL --}}
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label class="form-label small fw-bold text-danger mb-0"><i class="bi bi-box-arrow-up me-1"></i>DARI (Sumber/Out)</label>
                                                    <button type="button" class="btn btn-sm btn-danger bg-opacity-10 text-danger rounded-pill px-3 border-0" id="btnAddAsal">
                                                        <i class="bi bi-plus-lg me-1"></i> Add Source
                                                    </button>
                                                </div>
                                                <div class="table-responsive rounded-3 border border-danger border-opacity-25 bg-danger bg-opacity-10 p-2">
                                                    <table class="table table-sm table-borderless mb-0 align-middle">
                                                        <tbody id="wrapperAsal" class="dynamic-row-container"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            {{-- TUJUAN --}}
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label class="form-label small fw-bold text-success mb-0"><i class="bi bi-box-arrow-in-down me-1"></i>KE (Tujuan/In)</label>
                                                    <button type="button" class="btn btn-sm btn-success bg-opacity-10 text-success rounded-pill px-3 border-0" id="btnAddTujuan">
                                                        <i class="bi bi-plus-lg me-1"></i> Add Target
                                                    </button>
                                                </div>
                                                <div class="table-responsive rounded-3 border border-success border-opacity-25 bg-success bg-opacity-10 p-2">
                                                    <table class="table table-sm table-borderless mb-0 align-middle">
                                                        <tbody id="wrapperTujuan" class="dynamic-row-container"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- DESKRIPSI & UPLOAD FILE --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">Keterangan / Alasan</label>
                                        <textarea name="keterangan" class="form-control bg-light border-0" rows="2" placeholder="Jelaskan alasan pengajuan ini..."></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">Upload Bukti (Scan/Foto) <span class="text-danger">*</span></label>
                                        <input type="file" name="bukti_scan" class="form-control bg-light border-0" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <div class="form-text text-xs">Maks 2MB. Format: PDF, JPG, PNG.</div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-white border-top border-light py-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i>Buat Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
