<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Print Draft - <?php echo e($arsip->no_registrasi); ?></title>
    <?php if(isset($app_logo) && $app_logo): ?>
        <link rel="icon" type="image/png" href="<?php echo e(asset('storage/settings/' . $app_logo)); ?>">
    <?php else: ?>
        <link rel="icon" type="image/png" href="<?php echo e(asset('img/logo.png')); ?>">
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 16mm 10mm 10mm 10mm;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            color: #000;
        }

        .print-container {
            display: flex;
            flex-direction: column;
            min-height: 96vh;
            padding: 15px 8px 6px 8px;
        }

        /* ── META BAR (Printed date / User) ── */
        .meta-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffffff;
            border: 1px solid #ffffffff;
            border-radius: 3px;
            padding: 3px 10px;
            margin-bottom: 8px;
            font-size: 9px;
            color: #444;
        }

        .meta-bar .meta-left {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .meta-bar .meta-item {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .meta-bar .meta-label {
            color: #777;
            font-weight: 500;
        }

        .meta-bar .meta-value {
            font-weight: 700;
            color: #111;
        }

        /* ── DOCUMENT HEADER ── */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }

        .doc-header-center {
            flex: 1;
            text-align: center;
        }

        .header-title {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }

        .header-doc {
            font-weight: 700;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .header-doc-note {
            font-size: 9px;
            font-style: italic;
            font-weight: 700;
            color: #555;
        }

        .qr-wrapper {
            width: 65px;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
        }

        .qr-label {
            font-size: 7px;
            font-weight: 700;
            color: #555;
            margin-top: 2px;
            text-align: center;
            letter-spacing: 0.5px;
        }

        /* ── SEPARATOR ── */
        .separator {
            border: none;
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        /* ── INFO TABLE ── */
        .info-table {
            border: none;
            margin-bottom: 4px;
            width: 100%;
        }

        .info-table td {
            border: none;
            padding: 3px 2px;
            text-align: left;
            vertical-align: top;
            font-weight: 700;
        }

        /* ── RULED LINES ── */
        .ruled {
            background-image: repeating-linear-gradient(to bottom,
                    transparent 0,
                    transparent 22px,
                    #000 22px,
                    #000 23.5px);
            line-height: 23.5px;
            font-size: 11px;
            padding-top: 2px;
        }

        /* ── TABLES ── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-bottom: 5px;
            table-layout: fixed;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 10px;
        }

        .main-table th {
            font-weight: 800;
            text-transform: uppercase;
        }

        /* ── SIGNATURE TABLE ── */
        .signature-table {
            width: 100%;
            border: 1.5px solid #000;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .signature-table th,
        .signature-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        .signature-table th {
            font-size: 10px;
            font-weight: 800;
        }

        .signature-table td {
            height: 95px;
            vertical-align: bottom;
        }

        /* ── FOOTER ── */
        .footer-section {
            margin-top: auto;
            width: 100%;
        }

        .doc-footer-note {
            text-align: right;
            font-size: 8.5px;
            font-weight: 700;
            color: #444;
            margin-top: 4px;
        }

        /* ── PRINT ── */
        @media print {
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .print-container {
                min-height: 96vh;
                padding: 20px 0 0 0;
            }

            .footer-section {
                page-break-inside: avoid;
            }

            .no-print {
                display: none !important;
            }
        }
        /* ── WATERMARK (Rubber Stamp Design) ── */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 85px;
            font-weight: 900;
            letter-spacing: 8px;
            opacity: 0.07;
            pointer-events: none;
            z-index: 9999;
            white-space: nowrap;
            user-select: none;
            font-family: 'Inter', sans-serif;
            text-transform: uppercase;
            border: 12px double currentColor;
            padding: 15px 40px;
            border-radius: 15px;
            display: inline-block;
        }
        .watermark-lengkap  { color: #059669; border-color: #059669; }
        .watermark-void     { color: #dc2626; border-color: #dc2626; }
        .watermark-reject   { color: #7c3aed; border-color: #7c3aed; }
    </style>
</head>

<body onload="window.print()">
    
    <?php
        $status = strtolower($arsip->status ?? 'pending');
        
        if ($status === 'done') {
            $wmClass = 'watermark-lengkap';
            $wmText  = \App\Models\Setting::get('wm_done', 'DONE');
        } elseif ($status === 'void') {
            $wmClass = 'watermark-void';
            $wmText  = \App\Models\Setting::get('wm_void', 'VOID');
        } elseif ($status === 'reject') {
            $wmClass = 'watermark-reject';
            $wmText  = \App\Models\Setting::get('wm_reject', 'REJECT');
        } else {
            $wmClass = '';
            $wmText  = '';
        }
    ?>
    
    <?php if(!empty($wmText)): ?>
        <div class="watermark <?php echo e($wmClass); ?>"><?php echo e($wmText); ?></div>
    <?php endif; ?>

    <div class="print-container">

        
        <div class="no-print" style="text-align: right; margin-bottom: 8px;">
            <button onclick="window.print()"
                style="padding: 7px 18px; background: #1a1a1a; color: #fff; border: none; cursor: pointer; border-radius: 5px; font-weight: 700; font-size: 12px;">
                🖨️ Cetak
            </button>
        </div>

        <?php
            $isAdjust = $arsip->jenis_pengajuan === 'Adjust';
            $title = $isAdjust ? 'BERITA ACARA PENGAJUAN ADJUSTMENT' : 'BERITA ACARA PENGAJUAN SYSTEM ODOO';
            $adjustItemsCount = isset($arsip->adjustItems) ? count($arsip->adjustItems) : 0;
            $isCompact = $isAdjust && $adjustItemsCount > 9;
        ?>

        <?php if($isCompact): ?>
            <style>
                .main-table th,
                .main-table td {
                    padding: 2px;
                }

                .main-table td {
                    height: 14px !important;
                }

                .info-table {
                    margin-bottom: 2px;
                }

                .signature-table td {
                    height: 70px !important;
                }
            </style>
        <?php endif; ?>

        
        <!-- <div class="meta-bar">
            <div class="meta-left">
                <div class="meta-item">
                    <span class="meta-label">Printed date</span>
                    <span class="meta-value"><?php echo e(\Carbon\Carbon::now()->format('d/m/Y H.i.s')); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">User:</span>
                    <span class="meta-value"><?php echo e(auth()->user()->name ?? '-'); ?></span>
                </div>
            </div>
            <div style="font-size: 8px; color: #999; font-style: italic;"><?php echo e($arsip->no_registrasi); ?></div>
        </div> -->

        
        <div class="doc-header">
            
            <div style="width: 65px; flex-shrink: 0;"></div>

            <div class="doc-header-center">
                <div class="header-title"><?php echo e($title); ?></div>
                <div class="header-doc">
                    No. Dokumen :
                    <span
                        style="display:inline-block; border-bottom: 1.5px solid #000; min-width: 220px; padding-bottom: 1px;">
                        <?php echo e($arsip->no_doc ?? ''); ?>

                    </span>
                </div>
                <div class="header-doc-note">(DIISI OLEH DEPARTEMEN IT)*</div>
            </div>

            
            <div class="qr-wrapper">
                <div id="qrcode"></div>
                <div class="qr-label"><?php echo e(Str::limit($arsip->no_registrasi, 14, '')); ?></div>
            </div>
        </div>

        <!-- <hr class="separator"> -->

        
        <table class="info-table">
            <tr>
                <td style="width: 200px;">PERIHAL</td>
                <td style="width: 10px;">:</td>
                <td style="border-bottom: 1.5px solid #000;">
                    <?php echo e(strtoupper(str_replace('_', ' ', $arsip->jenis_pengajuan))); ?>

                </td>
            </tr>
            <tr>
                <td>PEMOHON</td>
                <td>:</td>
                <td style="border-bottom: 1.5px solid #000;">
                    <?php echo e(strtoupper($arsip->pemohon ?? $arsip->admin->name)); ?>

                </td>
            </tr>
            <tr>
                <td>DEPARTEMEN / UNIT KERJA</td>
                <td>:</td>
                <td style="border-bottom: 1.5px solid #000;">
                    <?php echo e(strtoupper($arsip->department->name ?? '')); ?> / <?php echo e(strtoupper($arsip->unit->name ?? '')); ?>

                </td>
            </tr>
            <tr>
                <td style="padding-top: 10px;">DESKRIPSI PERMASALAHAN</td>
                <td style="padding-top: 10px;">:</td>
                <td></td>
            </tr>
        </table>

        
        <div style="margin-top: -3px; position: relative;">
            <?php if($isAdjust): ?>
                <table class="main-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">KODE BARANG</th>
                            <th style="width: 35%;">NAMA BARANG</th>
                            <th style="width: 10%;">ODOO</th>
                            <th style="width: 10%;">FISIK</th>
                            <th style="width: 10%;">SELISIH</th>
                            <th style="width: 10%;">ADJUST</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $adjustItems = $arsip->adjustItems ?? []; ?>
                        <?php for($i = 0; $i < max(4, count($adjustItems)); $i++): ?>
                            <tr>
                                <td style="height: 18px;"><?php echo e($adjustItems[$i]->product_code ?? ''); ?></td>
                                <td style="text-align: left;"><?php echo e($adjustItems[$i]->product_name ?? ''); ?></td>
                                <td></td>
                                <td></td>
                                <td><?php echo e(isset($adjustItems[$i]) ? (($adjustItems[$i]->qty_in ?? 0) - ($adjustItems[$i]->qty_out ?? 0)) : ''); ?>

                                </td>
                                <td><?php echo e(isset($adjustItems[$i]) ? ((($adjustItems[$i]->qty_in ?? 0) > 0) ? 'IN' : 'OUT') : ''); ?>

                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <div style="font-weight: 800; margin-top: 5px; height: 18.5px;">CATATAN:</div>
            <?php endif; ?>

            <?php
                $keteranganLines = 6;
                $tindakanLines = 6;
                if ($isAdjust) {
                    $excessItems = max(0, $adjustItemsCount - 4);
                    $linesToRemove = (int) ceil($excessItems * 1.2);
                    if ($linesToRemove > 0) {
                        $keteranganLines = max(1, $keteranganLines - ceil($linesToRemove / 2));
                        $tindakanLines = max(1, $tindakanLines - floor($linesToRemove / 2));
                    }
                    if ($excessItems >= 8) {
                        $keteranganLines = 1;
                        $tindakanLines = 1;
                    }
                }
            ?>

            <div class="ruled"
                style="min-height: <?php echo e($keteranganLines * 23.5); ?>px; margin-top: 2px; text-align: justify;">
                <?php if(!empty(trim($arsip->keterangan))): ?>
                    <div style="white-space: pre-wrap;"><?php echo e(trim($arsip->keterangan)); ?></div>
                <?php endif; ?>
                <?php if(!empty(trim($arsip->no_transaksi))): ?>
                    <?php
                        $normalized = preg_replace('/\|+/', "\n\n", trim($arsip->no_transaksi));
                        $normalized = str_replace("\r\n", "\n", $normalized);
                        $trxGroups = preg_split('/\n{2,}/', $normalized);
                        $trxGroups = array_filter(array_map('trim', $trxGroups));
                    ?>
                    <div>
                        <div style="font-weight: 800;">No. Transaksi :</div>
                        <div style="column-count: 3; column-gap: 15px; font-weight: 700;">
                            <?php $__currentLoopData = $trxGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div style="break-inside: avoid; page-break-inside: avoid;">
                                    <?php
                                        $lines = array_filter(array_map('trim', explode("\n", $group)));
                                    ?>
                                    <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div><?php echo e($line); ?></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <div>&nbsp;</div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if(!$isAdjust): ?>
                    <?php if(str_contains($arsip->jenis_pengajuan, 'Mutasi')): ?>
                        <?php $__currentLoopData = $arsip->mutasiItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div><?php echo e(strtoupper($m->type)); ?>: <?php echo e($m->product_code); ?> - <?php echo e($m->product_name); ?> (<?php echo e($m->qty); ?>)</div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php elseif($arsip->jenis_pengajuan === 'Bundel'): ?>
                        <?php $__currentLoopData = $arsip->bundelItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>DOKUMEN: <?php echo e($b->no_doc); ?> (Qty: <?php echo e($b->qty); ?>)</div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        
        <div style="margin-top: 14px; display: flex; flex-direction: column; flex-grow: 1;">
            <div>
                <div style="font-weight: 800;">TINDAKAN</div>
                <div style="font-size: 9px; font-style: italic; font-weight: 700; color: #555;">(DIISI OLEH DEPARTEMEN
                    IT)*</div>
            </div>

            <?php if($isAdjust): ?>
                <table class="main-table" style="margin-top: 5px;">
                    <thead>
                        <tr>
                            <th style="width: 25%;">IN</th>
                            <th style="width: 25%;">KETERANGAN</th>
                            <th style="width: 25%;">OUT</th>
                            <th style="width: 25%;">KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($j = 0; $j < 3; $j++): ?>
                            <tr>
                                <td style="height: 18px;"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="ruled"
                style="flex-grow: 1; min-height: <?php echo e($tindakanLines * 23.5); ?>px; margin-top: 5px; margin-bottom: 18px;">
            </div>
        </div>

        
        <div class="footer-section" style="page-break-inside: avoid; break-inside: avoid;">
            <div style="margin-bottom: 5px; font-weight: 800;">
                <?php $kotaBa = \App\Models\Setting::get('kota_ba', 'PASURUAN'); ?>
                <?php echo e($kotaBa); ?>,
                <?php echo e($isAdjust ? '____________________' : \Carbon\Carbon::parse($arsip->created_at)->translatedFormat('d F Y')); ?>

            </div>

            <table class="signature-table">
                <tr>
                    <th style="width: 20%;">Diajukan Oleh,</th>
                    <?php if($isAdjust): ?>
                        <th colspan="2" style="width: 40%;">Diketahui Oleh,</th>
                        <th style="width: 20%;">Disetujui Oleh,</th>
                    <?php else: ?>
                        <th colspan="3" style="width: 60%;">Diketahui Oleh,</th>
                    <?php endif; ?>
                    <th style="width: 20%;">Dikerjakan Oleh,</th>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: 800; font-size: 10px;">Pemohon</div>
                        <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                    </td>
                    <?php if($isAdjust): ?>
                        <td style="width: 20%;">
                            <div style="font-weight: 800; font-size: 10px;">SPV / Kabag</div>
                            <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                        <td style="width: 20%;">
                            <div style="font-weight: 800; font-size: 10px;">Manager</div>
                            <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                        <td style="width: 20%;">
                            <div style="font-weight: 800; font-size: 10px;">Accounting</div>
                            <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                    <?php else: ?>
                        <td style="width: 20%;">
                            <div style="font-weight: 800; font-size: 10px;">SPV</div>
                            <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                        <td style="width: 20%;">
                            <div style="font-weight: 800; font-size: 10px;">Kabag</div>
                            <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                        <td style="width: 20%;">
                            <div style="font-weight: 800; font-size: 10px;">Manager</div>
                            <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                    <?php endif; ?>
                    <td>
                        <div style="font-weight: 800; font-size: 10px;">Departemen IT</div>
                        <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                    </td>
                </tr>
            </table>

            <div class="doc-footer-note">
                <?php echo e($isAdjust ? 'Adjustment' : 'System Odoo'); ?> / 01 / 15 Januari 2025
            </div>
        </div>
        <div class="meta-bar">
            <div class="meta-left">
                <div class="meta-item">
                    <span class="meta-label">Printed date</span>
                    <span class="meta-value"><?php echo e(\Carbon\Carbon::now()->format('d/m/Y H.i.s')); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">User:</span>
                    <span class="meta-value"><?php echo e(auth()->user()->name ?? '-'); ?></span>
                </div>
            </div>
            <div style="font-size: 8px; color: #999; font-style: italic;"><?php echo e($arsip->no_registrasi); ?></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new QRCode(document.getElementById("qrcode"), {
                text: "<?php echo e($arsip->no_registrasi); ?>",
                width: 62,
                height: 62,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        });
    </script>
</body>

</html><?php /**PATH C:\laragon\www\e_arsip\resources\views/print/arsip_draft.blade.php ENDPATH**/ ?>