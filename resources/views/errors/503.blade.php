<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <meta http-equiv="refresh" content="30">
    <title>Sedang Dalam Perbaikan — IT Submissions Inkalum</title>
    <link rel="icon" type="image/png" href="/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { min-height: 100%; }
        :root {
            --brand-red: #dc2626;
            --brand-red-light: #ef4444;
            --brand-red-glow: rgba(220, 38, 38, 0.35);
            --bg-1: #0f172a;
            --bg-2: #1e293b;
            --bg-3: #0b1120;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --border: rgba(255, 255, 255, 0.08);
            --card-bg: rgba(255, 255, 255, 0.03);
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background:
                radial-gradient(ellipse at top, #1e293b 0%, var(--bg-1) 45%, var(--bg-3) 100%);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 1px 1px, rgba(255,255,255,0.04) 1px, transparent 0);
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            top: -20%;
            right: -10%;
            width: 60vw;
            height: 60vw;
            max-width: 700px;
            max-height: 700px;
            background: radial-gradient(circle, var(--brand-red-glow) 0%, transparent 60%);
            filter: blur(60px);
            pointer-events: none;
            z-index: 0;
            animation: float-glow 12s ease-in-out infinite;
        }
        @keyframes float-glow {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: .6; }
            50%      { transform: translate(-30px, 30px) scale(1.1); opacity: .8; }
        }

        .container {
            max-width: 620px;
            width: 100%;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .logo-wrap {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-halo {
            position: absolute;
            inset: -20px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--brand-red-glow) 0%, transparent 70%);
            animation: halo-pulse 3s ease-in-out infinite;
            z-index: 1;
        }
        @keyframes halo-pulse {
            0%, 100% { transform: scale(1); opacity: .6; }
            50%      { transform: scale(1.15); opacity: 1; }
        }
        .logo-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid rgba(220, 38, 38, 0.3);
            animation: ring-spin 8s linear infinite;
            z-index: 2;
        }
        .logo-ring::before,
        .logo-ring::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            background: var(--brand-red-light);
            border-radius: 50%;
            box-shadow: 0 0 12px var(--brand-red-light);
        }
        .logo-ring::before { top: -4px; left: 50%; transform: translateX(-50%); }
        .logo-ring::after  { bottom: -4px; left: 50%; transform: translateX(-50%); }
        @keyframes ring-spin { to { transform: rotate(360deg); } }

        .logo-img {
            width: 82px;
            height: 82px;
            object-fit: contain;
            position: relative;
            z-index: 3;
            filter: drop-shadow(0 0 20px var(--brand-red-glow));
            animation: logo-breathe 3s ease-in-out infinite;
        }
        @keyframes logo-breathe {
            0%, 100% { transform: scale(1); }
            50%      { transform: scale(1.05); }
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(220, 38, 38, 0.12);
            border: 1px solid rgba(220, 38, 38, 0.3);
            color: var(--brand-red-light);
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .status-badge .dot {
            width: 6px; height: 6px;
            background: var(--brand-red-light);
            border-radius: 50%;
            animation: dot-blink 1.5s ease-in-out infinite;
            box-shadow: 0 0 8px var(--brand-red-light);
        }
        @keyframes dot-blink {
            0%, 100% { opacity: 1; }
            50%      { opacity: .3; }
        }

        h1 {
            font-size: clamp(2rem, 5.5vw, 3rem);
            font-weight: 800;
            margin-bottom: 14px;
            letter-spacing: -0.03em;
            line-height: 1.15;
            background: linear-gradient(135deg, #fff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .subtitle {
            font-size: clamp(0.95rem, 2vw, 1.05rem);
            color: var(--text-muted);
            margin-bottom: 36px;
            line-height: 1.7;
            font-weight: 400;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .subtitle strong { color: var(--text-main); font-weight: 600; }

        .progress-dots {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 36px;
        }
        .progress-dots .d {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--brand-red);
            opacity: 0.3;
            animation: dot-wave 1.4s ease-in-out infinite;
        }
        .progress-dots .d:nth-child(1) { animation-delay: 0s; }
        .progress-dots .d:nth-child(2) { animation-delay: 0.2s; }
        .progress-dots .d:nth-child(3) { animation-delay: 0.4s; }
        .progress-dots .d:nth-child(4) { animation-delay: 0.6s; }
        .progress-dots .d:nth-child(5) { animation-delay: 0.8s; }
        @keyframes dot-wave {
            0%, 60%, 100% { transform: scale(1); opacity: .3; }
            30%           { transform: scale(1.5); opacity: 1; box-shadow: 0 0 12px var(--brand-red-light); }
        }

        .info-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 24px 28px;
            margin-bottom: 28px;
            text-align: left;
            box-shadow: 0 20px 40px -20px rgba(0, 0, 0, 0.5);
        }
        .info-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
        }
        .info-row + .info-row {
            border-top: 1px solid var(--border);
        }
        .info-row .icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: rgba(220, 38, 38, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .info-row .icon svg {
            width: 20px; height: 20px;
            fill: var(--brand-red-light);
        }
        .info-row .content {
            flex: 1;
            min-width: 0;
        }
        .info-row .label {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 2px;
        }
        .info-row .value {
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 500;
        }
        .info-row .value.mono {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .info-row .value .countdown {
            display: inline-block;
            min-width: 26px;
            color: var(--brand-red-light);
            font-weight: 700;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 32px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 26px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            text-decoration: none;
            cursor: pointer;
            transition: all .2s ease;
            font-family: inherit;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-red) 0%, var(--brand-red-light) 100%);
            color: #fff;
            box-shadow: 0 8px 20px -6px var(--brand-red-glow);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -6px var(--brand-red-glow);
        }
        .btn svg { width: 18px; height: 18px; fill: currentColor; }

        .footer {
            padding-top: 24px;
            border-top: 1px solid var(--border);
            font-size: 0.78rem;
            color: var(--text-dim);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .footer .brand {
            color: var(--text-main);
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .footer .code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            color: var(--text-dim);
        }
        .footer .code .num {
            color: var(--brand-red-light);
            font-weight: 700;
        }

        @media (max-width: 480px) {
            .info-card { padding: 20px; }
            .actions { flex-direction: column; align-items: stretch; }
            .btn { justify-content: center; }
        }
    </style>
</head>
<body>
    <main class="container">

        <div class="logo-wrap">
            <div class="logo-halo"></div>
            <div class="logo-ring"></div>
            <img src="/img/logo.png" alt="Inkalum" class="logo-img">
        </div>

        <div class="status-badge">
            <span class="dot"></span>
            <span>Sistem sedang di-maintenance</span>
        </div>

        <h1>Sedang Dalam Perbaikan</h1>
        <p class="subtitle">
            Tim IT sedang melakukan pemeliharaan pada <strong>IT Submissions</strong>.
            Layanan akan kembali normal dalam waktu dekat. Terima kasih atas kesabaran Anda.
        </p>

        <div class="progress-dots">
            <div class="d"></div>
            <div class="d"></div>
            <div class="d"></div>
            <div class="d"></div>
            <div class="d"></div>
        </div>

        <div class="info-card">
            <div class="info-row">
                <div class="icon">
                    <svg viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                </div>
                <div class="content">
                    <div class="label">Status Server</div>
                    <div class="value">Sedang restart / update sistem</div>
                </div>
            </div>
            <div class="info-row">
                <div class="icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/></svg>
                </div>
                <div class="content">
                    <div class="label">Waktu Sekarang</div>
                    <div class="value mono" id="liveClock">--:--:--</div>
                </div>
            </div>
            <div class="info-row">
                <div class="icon">
                    <svg viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                </div>
                <div class="content">
                    <div class="label">Auto Refresh</div>
                    <div class="value">Halaman dimuat ulang otomatis dalam <span class="countdown" id="cd">30</span> detik</div>
                </div>
            </div>
            <div class="info-row">
                <div class="icon">
                    <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                </div>
                <div class="content">
                    <div class="label">Butuh Bantuan?</div>
                    <div class="value">Hubungi Departemen IT jika mendesak</div>
                </div>
            </div>
        </div>

        <div class="actions">
            <button type="button" class="btn btn-primary" onclick="location.reload()">
                <svg viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                Muat Ulang Sekarang
            </button>
        </div>

        <div class="footer">
            <div><span class="brand">Inkalum</span> · IT Submissions System · &copy; {{ date('Y') }}</div>
            <div class="code">Error <span class="num">503</span> · Service Temporarily Unavailable</div>
        </div>

    </main>

    <script>
        let sec = 30;
        const el = document.getElementById('cd');
        setInterval(() => { if (sec > 0) { sec--; el.textContent = sec; } }, 1000);

        const clock = document.getElementById('liveClock');
        function pad(n) { return String(n).padStart(2, '0'); }
        function tick() {
            const d = new Date();
            const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][d.getDay()];
            clock.textContent = `${hari} · ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())} WIB`;
        }
        tick();
        setInterval(tick, 1000);
    </script>
</body>
</html>
