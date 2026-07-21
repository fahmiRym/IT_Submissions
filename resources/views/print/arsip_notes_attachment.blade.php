<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lampiran Catatan Personal — {{ $arsip->no_registrasi ?? '' }}</title>
    <style>
        @page { margin: 18mm 15mm 16mm 15mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        .doc-head {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .doc-head .h-title {
            font-size: 13pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-head .h-sub {
            font-size: 9pt;
            color: #444;
            margin: 3px 0 0;
        }
        .doc-head .h-meta {
            margin-top: 6px;
            font-size: 9pt;
        }
        .doc-head .h-meta .row { display: table; width: 100%; }
        .doc-head .h-meta .cell-label { display: table-cell; width: 130px; font-weight: 700; }
        .doc-head .h-meta .cell-val { display: table-cell; }
        .pill {
            display: inline-block;
            background: #eef2ff;
            color: #312e81;
            padding: 1px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 9pt;
        }

        .note-card {
            border: 1px solid #d4d4d8;
            border-left: 4px solid #f59e0b;
            background: #fffdf7;
            padding: 8px 10px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .note-card .author-line {
            font-weight: 800;
            font-size: 9.5pt;
            color: #0f172a;
            margin-bottom: 4px;
            border-bottom: 1px solid #f4e4bc;
            padding-bottom: 3px;
        }
        .note-card .author-line .role-pill {
            display: inline-block;
            font-size: 7pt;
            background: #e0e7ff;
            color: #3730a3;
            padding: 1px 5px;
            border-radius: 3px;
            font-weight: 800;
            letter-spacing: 0.04em;
            margin-left: 4px;
        }
        .note-card .author-line .meta {
            float: right;
            font-weight: 500;
            color: #666;
            font-size: 8pt;
            font-family: 'Courier New', monospace;
        }
        .note-card .body {
            font-size: 9.5pt;
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
            color: #1e293b;
        }

        .footer-stamp {
            position: fixed;
            bottom: 6mm;
            left: 0; right: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 30px 10px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="doc-head">
        <h1 class="h-title">LAMPIRAN — CATATAN PERSONAL</h1>
        <p class="h-sub">Komentar &amp; klarifikasi tambahan dari pihak terkait pada dokumen ini.</p>
        <div class="h-meta">
            <div class="row">
                <div class="cell-label">No. Registrasi</div>
                <div class="cell-val"><span class="pill">{{ $arsip->no_registrasi ?? '—' }}</span></div>
            </div>
            <div class="row">
                <div class="cell-label">Jenis Pengajuan</div>
                <div class="cell-val">{{ str_replace('_', ' ', $arsip->jenis_pengajuan ?? '—') }}</div>
            </div>
            <div class="row">
                <div class="cell-label">Pemohon</div>
                <div class="cell-val">{{ optional($arsip->admin)->name ?? '—' }}</div>
            </div>
            <div class="row">
                <div class="cell-label">Jumlah Catatan</div>
                <div class="cell-val">{{ $personalNotes->count() }} catatan</div>
            </div>
        </div>
    </div>

    @if($personalNotes->isEmpty())
        <div class="empty-state">Tidak ada catatan personal pada dokumen ini.</div>
    @else
        @foreach($personalNotes as $i => $pn)
            <div class="note-card">
                <div class="author-line">
                    #{{ $i + 1 }} · {{ optional($pn->user)->name ?? '—' }}
                    @if(optional($pn->user)->role)
                        <span class="role-pill">{{ strtoupper($pn->user->role) }}</span>
                    @endif
                    <span class="meta">{{ $pn->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="body">{{ $pn->note }}</div>
            </div>
        @endforeach
    @endif

    <div class="footer-stamp">
        Lampiran ini di-generate otomatis dari sistem IT Submissions ·
        Dicetak {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name ?? 'System' }}
    </div>

</body>
</html>
