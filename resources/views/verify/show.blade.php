<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi TTD Digital — {{ $arsip->no_registrasi }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-1: #4f46e5;
            --brand-2: #7c3aed;
            --ok: #15803d;
            --ok-soft: #dcfce7;
            --bad: #b91c1c;
            --slate: #475569;
            --muted: #94a3b8;
        }
        body {
            background: linear-gradient(135deg, #f5f7fb 0%, #ecfdf5 100%);
            font-family: 'Inter', system-ui, sans-serif;
            color: #0f172a;
            min-height: 100vh;
            padding: 24px 12px 96px;
        }
        .verify-wrap { max-width: 820px; margin: 0 auto; }

        /* Document badge top */
        .doc-pill {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            color: white; padding: 6px 14px; border-radius: 999px;
            font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        }

        /* CERT CARD ala Makarya One */
        .cert-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px 36px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.08), 0 8px 16px -8px rgba(15, 23, 42, 0.05);
            margin-top: 20px;
            position: relative;
            overflow: hidden;
        }
        .cert-card::before {
            content: '';
            position: absolute; top: 0; left: 0;
            width: 6px; height: 100%;
            background: linear-gradient(180deg, var(--brand-1), var(--brand-2));
        }
        .cert-icon-wrap {
            text-align: center;
            margin-bottom: 18px;
        }
        .cert-icon {
            width: 84px; height: 84px;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--ok-soft), #bbf7d0);
            border-radius: 24px;
            color: var(--ok);
            font-size: 2.6rem;
            box-shadow: 0 8px 20px rgba(21, 128, 61, 0.15);
        }
        .cert-title {
            text-align: center;
            font-weight: 800;
            font-size: 1.85rem;
            color: var(--ok);
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }
        .cert-subtitle {
            text-align: center;
            color: var(--slate);
            font-size: 0.85rem;
            margin-bottom: 24px;
        }
        .cert-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 28px 0;
        }
        .cert-row {
            display: flex;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
            align-items: flex-start;
        }
        .cert-row:last-child { border-bottom: none; }
        .cert-row-label {
            min-width: 180px;
            color: #64748b;
            font-weight: 500;
            font-size: 0.92rem;
        }
        .cert-row-value {
            flex: 1;
            font-weight: 700;
            color: #0f172a;
            font-size: 0.95rem;
            word-break: break-word;
        }
        .cert-row-value .hash {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #ec4899, #f43f5e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .level-pill {
            display: inline-flex; align-items: center;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white; padding: 4px 12px; border-radius: 999px;
            font-size: 0.78rem; font-weight: 700;
        }
        .role-pill {
            display: inline-flex; align-items: center; gap: 4px;
            background: #f1f5f9;
            color: #334155;
            padding: 4px 10px; border-radius: 6px;
            font-size: 0.85rem; font-weight: 700;
            font-family: monospace;
        }

        /* Signatures list */
        .sig-list-title {
            font-weight: 800;
            font-size: 1.1rem;
            color: #0f172a;
            margin-bottom: 14px;
            display: flex; align-items: center; gap: 8px;
        }
        .sig-list-title .badge {
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            color: white;
            font-size: 0.7rem;
        }

        /* Doc summary card */
        .doc-summary {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .doc-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.88rem;
            border-bottom: 1px dashed #f1f5f9;
        }
        .doc-summary-row:last-child { border-bottom: none; }
        .doc-summary-row .lbl { color: #64748b; font-weight: 500; }
        .doc-summary-row .val { color: #0f172a; font-weight: 700; }
        .doc-summary-row .val.mono { font-family: monospace; color: var(--brand-1); }

        /* Approval chain */
        .approval-chain { display: flex; flex-direction: column; gap: 10px; }
        .approval-step {
            display: flex; gap: 14px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 16px;
            transition: all 0.2s ease;
        }
        .approval-step:hover { border-color: rgba(79,70,229,0.25); box-shadow: 0 4px 12px rgba(15,23,42,0.05); }
        .approval-step.approved { background: linear-gradient(135deg, #f0fdf4, #ffffff); border-left: 4px solid #22c55e; }
        .approval-step.rejected { background: linear-gradient(135deg, #fef2f2, #ffffff); border-left: 4px solid #ef4444; }
        .approval-step.pending  { background: linear-gradient(135deg, #fffbeb, #ffffff); border-left: 4px solid #f59e0b; }
        .approval-step-num {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
            color: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
            font-size: 0.92rem;
        }

        /* Footer */
        .verify-footer {
            text-align: center;
            margin-top: 28px;
            color: #94a3b8;
            font-size: 0.85rem;
        }
        .verify-footer .brand {
            color: var(--brand-1);
            font-weight: 800;
        }

        @media (max-width: 575.98px) {
            body { padding: 14px 8px 80px; }
            .cert-card { padding: 24px 18px; }
            .cert-title { font-size: 1.4rem; }
            .cert-row { flex-direction: column; gap: 4px; padding: 10px 0; }
            .cert-row-label { min-width: 0; font-size: 0.75rem; }
            .cert-row-value { font-size: 0.85rem; }
        }
    </style>
</head>
<body>
<div class="verify-wrap">

    @php $primarySig = $signatures->first(); @endphp

    {{-- ========= CERT CARD (utama) ========= --}}
    <div class="cert-card">
        <div class="text-center mb-3">
            <span class="doc-pill"><i class="bi bi-shield-fill-check"></i> SISTEM IT SUBMISSIONS · TERVERIFIKASI</span>
        </div>

        <div class="cert-icon-wrap">
            <div class="cert-icon">
                <i class="bi bi-patch-check-fill"></i>
            </div>
        </div>
        <div class="cert-title">Tanda Tangan Digital Terverifikasi</div>
        <div class="cert-subtitle">Dokumen ini telah ditandatangani secara digital dan dapat divalidasi keasliannya.</div>

        <div class="cert-divider"></div>

        @if($primarySig)
            <div class="cert-row">
                <div class="cert-row-label">Ditandatangani oleh</div>
                <div class="cert-row-value">
                    {{ $primarySig->signer_name }}
                    @if($primarySig->delegated_from_id && $primarySig->delegatedFrom)
                        <div class="text-warning-emphasis small mt-1" style="font-size:0.78rem;">
                            <i class="bi bi-arrow-return-left"></i>
                            sbg wakil dari <b>{{ $primarySig->delegatedFrom->name }}</b>
                        </div>
                    @endif
                </div>
            </div>
            <div class="cert-row">
                <div class="cert-row-label">Jabatan / Role</div>
                <div class="cert-row-value"><span class="role-pill">{{ str_replace(' ', '-', strtolower($primarySig->role_label)) }}</span></div>
            </div>
            <div class="cert-row">
                <div class="cert-row-label">Level Approval</div>
                <div class="cert-row-value">
                    @php
                        $level = $arsip->approvals?->where('role_label', $primarySig->role_label)->first()?->step_order;
                    @endphp
                    <span class="level-pill">Level {{ $level ?? '—' }}</span>
                </div>
            </div>
            <div class="cert-row">
                <div class="cert-row-label">Tanggal TTD</div>
                <div class="cert-row-value">{{ optional($primarySig->signed_at)->translatedFormat('d M Y, H:i') }} WIB</div>
            </div>
            <div class="cert-row">
                <div class="cert-row-label">Hash</div>
                <div class="cert-row-value"><span class="hash">{{ $primarySig->hash }}</span></div>
            </div>
            <div class="cert-row">
                <div class="cert-row-label">Dokumen</div>
                <div class="cert-row-value">{{ str_replace('_', ' ', $arsip->jenis_pengajuan) }} #{{ $arsip->id }}</div>
            </div>
        @else
            <div class="text-center text-muted py-3">
                <i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>
                Belum ada tanda tangan digital pada dokumen ini.
            </div>
        @endif
    </div>

    {{-- ========= DOC SUMMARY ========= --}}
    <div class="doc-summary mt-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i>Ringkasan Dokumen</h6>
        <div class="doc-summary-row"><span class="lbl">No Registrasi</span><span class="val mono">{{ $arsip->no_registrasi }}</span></div>
        <div class="doc-summary-row"><span class="lbl">No Dokumen</span><span class="val mono">{{ $arsip->no_doc ?: '—' }}</span></div>
        <div class="doc-summary-row"><span class="lbl">Jenis Pengajuan</span><span class="val">{{ str_replace('_',' ', $arsip->jenis_pengajuan) }}</span></div>
        <div class="doc-summary-row"><span class="lbl">Pemohon</span><span class="val">{{ $arsip->pemohon ?: $arsip->admin->name ?? '—' }}</span></div>
        <div class="doc-summary-row"><span class="lbl">Departemen / Unit</span><span class="val">{{ $arsip->department->name ?? '—' }} / {{ $arsip->unit->name ?? '—' }}</span></div>
        <div class="doc-summary-row"><span class="lbl">Tanggal Pengajuan</span><span class="val">{{ optional($arsip->tgl_pengajuan)->translatedFormat('d M Y') }}</span></div>
    </div>

    {{-- ========= ALL SIGNATURES ========= --}}
    @if($signatures->count() > 1)
        <div class="doc-summary mt-3">
            <div class="sig-list-title">
                <i class="bi bi-pen-fill text-primary"></i>Seluruh Tanda Tangan
                <span class="badge">{{ $signatures->count() }}</span>
            </div>
            <div class="approval-chain">
                @foreach($signatures as $i => $sig)
                    <div class="approval-step approved">
                        <div class="approval-step-num">{{ $i + 1 }}</div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between gap-2 flex-wrap">
                                <div>
                                    <div class="fw-bold" style="font-size:0.95rem;">
                                        {{ $sig->signer_name }}
                                        @if($sig->delegated_from_id)
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1" style="font-size:0.65rem;">
                                                <i class="bi bi-arrow-return-left me-1"></i>DELEGASI
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-muted small">
                                        {{ $sig->role_label }} · {{ optional($sig->signed_at)->translatedFormat('d M Y, H:i') }} WIB
                                    </div>
                                    @if($sig->delegated_from_id && $sig->delegatedFrom)
                                        <div class="text-warning-emphasis small mt-1" style="font-size:0.75rem;">
                                            <i class="bi bi-arrow-return-left"></i>
                                            TTD sbg wakil dari <b>{{ $sig->delegatedFrom->name }}</b>
                                            @if($sig->delegatedFrom->jabatan)
                                                <span class="text-muted">({{ $sig->delegatedFrom->jabatan }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    @if($sig->is_valid)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i>VALID</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i>TIDAK VALID</span>
                                    @endif
                                </div>
                            </div>
                            @if($sig->hash)
                                <div class="mt-1 font-monospace text-muted" style="font-size:0.7rem; word-break:break-all;">#{{ \Illuminate\Support\Str::limit($sig->hash, 36) }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ========= APPROVAL CHAIN (semua step) ========= --}}
    @if($arsip->approvals && $arsip->approvals->count())
        <div class="doc-summary mt-3">
            <div class="sig-list-title">
                <i class="bi bi-diagram-3-fill text-primary"></i>Alur Persetujuan
            </div>
            <div class="approval-chain">
                @foreach($arsip->approvals as $step)
                    @php
                        $cls = $step->status === 'approved' ? 'approved'
                             : ($step->status === 'rejected' ? 'rejected' : 'pending');
                        $icon = $step->status === 'approved' ? 'bi-check-circle-fill'
                              : ($step->status === 'rejected' ? 'bi-x-circle-fill' : 'bi-hourglass-split');
                    @endphp
                    <div class="approval-step {{ $cls }}">
                        <div class="approval-step-num">{{ $step->step_order }}</div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between gap-2 flex-wrap">
                                <div>
                                    <div class="fw-bold" style="font-size:0.95rem;">{{ $step->role_label }}</div>
                                    <div class="text-muted small">
                                        {{ $step->approver->name ?? '—' }}
                                        @if($step->acted_at)
                                            · {{ optional($step->acted_at)->translatedFormat('d M Y, H:i') }} WIB
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $cls === 'approved' ? 'success' : ($cls === 'rejected' ? 'danger' : 'warning') }}-subtle
                                                 text-{{ $cls === 'approved' ? 'success' : ($cls === 'rejected' ? 'danger' : 'warning') }}">
                                        <i class="bi {{ $icon }} me-1"></i>{{ strtoupper($step->status) }}
                                    </span>
                                </div>
                            </div>
                            @if($step->note)
                                <div class="mt-1 small text-secondary" style="font-style:italic;">"{{ $step->note }}"</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="verify-footer">
        Diverifikasi pada {{ now()->translatedFormat('d M Y, H:i') }} WIB<br>
        <span class="brand">IT Submissions</span> © {{ now()->year }}
    </div>
</div>
</body>
</html>
