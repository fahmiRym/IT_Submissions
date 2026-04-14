<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Print Draft Bundel - {{ $arsip->no_registrasi }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 8mm;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .print-container {
            width: 100%;
        }

        /* ── META BAR ── */
        .meta-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 3px 10px;
            margin-bottom: 6px;
            font-size: 8.5px;
            color: #444;
        }

        .meta-bar .meta-left {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .meta-bar .meta-item {
            display: flex;
            gap: 5px;
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

        /* ── FORM BLOCK ── */
        .form-block {
            height: 90mm;
            position: relative;
            margin-bottom: 2mm;
        }

        .wrapper {
            border: 2px solid #000;
            height: 88mm;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* ── HEADER ── */
        .header-table td {
            border: 1.5px solid #000;
        }

        .header-title {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        /* ── INFO BOX ── */
        .info-table td {
            border: 1px solid #000;
            font-size: 8.5px;
            padding: 2px 4px;
        }

        /* ── DATE ROW ── */
        .date-row {
            border-bottom: 1.5px solid #000;
            padding: 2px 8px;
            margin-bottom: 2px;
            font-weight: 600;
        }

        /* ── MAIN TABLE ── */
        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
        }

        .main-table th {
            font-size: 8.5px;
            font-weight: 700;
        }

        .main-table td {
            height: 16px;
        }

        /* ── SIGNATURE ── */
        .signature {
            display: flex;
            height: 30mm;
        }

        .sig-box {
            flex: 1;
            text-align: center;
            padding-top: 5mm;
            border-right: 1px solid #000;
        }

        .sig-box:last-child {
            border-right: none;
        }

        .sig-title {
            font-size: 9px;
            font-weight: 700;
        }

        .sig-name {
            margin-top: 10mm;
            font-weight: 800;
            text-decoration: underline;
        }

        .sig-note {
            font-size: 8px;
            color: #555;
            font-style: italic;
        }

        /* ── CUT LINE ── */
        .cut-line {
            border-bottom: 1px dashed #aaa;
            position: absolute;
            bottom: -1mm;
            width: 100%;
        }

        .cut-line::after {
            content: "✄";
            position: absolute;
            left: 0;
            top: -8px;
            font-size: 12px;
            color: #999;
        }

        /* ── PRINT ── */
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body onload="window.print()">
<div class="print-container">

    {{-- ── PRINT BUTTON ── --}}
    <div class="no-print" style="text-align: right; margin-bottom: 8px;">
        <button onclick="window.print()"
            style="padding: 7px 18px; background: #1a1a1a; color: #fff; border: none; cursor: pointer; border-radius: 5px; font-weight: 700; font-size: 12px;">
            🖨️ Cetak
        </button>
    </div>

    {{-- ── META BAR: Printed date + User ── --}}
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

    @php
        $allBundels = collect($arsip->bundelItems ?? []);
        $chunks = $allBundels->chunk(5);
        $totalChunks = $chunks->count();
        $displayFormCount = max(3, (int)(ceil($totalChunks / 3) * 3));
    @endphp

    @for ($k = 0; $k < $displayFormCount; $k++)
        @php
            $currentItems = $chunks->get($k) ?? collect([]);
        @endphp

        <div class="form-block">
            <div class="wrapper">

                {{-- HEADER --}}
                <table class="header-table">
                    <tr>
                        {{-- Kiri: No Registrasi + QR Code --}}
                        <td style="width: 25%; padding: 4px; vertical-align: top;">
                            <div style="font-size: 7.5px; color: #666;">No. Registrasi:</div>
                            <div style="font-weight: 800; font-size: 10px; margin-top: 1px;">
                                {{ $arsip->no_registrasi }}
                            </div>
                            @if ($arsip->no_doc)
                                <div style="font-size: 7.5px; color: #666; margin-top: 4px;">No. Dokumen:</div>
                                <div style="font-weight: 800; font-size: 9px; margin-top: 1px;">
                                    {{ $arsip->no_doc }}
                                </div>
                            @endif
                            {{-- Mini QR code --}}
                            <div id="qrcode-{{ $k }}" style="margin-top: 3px;"></div>
                        </td>

                        {{-- Tengah: Judul --}}
                        <td style="width: 50%; text-align: center; vertical-align: middle; padding: 4px;">
                            <div class="header-title">FORM PENGAJUAN ISI BUNDLE</div>
                            <div style="font-size: 8px; color: #555; margin-top: 2px;">
                                Form {{ $k + 1 }} dari {{ $displayFormCount }}
                            </div>
                        </td>

                        {{-- Kanan: Info Table --}}
                        <td style="width: 25%; padding: 0;">
                            <table class="info-table" style="border: none;">
                                <tr>
                                    <td style="width: 40%; border-top: none; border-left: none;">NO.FORM</td>
                                    <td style="border-top: none; border-right: none;">IJ. FR-QCE-021</td>
                                </tr>
                                <tr>
                                    <td style="border-left: none;">REV. KE</td>
                                    <td style="border-right: none;">REV. 01</td>
                                </tr>
                                <tr>
                                    <td style="border-bottom: none; border-left: none;">TGL EFEKTIF</td>
                                    <td style="border-bottom: none; border-right: none;">23 FEBRUARI 2023</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- DATE ROW --}}
                <div class="date-row">
                    Tanggal : <b>{{ \Carbon\Carbon::parse($arsip->tgl_pengajuan)->format('d-m-Y') }}</b>
                    &nbsp;&nbsp;&nbsp;
                    Pemohon : <b>{{ strtoupper($arsip->pemohon ?? $arsip->admin->name ?? '-') }}</b>
                </div>

                {{-- MAIN TABLE --}}
                <table class="main-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 5%;">NO</th>
                            <th rowspan="2" style="width: 16%;">SECTION</th>
                            <th rowspan="2" style="width: 16%;">BERAT STD</th>
                            <th colspan="2" style="width: 16%;">ISI</th>
                            <th rowspan="2" style="width: 15%;">BERAT TOTAL</th>
                            <th rowspan="2" style="width: 12%;">DIMENSI BUNDLE</th>
                            <th rowspan="2" style="width: 20%;">KETERANGAN</th>
                        </tr>
                        <tr>
                            <th>LAMA</th>
                            <th>BARU</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 5; $i++)
                            @php $item = $currentItems->values()->get($i); @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->no_doc ?? '' }}</td>
                                <td></td>
                                <td></td>
                                <td>{{ $item->qty ?? '' }}</td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left; padding-left: 5px;">
                                    {{ $item->keterangan ?? '' }}
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                {{-- SIGNATURE --}}
                <div class="signature">
                    <div class="sig-box">
                        <div class="sig-title">YANG MEMBUAT</div>
                        <div class="sig-name">{{ ucwords(mb_strtolower($arsip->admin->name ?? '')) }}</div>
                        <div class="sig-note">( TTD & Nama Jelas )</div>
                    </div>
                    <div class="sig-box">
                        <div class="sig-title">YANG MENYETUJUI</div>
                        <div class="sig-name">MANAGER PRODUCTION</div>
                        <div class="sig-note">( TTD & Nama Jelas )</div>
                    </div>
                    <div class="sig-box">
                        <div class="sig-title">YANG MENGETAHUI</div>
                        <div class="sig-name">EDP</div>
                        <div class="sig-note">( TTD & Nama Jelas )</div>
                    </div>
                </div>

            </div>{{-- .wrapper --}}

            @if (($k + 1) % 3 !== 0)
                <div class="cut-line"></div>
            @endif
        </div>{{-- .form-block --}}

    @endfor

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @for ($k = 0; $k < 9; $k++)
        if (document.getElementById('qrcode-{{ $k }}')) {
            new QRCode(document.getElementById('qrcode-{{ $k }}'), {
                text: "{{ $arsip->no_registrasi }}",
                width: 36,
                height: 36,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.L
            });
        }
        @endfor
    });
</script>
</body>

</html>