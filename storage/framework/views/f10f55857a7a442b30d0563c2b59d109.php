
<div class="row g-4">
    
    <div class="col-lg-3">
        <h6 class="fw-bold text-warning mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h6>
        
        
        <input type="hidden" name="id" id="editArsipId">

        
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Pengaju (Admin)</label>
            <select name="user_id" id="editUserId" class="form-select bg-light border-0" required>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold text-info">No Registrasi</label>
            <input type="text" name="no_registrasi" id="editNoRegistrasi" class="form-control bg-white border-info border-opacity-50 text-dark fw-bold font-monospace" placeholder="Otomatis jika kosong...">
            <small class="text-muted" style="font-size: 0.65rem;">* Internal System ID.</small>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold text-primary" style="color: #6366f1 !important;">No Doc / No Arsip</label>
            <input type="text" name="no_doc" id="editNoDoc" class="form-control bg-white border-primary border-opacity-50 text-dark fw-bold" placeholder="Contoh: 1234/02/IT/2026">
            <small class="text-muted" style="font-size: 0.65rem;">* Nomor dokumen resmi/fisik.</small>
        </div>

        
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Tanggal Pengajuan</label>
            <input type="date" name="tgl_pengajuan" id="editTglPengajuan" class="form-control bg-light border-0" required>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Tanggal Arsip</label>
            <input type="date" name="tgl_arsip" id="editTglArsip" class="form-control bg-light border-0">
        </div>

        
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Jenis Pengajuan</label>
            <select name="jenis_pengajuan" id="editJenisPengajuan" class="form-select bg-white border-warning border-opacity-50 text-dark fw-bold" required>
                <option value="Cancel">Cancel</option>
                <option value="Adjust">Adjust</option>
                <option value="Mutasi_Billet">Mutasi Billet</option>
                <option value="Mutasi_Produk">Mutasi Produk</option>
                <option value="Bundel">Bundel</option>
                <option value="Internal_Memo">Internal Memo</option>
                <option value="Produk_Baru">Pengajuan Produk Baru</option>
            </select>
        </div>

        
        <div class="mb-3 d-none dynamic-section-edit" id="editWrapperKategori">
            <label class="form-label small fw-bold text-danger">Kategori Error</label>
            <select name="kategori" id="editKategori" class="form-select bg-white border-danger border-opacity-50 text-dark fw-bold">
                <option value="Human">Human Error</option>
                <option value="System">System Error</option>
                <option value="None">None</option>
            </select>
        </div>

        
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Departemen</label>
            <select name="department_id" id="editDepartment" class="form-select bg-light border-0" required>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($d->id); ?>"><?php echo e($d->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-12">
                <label class="form-label small fw-bold text-secondary">Unit</label>
                <select name="unit_id" id="editUnit" class="form-select bg-light border-0" required>
                    <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold text-secondary">Manager</label>
                <select name="manager_id" id="editManager" class="form-select bg-light border-0" required>
                    <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m->id); ?>"><?php echo e($m->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Nama Pemohon</label>
            <textarea name="pemohon" id="editPemohon" class="form-control bg-light border-0" rows="2" placeholder="Nama-nama pemohon..."></textarea>
        </div>

        
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
                <div class="col-12">
                    <label class="form-label text-xs fw-bold text-muted mb-1">Berita Acara (BA)</label>
                    <select name="ba" id="editBa" class="form-select form-select-sm bg-white border-warning border-opacity-25">
                        <option value="Process">Process</option>
                        <option value="Done">Done (Ada)</option>
                        <option value="Void">Void</option>
                        <option value="None">None (Tidak Ada)</option>
                    </select>
                </div>
                <div class="col-12">
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
        
        
        <div id="auditTrailEdit" class="p-3 rounded-4 bg-white border shadow-sm d-none">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-clock-history text-info"></i>
                <h6 class="small fw-bold text-dark mb-0">Audit Terakhir</h6>
            </div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div id="auditEditorAvatar" class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.7rem; font-weight: bold;"></div>
                <span id="auditEditorName" class="fw-bold text-dark" style="font-size: 0.75rem;"></span>
            </div>
            <small id="auditUpdatedAt" class="text-muted d-block ps-4" style="font-size: 0.65rem;"></small>
        </div>
    </div>

    
    <div class="col-lg-9">
        <h6 class="fw-bold text-warning mb-3"><i class="bi bi-list-check me-2"></i>Detail Pengajuan</h6>

        
        <div class="mb-4 dynamic-section-edit" id="sectionNoTransEdit">
            <label class="form-label small fw-bold text-secondary">No. Transaksi / Referensi</label>
            <textarea name="no_transaksi" id="editNoTransaksi" class="form-control bg-light border-0" rows="3" style="min-height: 120px;"></textarea>
        </div>

        
        <div class="mb-4 d-none dynamic-section-edit" id="sectionAdjustExtraEdit" style="border:1px solid rgba(2,132,199,0.15); border-radius: 12px; padding: 12px 12px; background: rgba(14,165,233,0.03);">
            <div class="mt-2">
                <label class="form-label small fw-bold text-secondary">Deskripsi Masalah</label>
                <textarea name="keterangan" id="editKeterangan" class="form-control bg-light border-0" rows="4" style="min-height: 140px;"></textarea>
            </div>

            <small class="text-muted" style="font-size: 0.65rem;">Deskripsi masalah akan muncul sebagai CATATAN pada output draft.</small>
        </div>

         
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
                            <th class="text-center" width="70">Odoo</th>
                            <th class="text-center" width="70">Fisik</th>
                            <th class="text-center" width="80">Qty In</th>
                            <th class="text-center" width="80">Qty Out</th>
                            <th width="110">Lot</th>
                            <th width="160">Lokasi</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="wrapperAdjustEdit"></tbody>
                </table>
            </div>
        </div>

        
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

        
        <div class="mb-4 d-none dynamic-section-edit" id="sectionProdukBaruEdit">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label small fw-bold text-primary mb-0"><i class="bi bi-box-seam me-1"></i> Pengajuan Produk Baru</label>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="window.addProdukBaruRowEdit()">
                    <i class="bi bi-plus-lg me-1"></i> Tambah
                </button>
            </div>
            <div class="table-responsive rounded-3 border border-light">
                <table class="table table-sm table-borderless mb-0 align-middle">
                    <thead class="bg-light text-secondary">
                        <tr class="text-xs">
                            <th class="ps-3" width="100">Kode</th>
                            <th>Nama Produk</th>
                            <th width="110">Tipe</th>
                            <th width="180">Kategori</th>
                            <th width="100">Satuan</th>
                            <th width="120">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="wrapperProdukBaruEdit"></tbody>
                </table>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Keterangan</label>
            <textarea name="keterangan" id="editKeterangan" class="form-control bg-light border-0" rows="2"></textarea>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold text-primary">Tindakan IT</label>
                <textarea name="tindakan" id="editTindakan" class="form-control bg-light border-0" rows="2" placeholder="Tindakan yang diambil IT..."></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-primary">Catatan IT</label>
                <textarea name="catatan_it" id="editCatatanIt" class="form-control bg-light border-0" rows="2" placeholder="Catatan internal IT..."></textarea>
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label small fw-bold text-primary mb-0">Tindakan IT (per baris)</label>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="window.addTindakanItRowEdit()">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Baris
                </button>
            </div>

            <div class="table-responsive rounded-3 border border-light">
                <table class="table table-sm table-borderless mb-0 align-middle">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="text-center" width="22%">IN</th>
                            <th>KETERANGAN IN</th>
                            <th class="text-center" width="22%">OUT</th>
                            <th>KETERANGAN OUT</th>
                            <th width="36"></th>
                        </tr>
                    </thead>
                    <tbody id="wrapperTindakanItEdit"></tbody>
                </table>
            </div>

            <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">
                Isi baris IN/OUT dan keterangannya. Tersimpan ke tabel relasi `arsip_tindakan_items`.
            </small>
        </div>


        
        <div class="mb-3 p-3 rounded-3" style="background:#ecfdf5; border:1px solid #6ee7b7;">
            <label class="form-label small fw-bold text-success d-block mb-1">
                <i class="bi bi-shield-check me-1"></i> Scan Final (Eksekusi Tim IT)
            </label>
            <input type="file" name="scan_final" class="form-control bg-white border-0 mb-1" accept=".pdf">
            <div id="linkScanFinal" class="mt-1 text-xs"></div>
            <small class="text-muted d-block mt-1" style="font-size:0.65rem;">PDF maks 10MB. File final yang sudah lengkap (BA + tanda tangan) untuk arsip resmi IT.</small>
        </div>

        
        <div class="mb-3 p-3 rounded-3" style="background:#eef2ff; border:1px solid #c7d2fe;">
            <h6 class="fw-bold text-primary mb-2" style="font-size:0.85rem;"><i class="bi bi-diagram-3-fill me-1"></i>Alur Persetujuan</h6>
            <div id="editApprovalTimeline" class="mb-3"></div>
            <div id="editApprovalNote" class="alert alert-warning border-0 small d-none mb-2"></div>
            <div id="editApproverWrap">
                <?php echo $__env->make('partials._approver_select', ['approverUsers' => $approverUsers ?? collect(), 'jenisSelectId' => 'editJenisPengajuan'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

    </div>
</div><?php /**PATH C:\laragon\www\e_arsip\resources\views\superadmin\arsip\_edit.blade.php ENDPATH**/ ?>