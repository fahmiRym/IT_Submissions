<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lampiran — {{ $lampiran->original_name }}</title>
    <style>
        @page { size: A4 portrait; margin: 20mm 18mm; }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
        }
        .placeholder-card {
            border: 2px dashed #f59e0b;
            border-radius: 8px;
            padding: 14mm 12mm;
            background: #fffbeb;
        }
        .title-bar {
            border-bottom: 2px solid #1e293b;
            padding-bottom: 4mm;
            margin-bottom: 6mm;
        }
        .label-tag {
            display: inline-block;
            background: #f59e0b;
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 1px;
            padding: 3px 10px;
            border-radius: 3px;
            text-transform: uppercase;
            margin-bottom: 4mm;
        }
        h1 {
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 2mm;
            color: #92400e;
        }
        .sub {
            font-size: 11px;
            color: #475569;
            font-style: italic;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6mm;
        }
        .meta-table td {
            padding: 3mm 2mm;
            border-bottom: 1px solid #fde68a;
            vertical-align: top;
        }
        .meta-table td.label {
            width: 38mm;
            font-weight: 700;
            color: #92400e;
        }
        .reason-box {
            margin-top: 6mm;
            padding: 5mm 6mm;
            background: #fff;
            border-left: 4px solid #dc2626;
            border-radius: 4px;
        }
        .reason-box .reason-title {
            font-size: 10px;
            font-weight: 800;
            color: #dc2626;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2mm;
        }
        .reason-box .reason-text {
            font-size: 11px;
            color: #1e293b;
        }
        .hint {
            margin-top: 6mm;
            padding: 4mm 5mm;
            background: #dbeafe;
            border-radius: 4px;
            font-size: 10px;
            color: #1e40af;
        }
        .hint .bi {
            font-weight: 800;
            margin-right: 2mm;
        }
    </style>
</head>
<body>
<div class="placeholder-card">
    <div class="title-bar">
        <span class="label-tag">Lampiran Terlampir</span>
        <h1>{{ $lampiran->original_name ?? 'Lampiran.pdf' }}</h1>
        <div class="sub">Lampiran ini ter-record di sistem tetapi tidak dapat di-merge inline ke draft.</div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">No. Registrasi</td>
            <td>{{ $arsip->no_registrasi ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">File</td>
            <td>{{ $lampiran->original_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Ukuran</td>
            <td>{{ $lampiran->file_size ? number_format($lampiran->file_size / 1024, 1) . ' KB' : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah Halaman</td>
            <td>{{ $lampiran->page_count ?? 'tidak diketahui' }}</td>
        </tr>
        <tr>
            <td class="label">Hash (sha256)</td>
            <td style="font-family: monospace; font-size: 9px; word-break: break-all;">
                {{ $lampiran->file_hash ?? '-' }}
            </td>
        </tr>
        <tr>
            <td class="label">Tanggal Upload</td>
            <td>{{ optional($lampiran->created_at)->format('d M Y, H:i') ?? '-' }} WIB</td>
        </tr>
        @if(!empty($lampiran->keterangan))
        <tr>
            <td class="label">Keterangan</td>
            <td>{{ $lampiran->keterangan }}</td>
        </tr>
        @endif
    </table>

    <div class="reason-box">
        <div class="reason-title">Mengapa tidak ter-merge?</div>
        <div class="reason-text">{{ $reason }}</div>
    </div>

    <div class="hint">
        <b>Solusi:</b> Buka modal <b>Kelola Lampiran</b> di sistem untuk men-download file asli. Atau, untuk
        re-upload tanpa proteksi: buka PDF di aplikasi reader → Print → pilih "Microsoft Print to PDF" /
        "Save as PDF" → simpan ulang → upload kembali.
    </div>
</div>
</body>
</html>
