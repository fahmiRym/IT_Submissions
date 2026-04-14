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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
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
            padding: 6px 8px;
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
                padding: 0;
            }

            .footer-section {
                page-break-inside: avoid;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="print-container">

        {{-- ── PRINT BUTTON (hidden on print) ── --}}
        <div class="no-print" style="text-align: right; margin-bottom: 8px;">
            <button onclick="window.print()"
                style="padding: 7px 18px; background: #1a1a1a; color: #fff; border: none; cursor: pointer; border-radius: 5px; font-weight: 700; font-size: 12px;">
                🖨️ Cetak
            </button>
        </div>

        @php
            $isAdjust = $arsip->jenis_pengajuan === 'Adjust';
            $title = $isAdjust ? 'BERITA ACARA PENGAJUAN ADJUSTMENT' : 'BERITA ACARA PENGAJUAN SYSTEM ODOO';
            $adjustItemsCount = isset($arsip->adjustItems) ? count($arsip->adjustItems) : 0;
            $isCompact = $isAdjust && $adjustItemsCount > 9;
        @endphp

        @if($isCompact)
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
        @endif

        {{-- ── META BAR: Printed date + User ── --}}
        <!-- <div class="meta-bar">
            <div class="meta-left">
                <div class="meta-item">
                    <span class="meta-label">Printed date</span>
                    <span class="meta-value">{{ \Carbon\Carbon::now()->format('d/m/Y H.i.s') }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">User:</span>
                    <span class="meta-value">{{ auth()->user()->name ?? '-' }}</span>
                </div>
            </div>
            <div style="font-size: 8px; color: #999; font-style: italic;">{{ $arsip->no_registrasi }}</div>
        </div> -->

        {{-- ── DOCUMENT HEADER ── --}}
        <div class="doc-header">
            {{-- Spacer kiri agar title tetap center --}}
            <div style="width: 65px; flex-shrink: 0;"></div>

            <div class="doc-header-center">
                <div class="header-title">{{ $title }}</div>
                <div class="header-doc">
                    No. Dokumen :
                    <span
                        style="display:inline-block; border-bottom: 1.5px solid #000; min-width: 220px; padding-bottom: 1px;">
                        {{ $arsip->no_doc ?? '' }}
                    </span>
                </div>
                <div class="header-doc-note">(DIISI OLEH DEPARTEMEN IT)*</div>
            </div>

            {{-- QR Code kanan atas --}}
            <div class="qr-wrapper">
                <div id="qrcode"></div>
                <div class="qr-label">{{ Str::limit($arsip->no_registrasi, 14, '') }}</div>
            </div>
        </div>

        <hr class="separator">

        {{-- ── INFO TABLE ── --}}
        <table class="info-table">
            <tr>
                <td style="width: 200px;">PERIHAL</td>
                <td style="width: 10px;">:</td>
                <td style="border-bottom: 1.5px solid #000;">
                    {{ strtoupper(str_replace('_', ' ', $arsip->jenis_pengajuan)) }}
                </td>
            </tr>
            <tr>
                <td>PEMOHON</td>
                <td>:</td>
                <td style="border-bottom: 1.5px solid #000;">
                    {{ strtoupper($arsip->pemohon ?? $arsip->admin->name) }}
                </td>
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

        {{-- ── CONTENT AREA ── --}}
        <div style="margin-top: -3px; position: relative;">
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
                        @for($i = 0; $i < max(4, count($adjustItems)); $i++)
                            <tr>
                                <td style="height: 18px;">{{ $adjustItems[$i]->product_code ?? '' }}</td>
                                <td style="text-align: left;">{{ $adjustItems[$i]->product_name ?? '' }}</td>
                                <td></td>
                                <td></td>
                                <td>{{ isset($adjustItems[$i]) ? (($adjustItems[$i]->qty_in ?? 0) - ($adjustItems[$i]->qty_out ?? 0)) : '' }}
                                </td>
                                <td>{{ isset($adjustItems[$i]) ? ((($adjustItems[$i]->qty_in ?? 0) > 0) ? 'IN' : 'OUT') : '' }}
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
                <div style="font-weight: 800; margin-top: 5px; height: 18.5px;">CATATAN:</div>
            @endif

            @php
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
            @endphp

            <div class="ruled"
                style="min-height: {{ $keteranganLines * 23.5 }}px; margin-top: 2px; text-align: justify;">
                @if(!empty(trim($arsip->keterangan)))
                    <div style="white-space: pre-wrap;">{{ trim($arsip->keterangan) }}</div>
                @endif
                @if(!empty(trim($arsip->no_transaksi)))
                    <div style="column-width: 200px; column-gap: 20px; white-space: pre-wrap; page-break-inside: avoid;">
                        {{ trim($arsip->no_transaksi) }}
                    </div>
                @endif
                @if(!$isAdjust)
                    @if(str_contains($arsip->jenis_pengajuan, 'Mutasi'))
                        @foreach($arsip->mutasiItems as $m)
                            <div>{{ strtoupper($m->type) }}: {{ $m->product_code }} - {{ $m->product_name }} ({{ $m->qty }})</div>
                        @endforeach
                    @elseif($arsip->jenis_pengajuan === 'Bundel')
                        @foreach($arsip->bundelItems as $b)
                            <div>DOKUMEN: {{ $b->no_doc }} (Qty: {{ $b->qty }})</div>
                        @endforeach
                    @endif
                @endif
            </div>
        </div>

        {{-- ── TINDAKAN ── --}}
        <div style="margin-top: 14px; display: flex; flex-direction: column; flex-grow: 1;">
            <div>
                <div style="font-weight: 800;">TINDAKAN</div>
                <div style="font-size: 9px; font-style: italic; font-weight: 700; color: #555;">(DIISI OLEH DEPARTEMEN
                    IT)*</div>
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
                        @for($j = 0; $j < 3; $j++)
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

            <div class="ruled"
                style="flex-grow: 1; min-height: {{ $tindakanLines * 23.5 }}px; margin-top: 5px; margin-bottom: 18px;">
            </div>
        </div>

        {{-- ── FOOTER: Date + Signature ── --}}
        <div class="footer-section" style="page-break-inside: avoid; break-inside: avoid;">
            <div style="margin-bottom: 5px; font-weight: 800;">
                PASURUAN,
                {{ $isAdjust ? '____________________' : \Carbon\Carbon::parse($arsip->created_at)->translatedFormat('d F Y') }}
            </div>

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
                        <div style="font-weight: 800; font-size: 10px;">Pemohon</div>
                        <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                    </td>
                    @if($isAdjust)
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
                    @else
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
                    @endif
                    <td>
                        <div style="font-weight: 800; font-size: 10px;">Departemen IT</div>
                        <div style="font-size: 8px; font-style: italic;">(Tanda Tangan dan Nama Jelas)</div>
                    </td>
                </tr>
            </table>

            <div class="doc-footer-note">
                {{ $isAdjust ? 'Adjustment' : 'System Odoo' }} / 01 / 15 Januari 2025
            </div>
        </div>
        <div class="meta-bar">
            <div class="meta-left">
                <div class="meta-item">
                    <span class="meta-label">Printed date</span>
                    <span class="meta-value">{{ \Carbon\Carbon::now()->format('d/m/Y H.i.s') }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">User:</span>
                    <span class="meta-value">{{ auth()->user()->name ?? '-' }}</span>
                </div>
            </div>
            <div style="font-size: 8px; color: #999; font-style: italic;">{{ $arsip->no_registrasi }}</div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new QRCode(document.getElementById("qrcode"), {
                text: "{{ $arsip->no_registrasi }}",
                width: 62,
                height: 62,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        });
    </script>
</body>

</html>