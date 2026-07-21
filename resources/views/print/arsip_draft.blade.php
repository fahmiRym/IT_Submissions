<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Print Draft - {{ $arsip->no_registrasi }}</title>
    @if(isset($app_logo) && $app_logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/settings/' . $app_logo) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    @endif
    <style>
        @page { size: A4 portrait; margin: 0; }

        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            height: 297mm;
            max-height: 297mm;
            overflow: hidden;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.35;
        }

        /* ─── PAGE CONTAINER ──────────────────────────────────────────── */
        .print-container {
            position: relative;
            height: 277mm;
            max-height: 277mm;
            padding: 13mm 12mm 5mm 12mm;
            overflow: hidden;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        /* ─── DOCUMENT HEADER ─────────────────────────────────────────── */
        .doc-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .doc-header td {
            vertical-align: middle;
            padding: 0;
        }
        .doc-header-center {
            text-align: center;
            padding: 0 4mm;
        }
        .header-title {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.4px;
        }
        .header-doc {
            font-weight: 700;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .header-doc .no-doc-line {
            display: inline-block;
            border-bottom: 1.5px solid #000;
            min-width: 220px;
            padding-bottom: 1px;
        }
        .header-doc-note {
            font-size: 9px;
            font-style: italic;
            font-weight: 700;
            color: #555;
        }

        .qr-wrapper {
            width: 80px;
            text-align: center;
        }
        .qr-wrapper img {
            width: 58px;
            height: 58px;
            display: inline-block;
            object-fit: contain;
        }
        .qr-label {
            font-size: 7px;
            font-weight: 700;
            color: #555;
            margin-top: 2px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .qr-label.verify { color: #1d4ed8; }
        .qr-label.reg    { font-family: monospace; color: #111; letter-spacing: 0; text-transform: none; }

        /* ─── INFO TABLE ──────────────────────────────────────────────── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .info-table td {
            padding: 3px 2px;
            vertical-align: top;
            font-weight: 700;
            text-align: left;
        }
        .info-table td.label { width: 200px; }
        .info-table td.colon { width: 10px; }
        .info-table td.value { border-bottom: 1.5px solid #000; }
        .info-table tr.desc-row td { padding-top: 10px; }

        /* ─── RULED LINES ─────────────────────────────────────────────── */
        .ruled-line {
            border-bottom: 1px solid #000;
            height: 22px;
            line-height: 22px;
            font-size: 11px;
            padding: 0 2px;
        }
        .ruled-content {
            border-bottom: 1px solid #000;
            min-height: 22px;
            line-height: 22px;
            padding: 0 2px;
            font-weight: 700;
            white-space: pre-wrap;
        }
        .ruled-content .trx-label { font-weight: 800; }
        .ruled-content .trx-line {
            border-bottom: 1px solid #000;
            line-height: 22px;
            padding: 0 2px;
        }

        /* ─── MAIN ITEM TABLE ─────────────────────────────────────────── */
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
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
        .main-table th {
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.2;
            background: #f7f7f7;
        }
        .main-table td.cell-left { text-align: left; }

        /* ─── SECTION HEADINGS ────────────────────────────────────────── */
        .section-title {
            font-weight: 800;
            margin: 5px 0 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .section-note {
            font-size: 9px;
            font-style: italic;
            font-weight: 700;
            color: #555;
            margin-bottom: 2px;
        }
        .section-catatan {
            font-weight: 800;
            margin-top: 5px;
            height: 18.5px;
        }

        /* ─── SIGNATURE BLOCK (anchored bottom, kasih breathing room utk _print_footer) ─ */
        .footer-section-wrap {
            position: absolute;
            left: 12mm;
            right: 12mm;
            bottom: 10mm;             /* was 5mm — naik 5mm utk gap dgn _print_footer */
            background: #fff;
            z-index: 100;
            padding-top: 4mm;
            page-break-inside: avoid;
            page-break-before: avoid;
            page-break-after: avoid;
        }
        .footer-place-date {
            margin: 0 0 5px;
            font-weight: 800;
            text-transform: uppercase;
            background: #fff;
            padding-top: 1mm;
        }

        .signature-table {
            width: 100%;
            border: 1.5px solid #000;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .signature-table th,
        .signature-table td {
            border: 1px solid #000;
            text-align: center;
        }
        .signature-table th {
            font-size: 10px;
            font-weight: 800;
            padding: 5px 3px;
            background: #f7f7f7;
        }
        .signature-table td {
            height: 72px;
            vertical-align: bottom;
            padding: 3px;
        }

        .sig-stamp { min-height: 52px; line-height: 1; text-align: center; }
        .sig-stamp img {
            width: 48px;
            height: 48px;
            display: inline-block;
            object-fit: contain;
        }
        .sig-stamp .sig-name {
            font-size: 7.5px;
            font-weight: 800;
            margin-top: 1px;
            color: #0f172a;
        }
        .sig-stamp .sig-ts {
            font-size: 6.5px;
            color: #475569;
            font-style: italic;
        }
        .sig-stamp .sig-pending {
            color: #cbd5e1;
            font-size: 7px;
            font-style: italic;
            padding-top: 18px;
        }
        .sig-stamp .sig-delegate {
            font-size: 6.5px;
            color: #92400e;
            font-weight: 700;
            font-style: italic;
            background: #fef3c7;
            border-radius: 2px;
            padding: 1px 3px;
            margin-top: 1px;
            display: inline-block;
        }
        .sig-role {
            font-weight: 800;
            font-size: 10px;
            margin-top: 1px;
        }
        .sig-hint {
            font-size: 8px;
            font-style: italic;
            color: #444;
        }

        /* ─── DOC FOOTER NOTES ────────────────────────────────────────── */
        .doc-footer-note {
            text-align: right;
            font-size: 7.5px;
            font-weight: 700;
            color: #444;
            margin-top: 4px;
        }
        .ttd-validation {
            margin-top: 3px;
            padding: 4px 8px;
            border: 1px dashed #1d4ed8;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: 700;
            color: #1e293b;
            display: table;
            width: 100%;
        }
        .ttd-validation .ttd-text { display: table-cell; vertical-align: middle; }
        .ttd-validation .ttd-token {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-family: monospace;
            color: #1d4ed8;
            white-space: nowrap;
            width: 110px;
        }
        .ttd-validation .ttd-check { color: #1d4ed8; font-style: normal; margin-right: 3px; }

        /* ─── WATERMARK (VOID/REJECT only) ────────────────────────── */
        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 90px;
            font-weight: 900;
            letter-spacing: 8px;
            color: rgba(220, 38, 38, 0.08);
            pointer-events: none;
            z-index: 0;
            white-space: nowrap;
            user-select: none;
            font-family: 'DejaVu Sans', sans-serif;
            text-transform: uppercase;
        }
        .watermark-void   { color: rgba(220, 38, 38, 0.10); }
        .watermark-reject { color: rgba(124, 58, 237, 0.10); }

        /* ─── PRINT MEDIA ─────────────────────────────────────────── */
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>

<body onload="window.print()">
    {{-- ─── WATERMARK (VOID / REJECT) ───────────────────────────────── --}}
    @php
        $status = strtolower($arsip->status ?? 'pending');
        $wmClass = '';
        $wmText  = '';
        if ($status === 'void') {
            $wmClass = 'watermark-void';
            $wmText  = \App\Models\Setting::get('wm_void', 'VOID');
        } elseif ($status === 'reject') {
            $wmClass = 'watermark-reject';
            $wmText  = \App\Models\Setting::get('wm_reject', 'REJECT');
        }
    @endphp
    @if(!empty($wmText))
        <div class="watermark {{ $wmClass }}">{{ $wmText }}</div>
    @endif

    <div class="print-container">

        {{-- ─── PRINT BUTTON (screen only) ──────────────────────────── --}}
        @if(empty($forPdf))
            <div class="no-print" style="text-align: right; margin-bottom: 8px;">
                <button onclick="window.print()"
                    style="padding: 7px 18px; background: #1a1a1a; color: #fff; border: none; cursor: pointer; border-radius: 5px; font-weight: 700; font-size: 12px;">
                    Cetak
                </button>
            </div>
        @endif

        @php
            $isAdjust = $arsip->jenis_pengajuan === 'Adjust';
            $isProdukBaru = $arsip->jenis_pengajuan === 'Produk_Baru';
            $title = $isAdjust
                ? 'BERITA ACARA PENGAJUAN ADJUSTMENT'
                : ($isProdukBaru ? 'BERITA ACARA PENGAJUAN PRODUK BARU' : 'BERITA ACARA PENGAJUAN SYSTEM ODOO');

            $adjustItemsCount     = isset($arsip->adjustItems)     ? count($arsip->adjustItems)     : 0;
            $produkBaruItemsCount = isset($arsip->produkBaruItems) ? count($arsip->produkBaruItems) : 0;
            $totalItems = max(
                $adjustItemsCount, $produkBaruItemsCount,
                isset($arsip->mutasiItems) ? count($arsip->mutasiItems) : 0,
                isset($arsip->bundelItems) ? count($arsip->bundelItems) : 0
            );
            $compactLevel = 0;
            if ($totalItems > 6 && $totalItems <= 10)       $compactLevel = 1;
            elseif ($totalItems > 10 && $totalItems <= 16)  $compactLevel = 2;
            elseif ($totalItems > 16)                       $compactLevel = 3;
        @endphp

        {{-- ─── AUTO-COMPACT STYLES (kicks in only when items are many) ── --}}
        @if($compactLevel === 1)
            <style>
                body { font-size: 10.5px; }
                .main-table th, .main-table td { padding: 3px; font-size: 9.5px; }
                .info-table { margin-bottom: 3px; }
                .signature-table td { height: 70px; padding: 3px; }
                .header-title { font-size: 14px; }
            </style>
        @elseif($compactLevel === 2)
            <style>
                body { font-size: 9.5px; }
                .main-table th, .main-table td { padding: 2.5px; font-size: 8.5px; }
                .main-table td { height: 14px; }
                .info-table { margin-bottom: 2px; }
                .info-table td { padding: 2px; font-size: 10px; }
                .signature-table td { height: 64px; padding: 2px; }
                .header-title { font-size: 13px; }
                .ruled-line, .ruled-content { line-height: 18px; height: 18px; font-size: 9.5px; }
                .ruled-content { min-height: 18px; }
            </style>
        @elseif($compactLevel === 3)
            <style>
                body { font-size: 8.5px; }
                .main-table th, .main-table td { padding: 1.5px; font-size: 7.5px; }
                .main-table td { height: 12px; }
                .info-table { margin-bottom: 2px; }
                .info-table td { padding: 1.5px; font-size: 9px; }
                .signature-table td { height: 58px; padding: 2px; }
                .header-title { font-size: 12px; }
                .ruled-line, .ruled-content { line-height: 16px; height: 16px; font-size: 8.5px; }
                .ruled-content { min-height: 16px; }
            </style>
        @endif

        {{-- ─── DOCUMENT HEADER (QR | Title | QR) ─────────────────── --}}
        @php
            $qrDocVerify = \App\Services\QrSignatureService::renderDocumentQrDataUri($arsip, 160);
            $qrDocReg    = \App\Services\QrSignatureService::renderTextQrDataUri($arsip->no_registrasi ?: 'NO-REG', 120);
        @endphp
        <table class="doc-header">
            <tr>
                <td class="qr-wrapper">
                    @if($qrDocVerify)
                        <img src="{{ $qrDocVerify }}" alt="QR Verify">
                    @endif
                    @if($arsip->verify_token)
                        <div class="qr-label verify">SCAN VERIFIKASI</div>
                    @endif
                </td>

                <td class="doc-header-center">
                    <div class="header-title">{{ $title }}</div>
                    <div class="header-doc">
                        No. Dokumen :
                        <span class="no-doc-line">{{ $arsip->no_doc ?? '' }}</span>
                    </div>
                    <div class="header-doc-note">(DIISI OLEH DEPARTEMEN IT)*</div>
                </td>

                <td class="qr-wrapper">
                    @if($qrDocReg)
                        <img src="{{ $qrDocReg }}" alt="QR No Registrasi">
                    @endif
                    <div class="qr-label reg">{{ $arsip->no_registrasi }}</div>
                </td>
            </tr>
        </table>

        {{-- ─── INFO TABLE ──────────────────────────────────────────── --}}
        <table class="info-table">
            <tr>
                <td class="label">PERIHAL</td>
                <td class="colon">:</td>
                <td class="value">{{ strtoupper(str_replace('_', ' ', $arsip->jenis_pengajuan)) }}</td>
            </tr>
            <tr>
                <td class="label">PEMOHON</td>
                <td class="colon">:</td>
                <td class="value">{{ strtoupper($arsip->pemohon ?? $arsip->admin->name ?? '') }}</td>
            </tr>
            <tr>
                <td class="label">DEPARTEMEN / UNIT KERJA</td>
                <td class="colon">:</td>
                <td class="value">
                    {{ strtoupper($arsip->department->name ?? '') }} / {{ strtoupper($arsip->unit->name ?? '') }}
                </td>
            </tr>
            <tr class="desc-row">
                <td class="label">DESKRIPSI PERMASALAHAN</td>
                <td class="colon">:</td>
                <td></td>
            </tr>
        </table>

        {{-- ─── CONTENT AREA ────────────────────────────────────────── --}}
        <div style="position: relative;">
            @if($isAdjust)
                <table class="main-table">
                    <thead>
                        <tr>
                            <th style="width: 14%;">KODE<br>BARANG</th>
                            <th style="width: 24%;">NAMA BARANG</th>
                            <th style="width: 13%;">LOT</th>
                            <th style="width: 17%;">LOKASI</th>
                            <th style="width: 9%;">ODOO</th>
                            <th style="width: 9%;">FISIK</th>
                            <th style="width: 8%;">SELISIH</th>
                            <th style="width: 6%;">ADJ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $adjustItems = $arsip->adjustItems ?? []; @endphp
                        @for($i = 0; $i < max(4, count($adjustItems)); $i++)
                            @php $row = $adjustItems[$i] ?? null; @endphp
                            <tr>
                                <td style="height: 18px;">{{ $row->product_code ?? '' }}</td>
                                <td class="cell-left">{{ $row->product_name ?? '' }}</td>
                                <td>{{ $row->lot ?? '' }}</td>
                                <td>{{ $row->location ?? '' }}</td>
                                <td>{{ $row->odoo ?? '' }}</td>
                                <td>{{ $row->fisik ?? '' }}</td>
                                <td>{{ $row ? (($row->qty_in ?? 0) - ($row->qty_out ?? 0)) : '' }}</td>
                                <td>{{ $row ? ((($row->qty_in ?? 0) > 0) ? 'IN' : 'OUT') : '' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
                <div class="section-catatan">CATATAN:</div>
            @endif

            @if($isProdukBaru)
                <table class="main-table">
                    <thead>
                        <tr>
                            <th style="width: 14%;">KODE</th>
                            <th style="width: 30%;">NAMA PRODUK</th>
                            <th style="width: 12%;">TIPE</th>
                            <th style="width: 22%;">KATEGORI</th>
                            <th style="width: 10%;">SATUAN</th>
                            <th style="width: 12%;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $produkBaruItems = $arsip->produkBaruItems ?? []; @endphp
                        @for($i = 0; $i < max(4, count($produkBaruItems)); $i++)
                            @php $row = $produkBaruItems[$i] ?? null; @endphp
                            <tr>
                                <td style="height: 18px;">{{ $row->product_code ?? '' }}</td>
                                <td class="cell-left">{{ $row->product_name ?? '' }}</td>
                                <td>{{ $row->tipe_produk ?? '' }}</td>
                                <td class="cell-left">{{ $row->kategori ?? '' }}</td>
                                <td>{{ $row->satuan ?? '' }}</td>
                                <td>{{ $row->status_approval ?? '' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
                <div class="section-catatan">CATATAN:</div>
            @endif

            @php
                // Ruled-lines budget — sisa area antara content & signature anchored di bottom.
                if ($isAdjust) {
                    // Rebalance: keterangan filler diperbanyak supaya TINDAKAN section turun ke bawah.
                    // Total turun -2 lines (sinkron dgn wrap naik 5mm utk breathing room footer).
                    $keteranganLines = 5;
                    $tindakanLines   = 9;
                    if ($adjustItemsCount >= 3)  { $keteranganLines = 4; $tindakanLines = 8; }
                    if ($adjustItemsCount >= 5)  { $keteranganLines = 4; $tindakanLines = 7; }
                    if ($adjustItemsCount >= 7)  { $keteranganLines = 3; $tindakanLines = 7; }
                    if ($adjustItemsCount >= 9)  { $keteranganLines = 2; $tindakanLines = 6; }
                    if ($adjustItemsCount >= 12) { $keteranganLines = 2; $tindakanLines = 5; }
                    if ($adjustItemsCount >= 15) { $keteranganLines = 1; $tindakanLines = 4; }
                } else {
                    $BUDGET_RULED   = 24;
                    $usedRuledLines = 0;

                    if (!empty(trim((string) $arsip->no_transaksi))) {
                        $nrm = preg_replace('/\|+/', "\n\n", trim((string) $arsip->no_transaksi));
                        $nrm = str_replace("\r\n", "\n", $nrm);
                        $grps = array_values(array_filter(array_map('trim', preg_split('/\n{2,}/', $nrm))));
                        // Label "No. Transaksi :" + tiap baris di tiap group + 1 spacer-line antar group
                        $usedRuledLines += 1; // label row
                        foreach ($grps as $i => $g) {
                            $usedRuledLines += count(array_filter(array_map('trim', explode("\n", $g))));
                            if ($i < count($grps) - 1) $usedRuledLines += 1; // separator
                        }
                    }
                    if (str_contains($arsip->jenis_pengajuan, 'Mutasi')) {
                        $usedRuledLines += $arsip->mutasiItems?->count() ?? 0;
                    } elseif ($arsip->jenis_pengajuan === 'Bundel') {
                        $usedRuledLines += $arsip->bundelItems?->count() ?? 0;
                    }
                    if (!empty(trim((string) $arsip->keterangan))) {
                        $ketLines = preg_split('/\r\n|\r|\n/', (string) $arsip->keterangan);
                        foreach ($ketLines as $kl) {
                            $usedRuledLines += max(1, (int) ceil(mb_strlen($kl) / 90));
                        }
                    }

                    $remainingBudget = max(12, $BUDGET_RULED - $usedRuledLines);
                    $tindakanLines   = min(15, max(5, (int) floor($remainingBudget * 0.60)));
                    $keteranganLines = max(3, $remainingBudget - $tindakanLines);
                }
            @endphp

            <div style="margin-top: 2px; text-align: justify;">
                @if(!empty(trim((string) $arsip->keterangan)))
                    @php
                        $ketLines = preg_split('/\r\n|\r|\n/', trim((string) $arsip->keterangan));
                    @endphp
                    @foreach($ketLines as $kline)
                        @php $kt = trim($kline); @endphp
                        <div class="ruled-line" style="font-weight: 700;">{!! $kt === '' ? '&nbsp;' : e($kt) !!}</div>
                    @endforeach
                @endif

                @if(!empty(trim((string) $arsip->no_transaksi)))
                    @php
                        $normalized = preg_replace('/\|+/', "\n\n", trim((string) $arsip->no_transaksi));
                        $normalized = str_replace("\r\n", "\n", $normalized);
                        $trxGroups  = array_values(array_filter(array_map('trim', preg_split('/\n{2,}/', $normalized))));
                    @endphp
                    <div class="ruled-line" style="font-weight: 800; color: #000;">No. Transaksi :</div>
                    @foreach($trxGroups as $gIdx => $group)
                        @php $lines = array_values(array_filter(array_map('trim', explode("\n", $group)))); @endphp
                        @foreach($lines as $line)
                            <div class="ruled-line" style="color: #000;">{{ $line }}</div>
                        @endforeach
                        @if($gIdx < count($trxGroups) - 1)
                            {{-- separator antar group (jaga baseline ruled tetap konsisten) --}}
                            <div class="ruled-line" style="color: #000;">&nbsp;</div>
                        @endif
                    @endforeach
                @endif

                @if(!$isAdjust && !$isProdukBaru)
                    @if(str_contains($arsip->jenis_pengajuan, 'Mutasi'))
                        @foreach($arsip->mutasiItems as $m)
                            <div class="ruled-line">{{ strtoupper($m->type) }}: {{ $m->product_code }} - {{ $m->product_name }} ({{ $m->qty }})</div>
                        @endforeach
                    @elseif($arsip->jenis_pengajuan === 'Bundel')
                        @foreach($arsip->bundelItems as $b)
                            <div class="ruled-line">DOKUMEN: {{ $b->no_doc }} (Qty: {{ $b->qty }})</div>
                        @endforeach
                    @endif
                @endif

                @for($i = 0; $i < $keteranganLines; $i++)
                    <div class="ruled-line">&nbsp;</div>
                @endfor
            </div>
        </div>

        {{-- ─── TINDAKAN ────────────────────────────────────────────── --}}
        <div style="margin-top: 6px;">
            <div class="section-title">TINDAKAN</div>
            <div class="section-note">(DIISI OLEH DEPARTEMEN IT)*</div>

            @if($isAdjust)
                @php
                    $tindakanRows = $arsip->relationLoaded('tindakanItems')
                        ? $arsip->tindakanItems
                        : $arsip->tindakanItems()->get();

                    if ($tindakanRows->isEmpty() && (
                        !empty($arsip->tindakan_in) || !empty($arsip->ket_tindakan_in) ||
                        !empty($arsip->tindakan_out) || !empty($arsip->ket_tindakan_out)
                    )) {
                        $tindakanRows = collect([(object)[
                            'tindakan_in'      => $arsip->tindakan_in,
                            'ket_tindakan_in'  => $arsip->ket_tindakan_in,
                            'tindakan_out'     => $arsip->tindakan_out,
                            'ket_tindakan_out' => $arsip->ket_tindakan_out,
                        ]]);
                    }
                    $tindakanRowCount = max(2, $tindakanRows->count());
                @endphp
                <table class="main-table" style="margin-top: 5px;">
                    <thead>
                        <tr>
                            <th style="width: 20%;">IN</th>
                            <th style="width: 30%;">KETERANGAN</th>
                            <th style="width: 20%;">OUT</th>
                            <th style="width: 30%;">KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < $tindakanRowCount; $i++)
                            @php $row = $tindakanRows[$i] ?? null; @endphp
                            <tr>
                                <td style="height: 22px; font-weight: 700;">{{ $row->tindakan_in ?? '' }}</td>
                                <td class="cell-left">{{ $row->ket_tindakan_in ?? '' }}</td>
                                <td style="font-weight: 700;">{{ $row->tindakan_out ?? '' }}</td>
                                <td class="cell-left">{{ $row->ket_tindakan_out ?? '' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            @endif

            <div style="margin-top: 4px; margin-bottom: 8px;">
                @if(!empty(trim((string) $arsip->tindakan)))
                    @php $tindLines = preg_split('/\r\n|\r|\n/', trim((string) $arsip->tindakan)); @endphp
                    @foreach($tindLines as $idx => $tl)
                        @php $t = trim($tl); @endphp
                        <div class="ruled-line" style="font-weight: 700;">{!! $idx === 0 ? 'TINDAKAN: ' : '' !!}{!! $t === '' ? '&nbsp;' : e($t) !!}</div>
                    @endforeach
                @endif
                @if(!empty(trim((string) $arsip->catatan_it)))
                    @php $catLines = preg_split('/\r\n|\r|\n/', trim((string) $arsip->catatan_it)); @endphp
                    @foreach($catLines as $idx => $cl)
                        @php $c = trim($cl); @endphp
                        <div class="ruled-line" style="font-weight: 700;">{!! $idx === 0 ? 'CATATAN IT: ' : '' !!}{!! $c === '' ? '&nbsp;' : e($c) !!}</div>
                    @endforeach
                @endif
                @for($i = 0; $i < $tindakanLines; $i++)
                    <div class="ruled-line">&nbsp;</div>
                @endfor
            </div>
        </div>

        {{-- ─── FOOTER: Place/Date + Signature (anchored bottom) ──── --}}
        <div class="footer-section-wrap">
            @php
                $kotaBa = \App\Models\Setting::get('kota_ba', 'PASURUAN');
                \Carbon\Carbon::setLocale('id');
                $tglSign = \Carbon\Carbon::parse($arsip->tgl_pengajuan ?: $arsip->created_at)->translatedFormat('d F Y');
            @endphp
            <div class="footer-place-date">{{ strtoupper($kotaBa) }}, {{ $tglSign }}</div>

            @php
                $renderSig = function ($sig) use ($arsip) {
                    if (!$sig) {
                        return '<div class="sig-stamp"><div class="sig-pending">[ Menunggu TTD ]</div></div>';
                    }
                    $qr = \App\Services\QrSignatureService::renderSignatureQrDataUri($arsip, $sig, 150);
                    $html = '<div class="sig-stamp">';
                    if ($qr) {
                        $html .= '<img src="' . $qr . '" alt="TTD">';
                    }
                    $html .= '<div class="sig-name">' . e($sig->signer_name) . '</div>';
                    $html .= '<div class="sig-ts">' . optional($sig->signed_at)->format('d/m/Y H:i') . ' WIB</div>';
                    // Delegasi: kalau signer TTD sebagai wakil dari user lain, tunjukkan siapa aslinya.
                    if ($sig->delegated_from_id) {
                        $orig = $sig->delegatedFrom;
                        $origName = $orig ? $orig->name : 'user asal';
                        $html .= '<div class="sig-delegate">↩ Mewakili: ' . e($origName) . '</div>';
                    }
                    $html .= '</div>';
                    return $html;
                };
                // Eager-load delegatedFrom untuk semua signature supaya render efficient.
                $arsip->loadMissing('signatures.delegatedFrom');
                $sigPemohon    = $arsip->signatureFor('Pemohon');
                $sigAccounting = $arsip->signatureFor('Accounting');
                $sigIT         = $arsip->signatureFor('Departemen IT');
                $sigSPV        = $arsip->signatureFor('SPV');
                $sigKabag      = $arsip->signatureFor('Kabag');
                $sigManager    = $arsip->signatureFor('Manager');
            @endphp

            <table class="signature-table">
                <tr>
                    <th style="width: 20%;">Diajukan Oleh,</th>
                    @if($isAdjust)
                        <th colspan="2" style="width: 40%;">Diketahui Oleh,</th>
                        <th style="width: 20%;">Disetujui Oleh,</th>
                    @else
                        <th colspan="3" style="width: 60%;">Diketahui Oleh,</th>
                    @endif
                    <th style="width: 20%;">Dikerjakan Oleh,</th>
                </tr>
                <tr>
                    <td>
                        {!! $renderSig($sigPemohon) !!}
                        <div class="sig-role">Pemohon</div>
                        <div class="sig-hint">(Tanda Tangan dan Nama Jelas)</div>
                    </td>
                    @if($isAdjust)
                        <td>
                            {!! $renderSig($sigSPV ?: $sigKabag) !!}
                            <div class="sig-role">SPV / Kabag</div>
                            <div class="sig-hint">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                        <td>
                            {!! $renderSig($sigManager) !!}
                            <div class="sig-role">Manager</div>
                            <div class="sig-hint">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                        <td>
                            {!! $renderSig($sigAccounting) !!}
                            <div class="sig-role">Accounting</div>
                            <div class="sig-hint">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                    @else
                        <td>
                            {!! $renderSig($sigSPV) !!}
                            <div class="sig-role">SPV</div>
                            <div class="sig-hint">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                        <td>
                            {!! $renderSig($sigKabag) !!}
                            <div class="sig-role">Kabag</div>
                            <div class="sig-hint">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                        <td>
                            {!! $renderSig($sigManager) !!}
                            <div class="sig-role">Manager</div>
                            <div class="sig-hint">(Tanda Tangan dan Nama Jelas)</div>
                        </td>
                    @endif
                    <td>
                        {!! $renderSig($sigIT) !!}
                        <div class="sig-role">Departemen IT</div>
                        <div class="sig-hint">(Tanda Tangan dan Nama Jelas)</div>
                    </td>
                </tr>
            </table>

            <div class="doc-footer-note">
                {{ $isAdjust ? 'Adjustment' : ($isProdukBaru ? 'Produk Baru' : 'System Odoo') }} / 01 / 15 Januari 2025
            </div>

            @if($arsip->signatures->count() > 0)
                @php $signedNames = $arsip->signatures->pluck('role_label')->all(); @endphp
                <div class="ttd-validation">
                    <span class="ttd-text">
                        <span class="ttd-check">✓</span>
                        Dokumen ditandatangani secara digital ({{ implode(', ', $signedNames) }}). Scan QR di pojok kiri atas untuk verifikasi.
                    </span>
                    @if($arsip->verify_token)
                        <span class="ttd-token">#{{ \Illuminate\Support\Str::limit($arsip->verify_token, 10, '') }}</span>
                    @endif
                </div>
            @endif
        </div>{{-- /.footer-section-wrap --}}
    </div>{{-- /.print-container --}}

    @include('partials._print_footer')
</body>

</html>
