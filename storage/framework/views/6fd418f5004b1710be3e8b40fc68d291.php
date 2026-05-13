

<?php $__env->startSection('title', 'Pengajuan'); ?>
<?php $__env->startSection('page-title', '📁 Pengajuan Saya'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f4f7fa;
            color: #0f172a;
        }

        /* Global Typography & Clarity - High Contrast */
        .text-secondary {
            color: #334155 !important;
            font-weight: 600;
        }

        .text-muted {
            color: #64748b !important;
        }

        .small,
        small {
            font-size: 0.8rem;
            font-weight: 600;
        }

        .fw-extrabold {
            font-weight: 800 !important;
        }

        /* Responsive Table Utility */
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

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
            background-color: #f8fafc;
            box-shadow: inset 0 0 12px rgba(0,0,0,0.02);
            transition: all 0.2s ease;
        }

        .hover-warning:hover {
            background: #fffbeb !important;
            border-color: #f59e0b !important;
        }

        .btn-upload-ba:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4) !important;
        }

        /* Status Badge Enhancements */
        .status-badge {
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            padding: 6px 12px;
            border-radius: 99px;
            text-transform: uppercase;
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
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(255, 255, 255, 0.1) 0px, transparent 50%);
            z-index: 1;
        }

        .pattern-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
            transition: border-color 0.2s;
        }

        .item-detail-card:hover {
            border-color: #cbd5e1;
        }

        .note-detail-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 5px solid #0ea5e9 !important;
            border-radius: 14px;
            padding: 16px 20px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .text-primary-dark {
            color: #0f172a !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

    
    <div class="card border-0 shadow-sm mb-4 animate-on-scroll" style="border-radius: 12px;">
        <div class="card-body p-4">
            <form method="GET" class="row g-3">
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small fw-bold text-secondary mb-1">🔍 Pencarian</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="form-control bg-light border-0 px-3"
                            placeholder="No Dok, Transaksi..." style="border-radius: 0 8px 8px 0;">
                    </div>
                </div>

                <div class="col-6 col-lg-2">
                    <label class="form-label small fw-bold text-secondary mb-1">📅 Dari</label>
                    <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>"
                        class="form-control bg-light border-0 px-3" style="border-radius: 8px;">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label small fw-bold text-secondary mb-1">📅 Sampai</label>
                    <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>"
                        class="form-control bg-light border-0 px-3" style="border-radius: 8px;">
                </div>

                <div class="col-6 col-lg-2">
                    <label class="form-label small fw-bold text-secondary mb-1">📄 Jenis</label>
                    <select name="jenis_pengajuan" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                        <option value="">Semua Jenis</option>
                        <?php $__currentLoopData = ['Cancel', 'Adjust', 'Mutasi_Billet', 'Mutasi_Produk', 'Internal_Memo', 'Bundel']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($jp); ?>" <?php echo e((request('jenis_pengajuan') == $jp || request('jenis') == $jp) ? 'selected' : ''); ?>><?php echo e(str_replace('_', ' ', $jp)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-6 col-lg-3">
                    <label class="form-label small fw-bold text-secondary mb-1">⚙️ Status</label>
                    <select name="ket_process" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                        <option value="">Semua Status</option>
                        <?php $__currentLoopData = ['Review', 'Process', 'Done', 'Pending', 'Void']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($st); ?>" <?php echo e(request('ket_process') == $st ? 'selected' : ''); ?>><?php echo e($st); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small fw-bold text-secondary mb-1">🏢 Departemen</label>
                    <select name="department_id" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($d->id); ?>" <?php echo e(request('department_id') == $d->id ? 'selected' : ''); ?>><?php echo e($d->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small fw-bold text-secondary mb-1">📦 Unit</label>
                    <select name="unit_id" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($u->id); ?>" <?php echo e(request('unit_id') == $u->id ? 'selected' : ''); ?>><?php echo e($u->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small fw-bold text-secondary mb-1">👤 Manager</label>
                    <select name="manager_id" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->id); ?>" <?php echo e(request('manager_id') == $m->id ? 'selected' : ''); ?>><?php echo e($m->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-6 col-lg-1">
                    <label class="form-label small fw-bold text-secondary mb-1">🏷️ Kat</label>
                    <select name="kategori" class="form-select bg-light border-0 px-3" style="border-radius: 8px;">
                        <option value="">Semua</option>
                        <option value="Human" <?php echo e(request('kategori') == 'Human' ? 'selected' : ''); ?>>Human</option>
                        <option value="System" <?php echo e(request('kategori') == 'System' ? 'selected' : ''); ?>>System</option>
                    </select>
                </div>

                <div class="col-6 col-lg-2 d-flex gap-2 align-items-end">
                    <button type="submit"
                        class="btn btn-primary fw-bold shadow-sm flex-fill d-flex align-items-center justify-content-center"
                        style="background: #4f46e5; border-color: #4f46e5; border-radius: 10px; height: 38px;">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                    <a href="<?php echo e(route('admin.arsip.index')); ?>"
                        class="btn btn-white border bg-white shadow-sm d-flex align-items-center justify-content-center"
                        style="border-radius: 10px; width: 42px; height: 38px;" title="Reset">
                        <i class="bi bi-arrow-counterclockwise text-secondary fw-bold"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <?php
        $fJenis = request('jenis') ?? request('jenis_pengajuan');
        $sConfig = [
            'title' => 'SUBMISI SAYA',
            'icon' => 'bi-grid-fill',
            'color' => '#21d1a5ff',
            'bg' => 'linear-gradient(135deg, #16dac9ff 0%, #13cde6ff 100%)',
        ];
        if ($fJenis == 'Cancel') {
            $sConfig = ['title' => 'TOTAL CANCEL', 'icon' => 'bi-x-octagon-fill', 'color' => '#ef4444', 'bg' => 'linear-gradient(135deg, #ef4444 0%, #f87171 100%)'];
        } elseif ($fJenis == 'Adjust') {
            $sConfig = ['title' => 'TOTAL ADJUST', 'icon' => 'bi-sliders', 'color' => '#0ea5e9', 'bg' => 'linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%)'];
        }
    ?>

    <?php
        $tinyStats = [
            ['label' => 'REVIEW', 'key' => 'Review', 'icon' => 'bi-clock-history', 'color' => '#3b82f6'],
            ['label' => 'ON PROCESS', 'key' => 'Process', 'icon' => 'bi-hourglass-split', 'color' => '#f59e0b'],
            ['label' => 'PENDING', 'key' => 'Pending', 'icon' => 'bi-pause-circle-fill', 'color' => '#64748b'],
            ['label' => 'DONE', 'key' => 'Done', 'icon' => 'bi-check-circle-fill', 'color' => '#10b981'],
            ['label' => 'VOID / REJECT', 'key' => 'Void', 'icon' => 'bi-slash-circle-fill', 'color' => '#ef4444'],
        ];
    ?>

    <div class="row g-3 mb-4 animate-on-scroll">
        
        <div class="col-12 col-xl-3 col-lg-4">
            <a href="<?php echo e(request()->fullUrlWithQuery(['ket_process' => null])); ?>" class="text-decoration-none">
                <div class="card border-0 stat-card-main text-white h-100 p-1"
                    style="background: <?php echo e($sConfig['bg']); ?>; min-height: 140px;">
                    <div class="mesh-gradient"></div>
                    <div class="pattern-overlay"></div>

                    <div class="card-body p-4 d-flex align-items-center gap-4 position-relative" style="z-index: 2;">
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                            style="width: 64px; height: 64px; backdrop-filter: blur(4px);">
                            <i class="bi <?php echo e($sConfig['icon']); ?> fs-1 text-white"></i>
                        </div>
                        <div>
                            <h1 class="fw-extrabold mb-0 lh-1" style="font-size: 2.5rem; letter-spacing: -2px;">
                                <?php echo e(number_format($stats['total'] ?? 0)); ?></h1>
                            <p class="mb-0 small fw-extrabold opacity-75 mt-2 letter-spacing-1 text-uppercase text-white"
                                style="font-size: 0.72rem;"><?php echo e($sConfig['title']); ?></p>
                        </div>
                        <div class="position-absolute top-0 end-0 p-3">
                            <div class="glass-badge">ACTIVE</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-xl-9 col-lg-8">
            <div class="row g-2 g-md-3 h-100">
                <?php $__currentLoopData = $tinyStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-6 col-sm-4 col-md">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['ket_process' => $ts['key']])); ?>"
                            class="text-decoration-none h-100">
                            <div class="card mini-stat-card border-0 shadow-sm h-100" style="background: <?php echo e($ts['color']); ?>08;">
                                <div
                                    class="card-body p-2 p-md-3 text-center d-flex flex-column align-items-center justify-content-center">
                                    <div class="bg-white shadow-xs rounded-circle d-flex align-items-center justify-content-center mb-2"
                                        style="width: 36px; height: 36px; @media(min-width: 768px){ width: 44px; height: 44px; }">
                                        <i class="bi <?php echo e($ts['icon']); ?>" style="font-size: 1rem; color: <?php echo e($ts['color']); ?>;"></i>
                                    </div>
                                    <h5 class="fw-extrabold text-primary-dark mb-0 lh-1">
                                        <?php echo e(number_format($stats[$ts['key']] ?? 0)); ?></h5>
                                    <div class="text-secondary fw-extrabold text-uppercase mt-2"
                                        style="font-size: 0.5rem; letter-spacing: 1px; opacity: 0.8;"><?php echo e($ts['label']); ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold text-dark mb-1"><i class="bi bi-table me-2 text-primary"></i>Daftar Pengajuan</h5>
            <div class="text-muted small">
                Showing <span class="fw-bold"><?php echo e($arsips->firstItem() ?? 0); ?></span> - <span
                    class="fw-bold"><?php echo e($arsips->lastItem() ?? 0); ?></span> of <span
                    class="fw-bold"><?php echo e($arsips->total()); ?></span> data
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 w-100 w-md-auto">
            <div
                class="d-flex align-items-center bg-white rounded-pill px-3 py-1 shadow-sm border flex-grow-1 flex-md-grow-0">
                <small class="text-secondary fw-bold me-2" style="font-size: 0.75rem;">SHOW:</small>
                <select id="perPageSelect"
                    class="form-select form-select-sm border-0 bg-transparent fw-bold text-primary py-0 ps-0 pe-4"
                    style="width: auto; cursor: pointer; box-shadow: none;">
                    <?php $__currentLoopData = [10, 25, 50, 100, 250, 500, 1000]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($size); ?>" <?php echo e(request('per_page') == $size ? 'selected' : ''); ?>><?php echo e($size); ?> Rows</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <button
                class="btn btn-primary rounded-pill shadow px-4 py-2 fw-extrabold d-flex align-items-center justify-content-center flex-grow-1 flex-md-grow-0"
                data-bs-toggle="modal" data-bs-target="#modalTambahArsip"
                style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border: none;">
                <i class="bi bi-plus-circle-fill me-2 fs-5"></i>BUAT BARU
            </button>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm animate-on-scroll" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 d-none d-sm-table-cell" style="width: 50px;">#</th>
                            <th style="min-width: 180px;">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'no_registrasi', 'dir' => (request('sort') == 'no_registrasi' && request('dir') == 'asc') ? 'desc' : 'asc'])); ?>"
                                    class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    <span class="text-uppercase fw-bold">No. Reg / Transaksi</span>
                                </a>
                            </th>
                            <th class="d-none d-md-table-cell" style="width: 150px;">
                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'tgl_pengajuan', 'dir' => (request('sort') == 'tgl_pengajuan' && request('dir') == 'asc') ? 'desc' : 'asc'])); ?>"
                                    class="text-secondary text-decoration-none d-flex align-items-center gap-2">
                                    <span class="text-uppercase fw-bold">Tgl</span>
                                </a>
                            </th>
                            <th class="d-none d-lg-table-cell" style="width: 200px;"><span
                                    class="text-uppercase fw-bold">Unit</span></th>
                            <th class="text-center d-none d-sm-table-cell" style="width: 80px;"><span
                                    class="text-uppercase fw-bold">QTY</span></th>
                            <th class="d-none d-sm-table-cell" style="min-width: 250px;"><span
                                    class="text-uppercase fw-bold">Detail Dokumen</span></th>
                            <th class="text-center" style="width: 120px;"><span class="text-uppercase fw-bold">Status</span>
                            </th>
                            <th class="text-end pe-4" style="width: 100px;"><span class="text-uppercase fw-bold">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $arsips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="transition-hover" style="font-size: 0.85rem; border-bottom: 1px solid #f1f5f9;">
                                <td class="ps-4 text-center fw-bold text-muted d-none d-sm-table-cell">
                                    <?php echo e(($arsips->currentPage() - 1) * $arsips->perPage() + $loop->iteration); ?>

                                </td>

                                <td class="ps-3 py-3">
                                    <div class="d-flex flex-column gap-1">
                                        
                                        <?php if($a->no_registrasi): ?>
                                            <div class="fw-bold font-monospace text-primary mb-0"
                                                style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                                <?php echo e($a->no_registrasi); ?>

                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if($a->no_transaksi): ?>
                                            <div class="text-secondary fw-semibold font-monospace mt-n1" style="font-size: 0.68rem; opacity: 0.8;">
                                                <?php echo e($a->no_transaksi); ?>

                                            </div>
                                        <?php endif; ?>

                                        <div class="d-md-none mt-1 small text-muted">
                                            <i class="bi bi-calendar-event me-1"></i><?php echo e(optional($a->tgl_pengajuan)->format('d/m/y')); ?>

                                        </div>
                                    </div>
                                </td>

                                <td class="d-none d-md-table-cell py-3">
                                    <div class="text-dark fw-bold" style="font-size: 0.85rem;">
                                        <?php echo e(optional($a->tgl_pengajuan)->format('d M Y')); ?>

                                    </div>
                                    <div class="text-muted fw-bold mt-1" style="font-size: 0.7rem;">
                                        <?php echo e(optional($a->tgl_pengajuan)->format('H:i')); ?> WIB
                                    </div>

                                    <?php if($a->updated_by): ?>
                                        <div class="mt-2 pt-1 border-top border-light border-opacity-50">
                                            <small class="text-muted d-block" style="font-size: 0.55rem; font-weight: 700;">EDITED BY:</small>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="fw-bold text-secondary" style="font-size: 0.65rem;"><?php echo e($a->editor->name ?? 'System'); ?></span>
                                            </div>
                                            <small class="text-secondary opacity-75" style="font-size: 0.6rem;">
                                                <?php echo e($a->updated_at->format('d/m/y H:i')); ?>

                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="d-none d-lg-table-cell">
                                    <div class="fw-bold text-dark lh-sm mb-1 text-truncate"
                                        style="max-width: 140px; font-size: 0.82rem;"><?php echo e($a->department->name ?? '-'); ?></div>
                                    <span class="text-secondary px-1 rounded small"
                                        style="font-size: 0.65rem; border: 1px solid #cbd5e1;"><?php echo e($a->unit->name ?? '-'); ?></span>
                                </td>

                                <td class="text-center d-none d-sm-table-cell py-3">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <?php if($a->total_qty_in > 0): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 w-100"
                                                style="font-size: 0.68rem; min-width: 65px;">+<?php echo e(number_format($a->total_qty_in, 2)); ?></span>
                                        <?php endif; ?>
                                        <?php if($a->total_qty_out > 0): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 w-100"
                                                style="font-size: 0.68rem; min-width: 65px;">-<?php echo e(number_format($a->total_qty_out, 2)); ?></span>
                                        <?php endif; ?>
                                        <?php if($a->total_qty_in == 0 && $a->total_qty_out == 0): ?>
                                            <span class="text-muted small">0</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="ps-2 d-none d-sm-table-cell">
                                    <div class="d-flex flex-column gap-1" style="max-width: 250px;">
                                        <?php $itemsFound = false; ?>
                                        
                                        <?php if($a->adjustItems && $a->adjustItems->count() > 0): ?>
                                            <?php $itemsFound = true; ?>
                                            <?php $__currentLoopData = $a->adjustItems->take(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="text-truncate fw-bold text-dark" style="font-size: 0.7rem;">
                                                    <i class="bi bi-dot"></i> <?php echo e($item->product_code); ?>

                                                    (<?php echo e((int) ($item->qty_in + $item->qty_out)); ?>)
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                        <?php if($a->mutasiItems && $a->mutasiItems->count() > 0): ?>
                                            <?php $itemsFound = true; ?>
                                            <?php $__currentLoopData = $a->mutasiItems->take(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="text-truncate fw-bold text-dark" style="font-size: 0.7rem;">
                                                    <i class="bi bi-dot"></i> <?php echo e($item->product_code); ?> (<?php echo e((int) $item->qty); ?>)
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                        <?php if($a->keterangan && !$itemsFound): ?>
                                            <div class="text-muted italic small text-truncate" style="max-width: 200px;">
                                                "<?php echo e($a->keterangan); ?>"</div>
                                        <?php endif; ?>
                                        <?php if(($a->adjustItems->count() + $a->mutasiItems->count()) > 1): ?>
                                            <small class="text-primary fw-bold" style="font-size: 0.6rem;">+ lainnya</small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex flex-column gap-2 align-items-center">
                                        
                                        <?php
                                            $kpC = match ($a->ket_process) {
                                                'Review' => ['bg' => '#fefce8', 'text' => '#854d0e', 'border' => '#fde047', 'dot' => '#facc15'],
                                                'Process' => ['bg' => '#f0f9ff', 'text' => '#075985', 'border' => '#7dd3fc', 'dot' => '#38bdf8'],
                                                'Done' => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#86efac', 'dot' => '#22c55e'],
                                                'Pending' => ['bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1', 'dot' => '#64748b'],
                                                'Void' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fca5a5', 'dot' => '#ef4444'],
                                                default => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0', 'dot' => '#94a3b8'],
                                            };
                                        ?>
                                        <div class="status-badge d-flex align-items-center gap-2"
                                            style="background: <?php echo e($kpC['bg']); ?>; color: <?php echo e($kpC['text']); ?>; border: 1px solid <?php echo e($kpC['border']); ?>;">
                                            <div class="rounded-circle"
                                                style="width: 6px; height: 6px; background-color: <?php echo e($kpC['dot']); ?>;"></div>
                                            <?php echo e(strtoupper($a->ket_process ?? '-')); ?>

                                        </div>
                                    </div>
                                </td>

                                <td class="text-end pe-4">
                                    <div class="d-flex gap-2 justify-content-end align-items-center">
                                        
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            <a href="<?php echo e(route('admin.arsip.print-draft', $a->id)); ?>" target="_blank"
                                                class="btn btn-sm btn-light border-end" title="Print Draft" 
                                                style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; background: white;">
                                                <i class="bi bi-printer text-secondary"></i>
                                            </a>
                                            <button class="btn btn-sm btn-light"
                                                onclick="showBukti('<?php echo e($a->bukti_scan ? url('/preview-file/' . $a->bukti_scan) : '#'); ?>')"
                                                <?php echo e(!$a->bukti_scan ? 'disabled' : ''); ?> title="View Bukti Scan"
                                                style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; background: white;">
                                                <i class="bi bi-eye <?php echo e($a->bukti_scan ? 'text-info' : 'text-muted'); ?>"></i>
                                            </button>
                                        </div>

                                        
                                        <?php if(!in_array($a->status, ['Done', 'Reject', 'Void']) && !in_array($a->ket_process, ['Done', 'Void'])): ?>
                                            <button class="btn btn-sm btn-white border shadow-sm rounded-3 hover-warning"
                                                onclick="editArsip(<?php echo e($a->id); ?>)" title="Edit Data"
                                                style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; background: white;">
                                                <i class="bi bi-pencil-square text-warning"></i>
                                            </button>
                                        <?php endif; ?>

                                        
                                        <?php if(auth()->user()->role === 'accounting' && $a->jenis_pengajuan === 'Adjust' && in_array($a->ket_process, ['Process', 'Done'])): ?>
                                            <button class="btn btn-sm shadow-sm rounded-3 px-3 fw-bold text-white btn-upload-ba d-flex align-items-center gap-2"
                                                style="height: 38px; background: linear-gradient(135deg, #f59e0b, #d97706); border: none;"
                                                onclick="openModalReuploadBA(<?php echo e($a->id); ?>, '<?php echo e($a->no_registrasi); ?>')"
                                                title="Upload Scan BA (Accounting)">
                                                <i class="bi bi-cloud-arrow-up-fill"></i>
                                                <span class="small">BA</span>
                                            </button>
                                        <?php endif; ?>

                                        
                                        <?php if($a->scan_ba_accounting): ?>
                                            <button class="btn btn-sm btn-success shadow-sm rounded-3"
                                                onclick="showBukti('<?php echo e(url('/preview-file/' . $a->scan_ba_accounting)); ?>')"
                                                title="View Scan BA Final"
                                                style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-file-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($arsips->hasPages()): ?>
                <div class="card-footer bg-white border-top border-light p-3">
                    <?php echo e($arsips->links('pagination::bootstrap-5')); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <?php echo $__env->make('admin.arsip._create', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('admin.arsip._edit', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('superadmin.arsip._view', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <div class="modal fade" id="modalReuploadBA" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-warning text-dark border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>Upload Ulang Scan BA
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formReuploadBA" action="" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <h6 class="fw-bold text-secondary mb-1">NO REGISTRASI:</h6>
                            <h5 id="reuploadNoReg" class="fw-extrabold text-primary font-monospace"></h5>
                        </div>
                        <hr class="opacity-10">
                        <div class="text-start">
                            <label class="form-label small fw-bold text-uppercase">File Scan BA Final (PDF)</label>
                            <input type="file" name="scan_ba" class="form-control rounded-3 border-0 bg-light py-2" 
                                   accept=".pdf" required>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i> Upload file PDF BA yang sudah ditandatangani accounting & manager. (Maks. 10MB)
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">BATAL</button>
                        <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-cloud-upload-fill me-2"></i>UPLOAD SEKARANG
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function () {
            window.openModalReuploadBA = function(id, noReg) {
                $('#reuploadNoReg').text(noReg);
                $('#formReuploadBA').attr('action', `/admin/arsip/${id}/reupload-ba`);
                $('#modalReuploadBA').modal('show');
            }

            window.showBukti = function (url) {
                if (url && url !== '#') {
                    window.open(url, '_blank');
                }
            };

            // =========================================================================
            // 0. PAGINATION SIZE
            // =========================================================================
            $('#perPageSelect').on('change', function () {
                let perPage = $(this).val();
                let url = new URL(window.location.href);
                url.searchParams.set('per_page', perPage);
                window.location.href = url.toString();
            });

            // =========================================================================
            // A. LOGIKA TAMPILAN & TAMBAH DATA BARU (CREATE)
            // =========================================================================

            // Check if element exists to avoid errors on other pages
            const $jenisSelect = $('#jenisPengajuanTambahAdmin');
            if (!$jenisSelect.length) return;

            const $wrapKategori = $('#wrapperKategori');

            // 1. SHOW/HIDE SECTION
            $jenisSelect.on('change', function () {
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

            // Helper Add Row
            function refreshAllItemCounts() {
                ['wrapperAdjust', 'wrapperAsal', 'wrapperTujuan', 'wrapperBundel'].forEach(id => {
                    let count = $(`#${id} tr`).length;
                    let badgeId = id.replace('wrapper', 'badgeCount');
                    if (count > 0) {
                        $(`#${badgeId}`).text(`1-${count} of ${count}`).removeClass('d-none');
                    } else {
                        $(`#${badgeId}`).addClass('d-none');
                    }
                });
            }

            // 2. TAMBAH BARIS ITEM (CREATE)
            // -- ADJUST --
            $('#btnAddAdjust').on('click', function () {
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
                refreshAllItemCounts();
            });

            // -- MUTASI --
            window.addMutasiRow = function (targetId, prefixName) {
                let idx = getIndex();
                let locations = <?php echo json_encode(\App\Models\ArsipMutasiItem::getLocations(), 15, 512) ?>;
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
            $('#btnAddAsal').on('click', () => { window.addMutasiRow('wrapperAsal', 'mutasi_asal'); refreshAllItemCounts(); });
            $('#btnAddTujuan').on('click', () => { window.addMutasiRow('wrapperTujuan', 'mutasi_tujuan'); refreshAllItemCounts(); });

            // -- BUNDEL --
            $('#btnAddBundel').on('click', function () {
                let idx = getIndex();
                $('#wrapperBundel').append(`
                <tr>
                    <td><input type="text" name="bundel[${idx}][no_doc]" class="form-control form-control-sm border-0 bg-light" placeholder="No Dokumen" required></td>
                    <td><input type="number" step="any" name="bundel[${idx}][qty]" class="form-control form-control-sm border-0 bg-light fw-bold text-center" value="1" required></td>
                    <td><input type="text" name="bundel[${idx}][keterangan]" class="form-control form-control-sm border-0 bg-light" placeholder="Keterangan"></td>
                    <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
                </tr>
            `);
                refreshAllItemCounts();
            });

            // -- HAPUS BARIS --
            $(document).on('click', '.btnRemove', function () { $(this).closest('tr').remove(); refreshAllItemCounts(); });

            // Trigger change saat load agar form create bersih
            if ($jenisSelect.length) $jenisSelect.trigger('change');


            // =========================================================================
            // B. LOGIKA SIMPAN PERUBAHAN (EDIT / UPDATE)
            // =========================================================================

            $('#formEditArsip').on('submit', function (e) {
                e.preventDefault(); // STOP submit bawaan browser

                let id = $('#editArsipId').val(); // Ambil ID

                if (!id) {
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
                    beforeSend: function () {
                        $('button[type="submit"]', '#formEditArsip').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                    },
                    success: function (response) {
                        $('#modalEditArsip').modal('hide');
                        alert('Data Berhasil Diupdate!');
                        location.reload();
                    },
                    error: function (xhr) {
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
        window.addAdjustRowEdit = function (code = '', name = '', qty_in = 0, qty_out = 0, lot = '') {
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

        window.addMutasiRowEdit = function (type, code = '', name = '', qty = 1, lot = '', panjang = '', location = '') {
            let idx = Date.now() + Math.floor(Math.random() * 1000);
            let color = type === 'asal' ? 'danger' : 'success';
            let key = type === 'asal' ? 'mutasi_asal' : 'mutasi_tujuan';

            let locations = <?php echo json_encode(\App\Models\ArsipMutasiItem::getLocations(), 15, 512) ?>;
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
            if (type === 'asal') $('#wrapperAsalEdit').append(html);
            else $('#wrapperTujuanEdit').append(html);
        };

        window.addBundelRowEdit = function (no_doc = '', qty = 1, ket = '') {
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
        window.editArsip = function (id) {
            // 1. Reset Form Edit
            $('#formEditArsip')[0].reset();
            $('.dynamic-section-edit').addClass('d-none');
            $('#wrapperAdjustEdit, #wrapperAsalEdit, #wrapperTujuanEdit, #wrapperBundelEdit').empty();

            // 2. Set ID ke Hidden Input
            $('#editArsipId').val(id);

            // 3. Ambil Data dari Server
            let urlShow = "<?php echo e(route('admin.arsip.edit', ':id')); ?>";
            urlShow = urlShow.replace(':id', id);

            $.ajax({
                url: urlShow,
                type: "GET",
                success: function (response) {
                    let data = response.data;

                    // Reset Tampilan
                    $('#sectionNoTransEdit').addClass('d-none');
                    $('#editWrapperKategori').addClass('d-none');
                    $('#sectionBundelEdit').addClass('d-none');
                    $('#sectionAdjustEdit').addClass('d-none');
                    $('#sectionMutasiEdit').addClass('d-none');

                    $('#editNoTransaksi').prop('required', false);

                    // Update Action URL
                    let urlUpdate = "<?php echo e(route('admin.arsip.update', ':id')); ?>";
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
                    if (data.bukti_scan) {
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

                    if (jenis === 'Cancel') {
                        $('#sectionNoTransEdit').removeClass('d-none');
                        $('#editWrapperKategori').removeClass('d-none');
                        $('#editNoTransaksi').val(data.no_transaksi).prop('required', true);
                        $('#editKategori').val(data.kategori);
                    }
                    else if (jenis === 'Bundel') {
                        $('#sectionBundelEdit').removeClass('d-none');
                        if (data.bundel_items) {
                            data.bundel_items.forEach(item => {
                                addBundelRowEdit(item.no_doc, item.qty, item.keterangan);
                            });
                        }
                    }
                    else if (jenis === 'Adjust') {
                        $('#sectionAdjustEdit').removeClass('d-none');
                        if (data.adjust_items) {
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
                    else if (jenis && jenis.includes('Mutasi')) {
                        $('#sectionMutasiEdit').removeClass('d-none');
                        if (data.mutasi_items) {
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
                error: function (xhr) {
                    console.error("Error:", xhr);
                    alert('Gagal mengambil data. Silakan coba lagi.');
                }
            });
        }

        // =========================================================================
        // AUTO REFRESH (AJAX POLLING)
        // =========================================================================
        let currentLastUpdate = null;
        let currentCount = null;

        function checkArsipUpdates() {
            // JANGAN auto-refresh jika user sedang membuka modal (form tambah/edit)
            if ($('.modal.show').length > 0) return;

            $.ajax({
                url: "<?php echo e(route('arsip.check-updates')); ?>",
                type: "GET",
                cache: false,
                success: function (res) {
                    if (currentLastUpdate === null) {
                        currentLastUpdate = res.last_update;
                        currentCount = res.count;
                        return;
                    }

                    // Memuat ulang langsung jika data berubah
                    if (res.last_update !== currentLastUpdate || res.count !== currentCount) {
                        console.log("Perubahan terdeteksi! Refresh...");
                        window.location.reload();
                    }
                },
                error: function (err) { console.log("Gagal mengecek update_api: ", err); }
            });
        }

        // Pasang interval polling: panggil checkArsipUpdates() tiap 15 detik (15000ms)
        setInterval(checkArsipUpdates, 2000);
        checkArsipUpdates(); // Cek langsung saat load
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\e_arsip\resources\views/admin/arsip/index.blade.php ENDPATH**/ ?>