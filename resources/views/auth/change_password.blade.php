<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ganti Password — IT Submissions</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand-1: #6366f1;
            --brand-2: #4f46e5;
            --brand-3: #7c3aed;
            --ok-1:    #10b981;
            --ok-2:    #059669;
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
            background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 32px 32px;
            opacity: 0.6;
        }
        @keyframes meshFloat {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(2deg); }
        }

        /* Brand mark */
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
            background: radial-gradient(circle, rgba(16,185,129,0.30), transparent 70%);
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
        .step-dot.done { background: rgba(255,255,255,0.6); }
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

        /* Alerts */
        .alert-success-soft {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #6ee7b7;
            color: #065f46;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 0.85rem;
            display: flex; align-items: start; gap: 10px;
            margin-bottom: 16px;
        }
        .alert-success-soft i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
        .alert-info-soft {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #93c5fd;
            color: #1e3a8a;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 0.82rem;
            display: flex; align-items: start; gap: 10px;
            margin-bottom: 16px;
        }
        .alert-info-soft i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
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
        .alert-error i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

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

        /* Password input */
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
        .input-pass {
            border: none;
            background: transparent;
            padding: 16px 52px 16px 20px;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #0f172a;
            width: 100%;
            outline: none;
        }
        .input-pass::placeholder {
            color: #94a3b8;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        .toggle-eye {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            border: none; background: transparent; color: #94a3b8;
            cursor: pointer; padding: 8px 10px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .toggle-eye:hover { color: var(--brand-2); background: rgba(99,102,241,0.08); }

        /* Strength meter */
        .pwd-strength {
            height: 5px;
            background: #e2e8f0;
            border-radius: 999px;
            margin-top: 10px;
            overflow: hidden;
        }
        .pwd-strength > div {
            height: 100%; width: 0%;
            transition: all 0.3s ease;
            background: #ef4444;
            border-radius: 999px;
        }
        .pwd-strength.weak > div     { width: 25%;  background: #ef4444; }
        .pwd-strength.medium > div   { width: 55%;  background: #f59e0b; }
        .pwd-strength.strong > div   { width: 80%;  background: var(--ok-1); }
        .pwd-strength.very-strong > div { width: 100%; background: var(--ok-2); }

        .pwd-label {
            font-size: 0.72rem;
            font-weight: 800;
            margin-top: 6px;
            color: #94a3b8;
            display: flex; align-items: center; gap: 6px;
            letter-spacing: 0.05em;
        }

        .input-block { margin-bottom: 14px; }

        /* Tips card */
        .pwd-tips {
            background: linear-gradient(135deg, rgba(99,102,241,0.06), rgba(124,58,237,0.06));
            border: 1px solid rgba(99,102,241,0.18);
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 0.8rem;
            color: #312e81;
            margin: 18px 0 20px;
        }
        .pwd-tips .tips-title {
            font-weight: 800;
            color: var(--brand-2);
            display: flex; align-items: center; gap: 6px;
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .pwd-tips ul { margin: 8px 0 0 4px; padding: 0; list-style: none; }
        .pwd-tips li {
            margin: 4px 0; display: flex; align-items: start; gap: 8px;
            color: #475569;
        }
        .pwd-tips li i { color: var(--brand-1); flex-shrink: 0; margin-top: 2px; font-size: 0.75rem; }

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

        /* Footer mini */
        .card-footer-mini {
            text-align: center;
            margin-top: 22px;
            color: rgba(255,255,255,0.45);
            font-size: 0.72rem;
            position: relative; z-index: 2;
        }
        .card-footer-mini a { color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 700; }
        .card-footer-mini a:hover { color: white; }

        @media (max-width: 575.98px) {
            .brand-mark { top: 16px; left: 16px; }
            .brand-mark small { display: none; }
            .card-setup-head { padding: 28px 24px 24px; }
            h1.title { font-size: 1.4rem; }
            .card-setup-body { padding: 24px 22px 22px; }
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
            <span class="step-badge"><i class="bi bi-key-fill"></i>BUAT PASSWORD BARU</span>
            <h1 class="title">Amankan Akun Anda</h1>
            <p class="sub">
                @if(empty(auth()->user()->employee_id))
                    Demi keamanan, ganti password default Anda sebelum melanjutkan ke dashboard.
                @else
                    NIK berhasil di-link. Sekarang silakan buat password baru untuk akun Anda.
                @endif
            </p>
            <div class="step-progress">
                @if(!empty(auth()->user()->employee_id))
                    <div class="step-dot done"></div>
                    <div class="step-dot active"></div>
                @else
                    <div class="step-dot done"></div>
                    <div class="step-dot active"></div>
                @endif
            </div>
            <div class="step-num">STEP 2 / 2</div>
        </div>
    </div>

    <div class="card-setup-body">
        @if(session('success'))
            <div class="alert-success-soft">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif
        @if(session('info'))
            <div class="alert-info-soft">
                <i class="bi bi-info-circle-fill"></i>
                <div>{{ session('info') }}</div>
            </div>
        @endif
        @if(isset($errors) && $errors->any())
            <div class="alert-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('auth.change-password.submit') }}" autocomplete="off" id="formCp">
            @csrf

            <div class="input-block">
                <div class="input-label">
                    <i class="bi bi-key"></i>
                    Password Saat Ini
                </div>
                <div class="input-wrap">
                    <input type="password" name="current_password" id="cp_old" class="input-pass"
                           placeholder="Password saat ini (default sesuai role: admin123, spv123, dll)" required>
                    <button type="button" class="toggle-eye" data-target="cp_old"><i class="bi bi-eye"></i></button>
                </div>
            </div>

            <div class="input-block">
                <div class="input-label">
                    <i class="bi bi-shield-lock-fill"></i>
                    Password Baru
                </div>
                <div class="input-wrap">
                    <input type="password" name="new_password" id="cp_new" class="input-pass"
                           placeholder="Minimal 6 karakter" required minlength="6">
                    <button type="button" class="toggle-eye" data-target="cp_new"><i class="bi bi-eye"></i></button>
                </div>
                <div class="pwd-strength" id="pwdStrength"><div></div></div>
                <div class="pwd-label" id="pwdLabel"><i class="bi bi-dash-circle"></i>Belum diisi</div>
            </div>

            <div class="input-block">
                <div class="input-label">
                    <i class="bi bi-check2-square"></i>
                    Konfirmasi Password Baru
                </div>
                <div class="input-wrap">
                    <input type="password" name="new_password_confirmation" id="cp_conf" class="input-pass"
                           placeholder="Ketik ulang password baru" required minlength="6">
                    <button type="button" class="toggle-eye" data-target="cp_conf"><i class="bi bi-eye"></i></button>
                </div>
            </div>

            <div class="pwd-tips">
                <div class="tips-title"><i class="bi bi-lightbulb-fill"></i> Tips Password Aman</div>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Minimal 6 karakter (rekomendasi &ge; 8)</li>
                    <li><i class="bi bi-check-circle-fill"></i> Gabungkan huruf besar, kecil, &amp; angka</li>
                    <li><i class="bi bi-check-circle-fill"></i> Hindari NIK, tanggal lahir, atau nama Anda</li>
                </ul>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-shield-check me-1"></i>
                SIMPAN &amp; MASUK DASHBOARD
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
<div class="card-footer-mini">&copy; {{ date('Y') }} IT Submissions &middot; <a href="#" onclick="return false;">Bantuan?</a></div>
</div>

<script>
    // Toggle eye
    document.querySelectorAll('.toggle-eye').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target);
            const icon = btn.querySelector('i');
            if (target.type === 'password') { target.type = 'text'; icon.className = 'bi bi-eye-slash'; }
            else { target.type = 'password'; icon.className = 'bi bi-eye'; }
        });
    });

    // Password strength meter
    const pwd = document.getElementById('cp_new');
    const meter = document.getElementById('pwdStrength');
    const label = document.getElementById('pwdLabel');
    pwd?.addEventListener('input', () => {
        const v = pwd.value;
        let score = 0;
        if (v.length >= 6) score++;
        if (v.length >= 10) score++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
        if (/\d/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;
        meter.className = 'pwd-strength';
        if (v.length === 0) {
            label.innerHTML = '<i class="bi bi-dash-circle"></i>Belum diisi';
            label.style.color = '#94a3b8';
            return;
        }
        if (score <= 1)      { meter.classList.add('weak');        label.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i>Lemah';       label.style.color = '#dc2626'; }
        else if (score <= 2) { meter.classList.add('medium');      label.innerHTML = '<i class="bi bi-shield-exclamation"></i>Sedang';            label.style.color = '#d97706'; }
        else if (score <= 3) { meter.classList.add('strong');      label.innerHTML = '<i class="bi bi-shield-check"></i>Kuat';                    label.style.color = '#059669'; }
        else                 { meter.classList.add('very-strong'); label.innerHTML = '<i class="bi bi-shield-fill-check"></i>Sangat Kuat';        label.style.color = '#047857'; }
    });

    // Confirm match indicator
    const conf = document.getElementById('cp_conf');
    conf?.addEventListener('input', () => {
        if (conf.value.length === 0) { conf.style.borderColor = ''; return; }
        if (conf.value === pwd.value) {
            conf.closest('.input-wrap').style.borderColor = '#10b981';
        } else {
            conf.closest('.input-wrap').style.borderColor = '#fca5a5';
        }
    });
</script>
</body>
</html>
