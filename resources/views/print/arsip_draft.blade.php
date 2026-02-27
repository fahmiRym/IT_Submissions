<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: portrait; margin: 10mm; }
        html, body {
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
            min-height: 98vh;
            padding: 10px;
            box-sizing: border-box;
        }
        .header-title {
            font-size: 16px; 
            font-weight: 800; 
            text-align: center; 
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header-doc { 
            text-align: center; 
            margin-bottom: 20px; 
            font-weight: 700; 
            font-size: 11px;
        }
        .info-table { 
            border: none; 
            margin-bottom: 5px; 
            width: 100%; 
        }
        .info-table td { 
            border: none; 
            padding: 3px 2px; 
            text-align: left; 
            vertical-align: top;
            font-weight: 700;
        }
        .dotted-line {
            width: 100%; 
            border-bottom: 1.5px solid #000; 
            height: 22px;
        }
        /* Table Styles for Adjust and Tindakan */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-bottom: 5px;
            table-layout: fixed;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 10px;
        }
        .main-table th {
            font-weight: 800;
            text-transform: uppercase;
        }
        
        .signature-table {
            width: 100%; 
            border: 1.5px solid #000; 
            table-layout: fixed;
            border-collapse: collapse;
        }
        .signature-table th, .signature-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        .signature-table th {
            font-size: 10px;
            font-weight: 800;
        }
        .signature-table td {
            height: 100px;
            vertical-align: bottom;
        }
        .footer-section {
            margin-top: auto; 
            width: 100%;
        }
        @media print {
            body { 
                margin: 0; 
                padding: 0; 
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-container { 
                display: flex; 
                flex-direction: column;
                min-height: 98vh; 
                padding: 0; 
            }
            .footer-section {
                margin-top: auto !important; 
                page-break-inside: avoid;
            }
            button { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
<div class="print-container">

    <!-- Print Button -->
    <div style="text-align: right; margin-bottom: 10px;" class="no-print">
        <button onclick="window.print()" style="padding: 8px 16px; background: #000; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">Cetak</button>
    </div>

    @php
        $isAdjust = $arsip->jenis_pengajuan === 'Adjust';
        $title = $isAdjust ? 'BERITA ACARA PENGAJUAN ADJUSTMENT' : 'BERITA ACARA PENGAJUAN SYSTEM ODOO';
        $adjustItemsCount = isset($arsip->adjustItems) ? count($arsip->adjustItems) : 0;
        $isCompact = $isAdjust && $adjustItemsCount > 9;
    @endphp

    @if($isCompact)
    <style>
        .main-table th, .main-table td { padding: 2px; }
        .main-table td { height: 14px !important; }
        .info-table { margin-bottom: 2px; }
        .header-doc { margin-bottom: 5px; }
        .signature-table td { height: 75px !important; }
        .signature-table { margin-top: -5px; }
        .print-container { padding-top: 5px; padding-bottom: 5px; }
    </style>
    @endif

    <div style="font-size: 10px; font-weight: 800; margin-bottom: 5px;">
        {{ $arsip->no_registrasi }}
    </div>

    <div class="header-title">{{ $title }}</div>
    <div class="header-doc">
        No. Dokumen : <span style="display:inline-block; border-bottom: 1.5px solid #000; width: 250px;">{{ $arsip->no_doc ?? '' }}</span><br>
        <div style="font-size: 9px; font-style: italic; margin-top:2px; font-weight: 800;">(DIISI OLEH DEPARTEMEN IT)*</div>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 200px;">PERIHAL</td>
            <td style="width: 10px;">:</td>
            <td style="border-bottom: 1.5px solid #000;">{{ strtoupper(str_replace('_', ' ', $arsip->jenis_pengajuan)) }}</td>
        </tr>
        <tr>
            <td>PEMOHON</td>
            <td>:</td>
            <td style="border-bottom: 1.5px solid #000;">{{ strtoupper($arsip->pemohon ?? $arsip->admin->name) }}</td>
        </tr>
        <tr>
            <td>DEPARTEMEN / UNIT KERJA</td>
            <td>:</td>
            <td style="border-bottom: 1.5px solid #000;">
                {{ strtoupper($arsip->department->name ?? '') }} / {{ strtoupper($arsip->unit->name ?? '') }}
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px;">DESKRIPSI PERMASALAHAN</td>
            <td style="padding-top: 10px;">:</td>
            <td></td>
        </tr>
    </table>

    <div style="margin-top: -3px; position: relative;">
        <!-- If Adjust, show items table -->
        @if($isAdjust)
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
                    @php $adjustItems = $arsip->adjustItems ?? []; @endphp
                    @for($i=0; $i<max(4, count($adjustItems)); $i++)
                    <tr>
                        <td style="height: 18px;">{{ $adjustItems[$i]->product_code ?? '' }}</td>
                        <td style="text-align: left;">{{ $adjustItems[$i]->product_name ?? '' }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ isset($adjustItems[$i]) ? (($adjustItems[$i]->qty_in ?? 0) - ($adjustItems[$i]->qty_out ?? 0)) : '' }}</td>
                        <td>{{ isset($adjustItems[$i]) ? ((($adjustItems[$i]->qty_in ?? 0) > 0) ? 'IN' : 'OUT') : '' }}</td>
                    </tr>
                    @endfor
                </tbody>
            </table>
            <div style="font-weight: 800; margin-top: 5px; height: 18.5px;">CATATAN:</div>
        @endif

        @php 
            $keteranganLines = 6;
            $tindakanLines = 6;

            // Jika item adjust sangat banyak (makan banyak ruang baris > 4)
            if ($isAdjust) {
                $excessItems = max(0, $adjustItemsCount - 4);
                // 1 item roughly equals 1.25 baris kosong. Kita kurangi baris Keterangan dan Tindakan
                $linesToRemove = (int)ceil($excessItems * 1.2);
                
                // Sebisa mungkin kurangi rata
                if ($linesToRemove > 0) {
                    $keteranganLines = max(1, $keteranganLines - ceil($linesToRemove / 2));
                    $tindakanLines = max(1, $tindakanLines - floor($linesToRemove / 2));
                }
                
                // Jika masih butuh dikurangi lebih banyak lagi agar paksa 1 page
                if ($excessItems >= 8) {
                    $keteranganLines = 1;
                    $tindakanLines = 1;
                }
            }
        @endphp

        <div class="ruled-background" style="
            min-height: {{ $keteranganLines * 23.5 }}px;
            background-image: repeating-linear-gradient(to bottom, transparent 0, transparent 22px, #000 22px, #000 23.5px);
            line-height: 23.5px;
            font-size: 11px;
            padding-top: 2px;
            margin-top: 2px;
            text-align: justify;
            /* Allow elements to break within this block if needed, but the footer won't be pushed out */
        ">
            @if(!empty(trim($arsip->keterangan)))
                <div style="white-space: pre-wrap; margin-bottom: 0;">{{ trim($arsip->keterangan) }}</div>
            @endif

            @if(!empty(trim($arsip->no_transaksi)))
                <!-- Menggunakan column-width: 200px akan otomatis memecah konten jadi beberapa kolom jika lebar mencukupi -->
                <div style="column-width: 200px; column-gap: 20px; white-space: pre-wrap; margin-top: 0; page-break-inside: avoid;">{{ trim($arsip->no_transaksi) }}</div>
            @endif

            @if(!$isAdjust)
                 @if(str_contains($arsip->jenis_pengajuan, 'Mutasi'))
                    @foreach($arsip->mutasiItems as $m)
                        <div style="margin: 0;">{{ strtoupper($m->type) }}: {{ $m->product_code }} - {{ $m->product_name }} ({{ $m->qty }})</div>
                    @endforeach
                 @elseif($arsip->jenis_pengajuan === 'Bundel')
                    @foreach($arsip->bundelItems as $b)
                        <div style="margin: 0;">DOKUMEN: {{ $b->no_doc }} (Qty: {{ $b->qty }})</div>
                    @endforeach
                 @endif
            @endif
        </div>
    </div>

    <!-- Tindakan Section -->
    <div style="margin-top: 15px; display: flex; flex-direction: column; flex-grow: 1;">
        <div>
            <div style="font-weight: 800;">TINDAKAN</div>
            <div style="font-size: 9px; font-style: italic; font-weight: 800;">(DIISI OLEH DEPARTEMEN IT)*</div>
        </div>
        
        @if($isAdjust)
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
                    @for($j=0; $j<3; $j++)
                    <tr>
                        <td style="height: 18px;"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        @endif

        <div style="
            flex-grow: 1;
            min-height: {{ $tindakanLines * 23.5 }}px; 
            background-image: repeating-linear-gradient(to bottom, transparent 0, transparent 22px, #000 22px, #000 23.5px);
            margin-top: 5px;
            margin-bottom: 20px;
        "></div>
    </div>

    <div class="footer-section" style="page-break-inside: avoid; break-inside: avoid;">
        <div style="margin-bottom: 5px; font-weight: 800;">
            PASURUAN, {{ \Carbon\Carbon::parse($arsip->created_at)->translatedFormat('d F Y') }}
        </div>

        <table class="signature-table" style="page-break-inside: avoid; break-inside: avoid;">
            <tr>
                <th style="width: 20%;">Diajukan Oleh,</th>
                <th colspan="3" style="width: 60%;">Diketahui Oleh,</th>
                <th style="width: 20%;">Dikerjakan Oleh,</th>
            </tr>
            <tr>
                <td>
                    <div style="font-weight: 800; text-decoration: underline; margin-bottom: 2px;"></div>
                    <div style="font-weight: 800; font-size: 10px;">Pemohon</div>
                    <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                </td>
                <td style="width: 20%;">
                    <div style="font-weight: 800; text-decoration: underline; margin-bottom: 2px;"></div>
                    <div style="font-weight: 800; font-size: 10px;">SPV</div>
                    <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                </td>
                <td style="width: 20%;">
                    <div style="font-weight: 800; text-decoration: underline; margin-bottom: 2px;"></div>
                    <div style="font-weight: 800; font-size: 10px;">Kabag</div>
                    <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                </td>
                <td style="width: 20%;">
                    <div style="font-weight: 800; text-decoration: underline; margin-bottom: 2px;"></div>
                    <div style="font-weight: 800; font-size: 10px;">Manager</div>
                    <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                </td>
                <td>
                    <div style="font-weight: 800; text-decoration: underline; margin-bottom: 2px;"></div>
                    <div style="font-weight: 800; font-size: 10px;">Departemen IT</div>
                    <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                </td>
            </tr>
        </table>
        <div style="text-align: right; font-size: 9px; font-weight: 800; margin-top: 5px;">
            {{ $isAdjust ? 'Adjustment' : 'System Odoo' }} / 01 / 15 Januari 2025
        </div>
    </div>
</div>
</body>
</html>
