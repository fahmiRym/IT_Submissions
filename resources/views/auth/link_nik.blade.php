<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Link NIK Karyawan — IT Submissions</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-1: #6366f1;
            --brand-2: #4f46e5;
            --brand-3: #7c3aed;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0a0e1f;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
            color: #1e293b;
        }
        /* Animated mesh background */
        body::before, body::after {
            content: ''; position: fixed; inset: 0; pointer-events: none; z-index: 0;
        }
        body::before {
            background:
                radial-gradient(ellipse 800px 600px at 0% 0%, rgba(99,102,241,0.35), transparent 50%),
                radial-gradient(ellipse 700px 500px at 100% 100%, rgba(124,58,237,0.30), transparent 50%),
                radial-gradient(ellipse 500px 400px at 50% 50%, rgba(6,182,212,0.15), transparent 60%);
            animation: meshFloat 18s ease-in-out infinite;
        }
        body::after {
            background-image:
                radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 32px 32px;
            opacity: 0.6;
        }
        @keyframes meshFloat {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(2deg); }
        }
        /* Brand mark floating top-left */
        .brand-mark {
            position: fixed; top: 28px; left: 28px;
            display: flex; align-items: center; gap: 10px;
            color: white; z-index: 10;
            font-weight: 800; letter-spacing: 0.3px;
        }
        .brand-mark .logo {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--brand-1), var(--brand-3));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 20px rgba(99,102,241,0.4);
        }
        .brand-mark small { display: block; font-size: 0.7rem; font-weight: 500; opacity: 0.7; letter-spacing: 0; }

        .card-setup {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(20px);
            border-radius: 28px;
            max-width: 500px; width: 100%;
            box-shadow:
                0 30px 80px rgba(0,0,0,0.5),
                0 0 0 1px rgba(255,255,255,0.1) inset;
            overflow: hidden;
            position: relative; z-index: 1;
            animation: cardIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(20px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Header */
        .card-setup-head {
            background: linear-gradient(135deg, var(--brand-1) 0%, var(--brand-2) 50%, var(--brand-3) 100%);
            color: white;
            padding: 36px 32px 32px;
            position: relative;
            overflow: hidden;
        }
        .card-setup-head::after {
            content: ''; position: absolute; top: -50%; right: -15%;
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(255,255,255,0.2), transparent 70%);
            pointer-events: none;
        }
        .card-setup-head::before {
            content: ''; position: absolute; bottom: -60%; left: -10%;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(6,182,212,0.25), transparent 70%);
            pointer-events: none;
        }
        .head-content { position: relative; z-index: 2; }
        .step-badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(10px);
            padding: 7px 16px; border-radius: 999px;
            font-size: 0.68rem; font-weight: 800; letter-spacing: 0.15em;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .step-badge i { font-size: 0.85rem; }
        h1.title {
            font-size: 1.65rem;
            font-weight: 900;
            margin-top: 18px;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }
        p.sub { opacity: 0.88; margin: 8px 0 0; font-size: 0.92rem; line-height: 1.45; }
        .step-progress { display: flex; align-items: center; gap: 10px; margin-top: 24px; }
        .step-dot {
            flex: 1; height: 5px; border-radius: 999px;
            background: rgba(255,255,255,0.2);
            transition: all 0.3s;
        }
        .step-dot.active {
            background: white;
            box-shadow: 0 0 12px rgba(255,255,255,0.5);
        }
        .step-num {
            font-size: 0.65rem;
            font-weight: 800;
            opacity: 0.7;
            margin-top: 8px;
            text-align: right;
            letter-spacing: 0.05em;
        }

        /* Body */
        .card-setup-body { padding: 30px 32px 28px; }

        /* User strip */
        .user-strip {
            display: flex; align-items: center; gap: 14px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1.5px solid #e2e8f0;
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .user-strip::before {
            content: ''; position: absolute; top: 0; left: 0;
            width: 4px; height: 100%;
            background: linear-gradient(180deg, var(--brand-1), var(--brand-3));
        }
        .user-strip .avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-1), var(--brand-3));
            color: white;
            font-weight: 900;
            font-size: 1.1rem;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(99,102,241,0.35);
            flex-shrink: 0;
        }
        .user-strip .info { flex-grow: 1; min-width: 0; }
        .user-strip .info .name { font-weight: 800; color: #0f172a; font-size: 0.95rem; line-height: 1.25; }
        .user-strip .info .username {
            color: #64748b; font-size: 0.75rem;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            margin-top: 1px;
        }
        .user-strip .arrow-area {
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            color: var(--brand-2);
        }
        .user-strip .arrow-area i { font-size: 1.4rem; animation: arrowSlide 1.8s ease-in-out infinite; }
        .user-strip .arrow-area small { font-size: 0.6rem; font-weight: 800; letter-spacing: 0.1em; color: var(--brand-2); }
        @keyframes arrowSlide { 0%,100% { transform: translateX(0); } 50% { transform: translateX(4px); } }

        /* Alert info */
        .alert-info-soft {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #93c5fd;
            color: #1e3a8a;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 0.82rem;
            display: flex; align-items: start; gap: 10px;
        }
        .alert-info-soft i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

        /* Label */
        .input-label {
            display: flex; align-items: center; gap: 6px;
            font-size: 0.72rem; font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #475569;
            margin-bottom: 10px;
        }
        .input-label i { color: var(--brand-2); font-size: 0.95rem; }

        /* NIK Input */
        .input-wrap {
            position: relative;
            background: #f8fafc;
            border: 2.5px solid #e2e8f0;
            border-radius: 16px;
            transition: all 0.25s ease;
            overflow: hidden;
        }
        .input-wrap:focus-within {
            border-color: var(--brand-1);
            background: white;
            box-shadow: 0 0 0 5px rgba(99,102,241,0.12);
        }
        .input-wrap::before {
            content: '#';
            position: absolute;
            left: 20px; top: 50%; transform: translateY(-50%);
            font-family: monospace;
            color: #cbd5e1;
            font-size: 1.5rem;
            font-weight: 700;
            pointer-events: none;
        }
        .input-nik {
            border: none;
            background: transparent;
            padding: 18px 22px 18px 52px;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 8px;
            text-align: center;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            color: #0f172a;
            width: 100%;
            outline: none;
        }
        .input-nik::placeholder {
            color: #cbd5e1;
            font-weight: 700;
            letter-spacing: 8px;
        }

        .help-text {
            color: #64748b;
            font-size: 0.78rem;
            margin-top: 12px;
            display: flex; align-items: start; gap: 6px;
            line-height: 1.5;
        }
        .help-text i { color: var(--brand-1); flex-shrink: 0; margin-top: 2px; }
        .help-text b {
            color: var(--brand-2);
            background: rgba(99,102,241,0.08);
            padding: 1px 6px;
            border-radius: 5px;
            font-family: monospace;
        }

        /* Submit button */
        .btn-submit {
            background: linear-gradient(135deg, var(--brand-1) 0%, var(--brand-2) 50%, var(--brand-3) 100%);
            background-size: 200% auto;
            color: white;
            border: none;
            border-radius: 16px;
            padding: 16px 24px;
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            width: 100%;
            box-shadow: 0 14px 30px rgba(99,102,241,0.4);
            transition: all 0.3s ease;
            margin-top: 18px;
            position: relative;
            overflow: hidden;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(99,102,241,0.5);
            background-position: right center;
        }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit::after {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
            transition: left 0.6s ease;
        }
        .btn-submit:hover::after { left: 100%; }

        /* Logout */
        .logout-link {
            display: block; text-align: center;
            color: #94a3b8; font-size: 0.78rem;
            margin-top: 18px; text-decoration: none;
            padding: 10px;
            border-radius: 10px;
            transition: all 0.2s;
            background: transparent;
        }
        .logout-link:hover { color: #ef4444; background: #fef2f2; }

        /* Alert error */
        .alert-error {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border: 1px solid #fca5a5;
            color: #7f1d1d;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 0.85rem;
            display: flex; align-items: start; gap: 10px;
            margin-bottom: 16px;
            animation: shake 0.5s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        /* Footer mini */
        .card-footer-mini {
            text-align: center;
            margin-top: 22px;
            color: rgba(255,255,255,0.45);
            font-size: 0.72rem;
            position: relative;
            z-index: 2;
        }
        .card-footer-mini a { color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 700; }
        .card-footer-mini a:hover { color: white; }

        @media (max-width: 575.98px) {
            .brand-mark { top: 16px; left: 16px; }
            .brand-mark small { display: none; }
            .card-setup-head { padding: 28px 24px 24px; }
            h1.title { font-size: 1.4rem; }
            .card-setup-body { padding: 24px 22px 22px; }
            .input-nik { font-size: 1.25rem; letter-spacing: 5px; padding: 16px 18px 16px 46px; }
        }
    </style>
</head>
<body>

<div class="brand-mark">
    <div class="logo"><i class="bi bi-shield-fill-check"></i></div>
    <div>
        IT Submissions
        <small>Digital Approval System</small>
    </div>
</div>

<div>
<div class="card-setup">
    <div class="card-setup-head">
        <div class="head-content">
            <span class="step-badge"><i class="bi bi-shield-lock-fill"></i>VERIFIKASI AKUN</span>
            <h1 class="title">Masukkan NIK Karyawan</h1>
            <p class="sub">Verifikasi sekali untuk menghubungkan akun lama Anda dengan data karyawan dari HR. Hanya butuh 30 detik.</p>
            <div class="step-progress">
                <div class="step-dot active"></div>
                <div class="step-dot"></div>
            </div>
            <div class="step-num">STEP 1 / 2</div>
        </div>
    </div>

    <div class="card-setup-body">
        <div class="user-strip">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="info">
                <div class="name text-truncate">{{ auth()->user()->name }}</div>
                <div class="username text-truncate">{{ '@' . auth()->user()->username }}</div>
            </div>
            <div class="arrow-area">
                <i class="bi bi-arrow-right-circle-fill"></i>
                <small>LINK NIK</small>
            </div>
        </div>

        @if(session('info'))
            <div class="alert-info-soft mb-3"><i class="bi bi-info-circle-fill"></i><div>{{ session('info') }}</div></div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('auth.link-nik.submit') }}" autocomplete="off">
            @csrf
            <div class="input-label">
                <i class="bi bi-person-badge-fill"></i>
                NIK / Employee ID
            </div>
            <div class="input-wrap">
                <input type="text" name="employee_id" class="input-nik"
                       placeholder="0000" value="{{ old('employee_id') }}"
                       inputmode="numeric" pattern="[0-9]*"
                       autofocus required maxlength="20">
            </div>
            <div class="help-text">
                <i class="bi bi-lightbulb-fill"></i>
                <div>
                    NIK Anda biasanya 4–5 digit (contoh: <b>4492</b>). Jika tidak yakin, hubungi atasan atau bagian HR.
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-check2-circle me-1"></i>
                LANJUT KE GANTI PASSWORD
                <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-link border-0 w-100">
                <i class="bi bi-box-arrow-left me-1"></i>Logout &amp; login dengan akun lain
            </button>
        </form>
    </div>
</div>
<div class="card-footer-mini">© {{ date('Y') }} IT Submissions · <a href="#" onclick="return false;">Bantuan?</a></div>
</div>

<script>
    // Auto-format input: hanya angka
    document.querySelector('.input-nik')?.addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });
</script>
</body>
</html>
