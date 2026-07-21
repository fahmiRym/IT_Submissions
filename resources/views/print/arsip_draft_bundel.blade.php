<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Print Draft Bundel - {{ $arsip->no_registrasi }}</title>
    <style>
        @page { size: A4 portrait; margin: 5mm 7mm; }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #000;
        }

        .print-container { width: 100%; }

        /* ─── PAGE WRAPPER (3 form per A4, forced page-break antar page) ── */
        .bundle-page {
            position: relative;
        }
        .bundle-page + .bundle-page {
            page-break-before: always;
        }

        /* ─── FORM BLOCK (3 per page) ───────────────────────────── */
        .form-block {
            position: relative;
            margin-bottom: 1mm;
        }
        .wrapper {
            border: 2px solid #000;
        }

        table { border-collapse: collapse; width: 100%; }

        /* ─── HEADER (compact agar 3 form muat) ─────────────────── */
        .header-table td { border: 1.5px solid #000; }
        .header-title {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-sub {
            font-size: 7.5px;
            color: #555;
            margin-top: 1px;
        }
        .header-meta-label { font-size: 7px; color: #666; }
        .header-meta-value { font-weight: 800; font-size: 9.5px; margin-top: 1px; }

        /* ─── INFO BOX (kanan atas) ──────────────────────────────── */
        .info-table td {
            border: 1px solid #000;
            font-size: 8px;
            padding: 1.5px 4px;
        }

        /* ─── DATE ROW ───────────────────────────────────────────── */
        .date-row {
            border-bottom: 1.5px solid #000;
            padding: 2px 8px;
            font-weight: 600;
            font-size: 9.5px;
        }

        /* ─── MAIN TABLE ─────────────────────────────────────────── */
        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
        }
        .main-table th {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            background: #f5f5f5;
        }
        .main-table td { height: 12px; font-size: 9px; }
        .main-table td.keterangan-cell {
            text-align: left;
            padding-left: 5px;
        }

        /* ─── SIGNATURE (NAMA di atas, TTD/QR di bawah) ─────────── */
        .signature-table {
            border-top: 1.5px solid #000;
            table-layout: fixed;
        }
        .signature-table td {
            border-right: 1px solid #000;
            text-align: center;
            vertical-align: top;
            padding: 3px 2px;
            width: 33.33%;
            height: 18mm;
            overflow: hidden;
        }
        .signature-table td:last-child { border-right: none; }
        .sig-title {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .sig-stamp {
            min-height: 9mm;
            text-align: center;
            line-height: 1;
            padding-top: 1mm;
        }
        .sig-stamp img {
            width: 34px;
            height: 34px;
            object-fit: contain;
            display: inline-block;
        }
        .sig-name {
            font-size: 10px;
            font-weight: 800;
            color: #000;
            text-decoration: underline;
            text-underline-offset: 2px;
            letter-spacing: 0.3px;
            margin: 1mm 0 0;
        }
        .sig-signer {
            font-size: 7.5px;
            color: #1e293b;
            font-style: italic;
            font-weight: 600;
            margin-top: 0;
        }
        .sig-ts {
            font-size: 6.5px;
            color: #475569;
            font-style: italic;
            margin-top: 1px;
        }
        .sig-delegate {
            display: inline-block;
            font-size: 6.5px;
            color: #92400e;
            font-weight: 700;
            font-style: italic;
            background: #fef3c7;
            border-radius: 2px;
            padding: 0 3px;
            margin-top: 1px;
            letter-spacing: 0.2px;
        }
        .sig-note {
            font-size: 7px;
            color: #555;
            font-style: italic;
            margin-top: 1mm;
        }

        /* ─── PER-FORM FOOTER (Dicetak pada... — IT Submissions) ─ */
        .form-footer {
            text-align: center;
            font-size: 7.5px;
            color: #475569;
            font-style: italic;
            padding: 1mm 0;
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.2;
        }
        .form-footer b { font-weight: 700; color: #1e293b; font-style: normal; }
        .form-footer .brand {
            font-weight: 800;
            color: #dc2626;
            font-style: italic;
            letter-spacing: 0.3px;
        }

        /* ─── CUT LINE ──────────────────────────────────────────── */
        .cut-line {
            border-bottom: 1px dashed #aaa;
            margin: 0 0 1mm 0;
            position: relative;
            height: 1px;
        }
        .cut-line .scissor {
            position: absolute;
            left: 0;
            top: -7px;
            font-size: 10px;
            color: #999;
        }

        /* ─── PRINT ─────────────────────────────────────────────── */
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>

<body onload="window.print()">
<div class="print-container">

    {{-- ── PRINT BUTTON (screen only) ────────────────────────────── --}}
    @if(empty($forPdf))
        <div class="no-print" style="text-align: right; margin-bottom: 6px;">
            <button onclick="window.print()"
                style="padding: 6px 16px; background: #1a1a1a; color: #fff; border: none; cursor: pointer; border-radius: 5px; font-weight: 700; font-size: 12px;">
                Cetak
            </button>
        </div>
    @endif

    @php
        // Pre-render server-side QR (dompdf-friendly)
        $qrDocVerify = \App\Services\QrSignatureService::renderDocumentQrDataUri($arsip, 120);

        // TTD digital roles
        $sigPemohon = $arsip->signatureFor('Pemohon');
        $sigManager = $arsip->signatureFor('Manager');
        $sigIT      = $arsip->signatureFor('Departemen IT');

        // Layout:
        //   [QR / wet-sign space]
        //   [ROLE LABEL underlined]      ← selalu tampil
        //   [signer name italic]          ← bila signed & beda dari role
        //   [↩ Mewakili: ORIGINAL NAME]   ← bila TTD sebagai delegasi
        //   [timestamp italic]            ← bila signed
        $renderSig = function ($sig, $roleLabel) use ($arsip) {
            $roleLabel = strtoupper(trim($roleLabel ?? ''));
            $html = '<div class="sig-stamp">';
            if ($sig) {
                $qr = \App\Services\QrSignatureService::renderSignatureQrDataUri($arsip, $sig, 130);
                if ($qr) $html .= '<img src="' . $qr . '" alt="TTD">';
            }
            $html .= '</div>';

            $html .= '<div class="sig-name">' . e($roleLabel) . '</div>';

            if ($sig) {
                $signerName = strtoupper(trim($sig->signer_name));
                if ($signerName !== '' && $signerName !== $roleLabel) {
                    $html .= '<div class="sig-signer">' . e($sig->signer_name) . '</div>';
                }
                if ($sig->delegated_from_id) {
                    $orig = $sig->delegatedFrom;
                    $origName = $orig ? $orig->name : 'user asal';
                    $html .= '<div class="sig-delegate">↩ Mewakili ' . e($origName) . '</div>';
                }
                $html .= '<div class="sig-ts">' . optional($sig->signed_at)->format('d/m/Y H:i') . ' WIB</div>';
            }
            return $html;
        };

        $arsip->loadMissing('signatures.delegatedFrom');

        \Carbon\Carbon::setLocale('id');
        $printedFooterUser = auth()->user()->name ?? 'System';
        $printedFooterDate = \Carbon\Carbon::now()->translatedFormat('j F Y, H:i');

        $allBundels = collect($arsip->bundelItems ?? []);
        $chunks = $allBundels->chunk(5);
        $totalChunks = $chunks->count();
        $displayFormCount = max(3, (int)(ceil($totalChunks / 3) * 3));
        $pageCount = (int) ceil($displayFormCount / 3);
    @endphp

    {{-- $displayFormCount selalu kelipatan 3, jadi tiap .bundle-page diisi tepat 3 form --}}
    @for ($p = 0; $p < $pageCount; $p++)
    <div class="bundle-page">
    @for ($idx = 0; $idx < 3; $idx++)
        @php
            $k = $p * 3 + $idx;
            $currentItems = $chunks->get($k) ?? collect([]);
        @endphp

        <div class="form-block">
            <div class="wrapper">

                {{-- ── HEADER ───────────────────────────────────────── --}}
                <table class="header-table">
                    <tr>
                        {{-- Kiri: No Reg + No Doc + QR Verifikasi --}}
                        <td style="width: 26%; padding: 4px; vertical-align: top;">
                            <div class="header-meta-label">No. Registrasi:</div>
                            <div class="header-meta-value">{{ $arsip->no_registrasi }}</div>
                            @if ($arsip->no_doc)
                                <div class="header-meta-label" style="margin-top: 3px;">No. Dokumen:</div>
                                <div class="header-meta-value" style="font-size: 9px;">{{ $arsip->no_doc }}</div>
                            @endif
                            @if($qrDocVerify)
                                <div style="margin-top: 3px;">
                                    <img src="{{ $qrDocVerify }}" alt="QR" style="width: 36px; height: 36px;">
                                </div>
                            @endif
                        </td>

                        {{-- Tengah: Judul --}}
                        <td style="width: 48%; text-align: center; vertical-align: middle; padding: 4px;">
                            <div class="header-title">FORM PENGAJUAN ISI BUNDLE</div>
                            <div class="header-sub">Form {{ $k + 1 }} dari {{ $displayFormCount }}</div>
                            @if($arsip->verify_token)
                                <div style="font-size: 7px; color: #1d4ed8; margin-top: 2px; font-weight: 700; letter-spacing: 0.5px;">
                                    SCAN QR UNTUK VERIFIKASI
                                </div>
                            @endif
                        </td>

                        {{-- Kanan: Info form --}}
                        <td style="width: 26%; padding: 0;">
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

                {{-- ── DATE ROW ──────────────────────────────────── --}}
                <div class="date-row">
                    Tanggal : <b>{{ \Carbon\Carbon::parse($arsip->tgl_pengajuan)->format('d-m-Y') }}</b>
                    &nbsp;&nbsp;&nbsp;
                    Pemohon : <b>{{ strtoupper($arsip->pemohon ?? $arsip->admin->name ?? '-') }}</b>
                </div>

                {{-- ── MAIN TABLE ───────────────────────────────── --}}
                <table class="main-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 5%;">NO</th>
                            <th rowspan="2" style="width: 16%;">SECTION</th>
                            <th rowspan="2" style="width: 14%;">BERAT STD</th>
                            <th colspan="2" style="width: 14%;">ISI</th>
                            <th rowspan="2" style="width: 14%;">BERAT TOTAL</th>
                            <th rowspan="2" style="width: 12%;">DIMENSI BUNDLE</th>
                            <th rowspan="2" style="width: 25%;">KETERANGAN</th>
                        </tr>
                        <tr>
                            <th style="width: 7%;">LAMA</th>
                            <th style="width: 7%;">BARU</th>
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
                                <td class="keterangan-cell">{{ $item->keterangan ?? '' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                {{-- ── SIGNATURE (table = dompdf reliable) ──────── --}}
                <table class="signature-table">
                    <tr>
                        <td>
                            <div class="sig-title">YANG MEMBUAT</div>
                            {!! $renderSig($sigPemohon, $arsip->pemohon ?? ($arsip->admin->name ?? '')) !!}
                            <div class="sig-note">( TTD &amp; Nama Jelas )</div>
                        </td>
                        <td>
                            <div class="sig-title">YANG MENYETUJUI</div>
                            {!! $renderSig($sigManager, 'Manager Production') !!}
                            <div class="sig-note">( TTD &amp; Nama Jelas )</div>
                        </td>
                        <td>
                            <div class="sig-title">YANG MENGETAHUI</div>
                            {!! $renderSig($sigIT, 'Departemen IT') !!}
                            <div class="sig-note">( TTD &amp; Nama Jelas )</div>
                        </td>
                    </tr>
                </table>

            </div>{{-- /.wrapper --}}

            {{-- Footer per-form (Dicetak pada... — IT Submissions) muncul di bawah TIAP form --}}
            <div class="form-footer">
                Dicetak pada <b>{{ $printedFooterDate }}</b> oleh <b>{{ $printedFooterUser }}</b>
                <span class="brand"> ~ IT Submissions ~</span>
            </div>

            @if ($idx < 2 && ($k + 1) < $displayFormCount)
                <div class="cut-line"><span class="scissor">✄</span></div>
            @endif
        </div>{{-- /.form-block --}}
    @endfor
    </div>{{-- /.bundle-page --}}
    @endfor

</div>{{-- /.print-container --}}
</body>

</html>
