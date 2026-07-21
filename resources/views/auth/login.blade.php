<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>IT Submissions | V2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --accent: #06b6d4;
            --bg-dark: #0f172a;
            --shadow-focus: 0 0 0 4px rgba(99, 102, 241, 0.14);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Abstract Background Shapes */
        .bg-blobs {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .blob {
            position: absolute;
            filter: blur(80px);
            border-radius: 50%;
            opacity: 0.4;
            animation: move 20s infinite alternate;
        }

        .blob-1 {
            width: 400px;
            height: 400px;
            background: var(--primary);
            top: -10%;
            left: -5%;
        }

        .blob-2 {
            width: 300px;
            height: 300px;
            background: var(--accent);
            bottom: -5%;
            right: -5%;
            animation-delay: -5s;
        }

        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(100px, 50px) scale(1.2); }
        }

        /* Main Container */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2.5rem;
            display: flex;
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: all 0.4s ease;
        }

        /* Left Side: Branding & Info */
        .side-info {
            flex: 1.2;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(225deg, rgba(99, 102, 241, 0.12) 0%, rgba(6, 182, 212, 0.12) 100%);
            color: white;
            position: relative;
        }

        .badge-new {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            width: fit-content;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 1.5rem;
        }

        /* Right Side: Form */
        .side-form {
            flex: 1;
            background: white;
            padding: 4rem 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* OG image guide card */
        .og-guide {
            margin-top: 1.5rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1.25rem;
            overflow: hidden;
        }

.og-guide img {
            display: block;
            width: 100%;
            height: 220px;
            object-fit: contain;
            background: rgba(0, 0, 0, 0.08);
        }

        .og-guide .og-caption {
            padding: 0.85rem 1rem;
            color: rgba(255, 255, 255, 0.92);
            font-weight: 700;
            font-size: 0.85rem;
        }


        .input-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-box i {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: 0.3s;
        }

        .form-control {
            height: 60px;
            padding-left: 3.5rem;
            border-radius: 1rem;
            border: 1.5px solid #e2e8f0;
            font-weight: 500;
            background: #f8fafc;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: none;
        }

        .form-control:focus {
            background: white;
            border-color: var(--primary);
            box-shadow: var(--shadow-focus);
        }

        .form-control:focus+i {
            color: var(--primary);
        }

        .btn-login {
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 1rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px -10px rgba(79, 70, 229, 0.45);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 28px -12px rgba(79, 70, 229, 0.55);
        }

        .btn-login::after {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.22), transparent);
            transition: 0.5s;
        }

        .btn-login:hover::after { left: 100%; }

        /* UX helpers */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .password-toggle i { font-size: 1.05rem; }

        /* Responsive */
        @media (max-width: 992px) {
            .side-info { padding: 3rem; }
            .side-form { padding: 3rem 2.5rem; }
        }

        @media (max-width: 850px) {
            .glass-card { flex-direction: column; max-width: 500px; min-height: auto; }
            .side-info { display: none; }
            .side-form { padding: 3.5rem 2.25rem; border-radius: 2.5rem; }
        }

        @media (max-width: 575.98px) {
            body { padding: 14px; align-items: flex-start; padding-top: 24px; padding-bottom: 24px; }
            .side-form { padding: 2.25rem 1.4rem; }
            .form-control, .btn-login { height: 52px; }
            .glass-card { border-radius: 1.5rem; }
            .input-box { margin-bottom: 1.15rem; }
            .input-box i { left: 1rem; }
            .form-control { padding-left: 3rem; font-size: 0.95rem; }
            .btn-login { font-size: 0.95rem; }
            h3.fw-800 { font-size: 1.35rem; }
        }

        @media (max-width: 380px) {
            body { padding: 10px; padding-top: 18px; padding-bottom: 18px; }
            .side-form { padding: 1.85rem 1.05rem; }
            .form-control, .btn-login { height: 48px; }
            .form-control { padding-left: 2.75rem; font-size: 0.9rem; }
            .input-box i { left: 0.85rem; font-size: 1rem !important; }
            .password-toggle { right: 0.6rem; width: 38px; height: 38px; }
            h3.fw-800 { font-size: 1.2rem; }
        }

        /* OG modal/preview should never exceed viewport */
        .og-modal img { max-width: 100%; height: auto; }
    </style>

    <script>
        // --- OG IMAGE: close on ESC (helper) ---
        function bindEscapeClose(modalEl, overlayEl) {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (overlayEl && overlayEl.style) overlayEl.style.display = 'none';
                    if (modalEl && modalEl.style) modalEl.style.display = 'none';
                }
            });
        }

        function togglePassword() {

            const pwd = document.getElementById('password');
            if (!pwd) return;
            const isPassword = pwd.type === 'password';
            pwd.type = isPassword ? 'text' : 'password';
        }

        function checkCapsLock(event) {
            const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
            const caps = isMac ? event.getModifierState && event.getModifierState('CapsLock') : event.getModifierState && event.getModifierState('CapsLock');
            const el = document.getElementById('capsWarning');
            if (!el) return;
            el.classList.toggle('d-none', !caps);
        }

    window.addEventListener('DOMContentLoaded', () => {
            const pwd = document.getElementById('password');
            const btn = document.querySelector('.password-toggle');
            if (btn) btn.addEventListener('click', togglePassword);
            if (pwd) pwd.addEventListener('keyup', checkCapsLock);
            if (pwd) pwd.addEventListener('keydown', checkCapsLock);

            // OG image modal (click to zoom)
            const ogImg = document.getElementById('ogImageGuide');
            const ogModal = document.getElementById('ogImageModal');
            const ogModalClose = document.getElementById('ogImageModalClose');

            const openOgModal = () => {
                if (!ogModal) return;
                ogModal.style.display = 'flex';
            };

            const closeOgModal = () => {
                if (!ogModal) return;
                ogModal.style.display = 'none';
            };

            if (ogImg) {
                ogImg.addEventListener('click', openOgModal);
                ogImg.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') openOgModal();
                });
            }

            if (ogModalClose) ogModalClose.addEventListener('click', closeOgModal);
            if (ogModal) {
                ogModal.addEventListener('click', (e) => {
                    if (e.target === ogModal) closeOgModal();
                });
            }

            // After login: show OG guide modal only per session.
            // We use sessionStorage to persist choice within this browser session.

        });
    </script>


    <style>
        /* Modal OG image */
        .og-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.7);z-index:1060;display:none;align-items:center;justify-content:center;padding:20px;}
        .og-modal{max-width:980px;width:100%;background:#0b1220;border-radius:18px;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,.45);}
        .og-modal-header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:rgba(255,255,255,.06);color:#e2e8f0;}
        .og-modal-title{font-weight:800;letter-spacing:.2px;font-size:0.95rem;margin:0;}
        .og-modal-close{background:transparent;border:none;color:#e2e8f0;font-size:1.25rem;cursor:pointer;line-height:1;}
        .og-modal-body{padding:12px;background:#0b1220;}
        .og-modal-body img{width:100%;height:auto;display:block;object-fit:contain;background:rgba(255,255,255,.04);}

        /* Modal guide */
        .guide-modal-content{background:#0b1220;border-radius:18px;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,.45);}
        .guide-modal-header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:rgba(255,255,255,.06);color:#e2e8f0;}
        .guide-modal-header h3{margin:0;font-size:1rem;font-weight:900;}
        .guide-modal-body{padding:16px;color:#cbd5e1;}
        .guide-step{background:rgba(255,255,255,.04);border:1px solid rgba(148,163,184,.18);border-radius:14px;padding:12px 14px;margin-bottom:10px;}
        .guide-step b{color:#e2e8f0;}
        .guide-actions{padding:14px 16px;background:rgba(255,255,255,.03);display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:space-between;}
        .guide-actions label{display:flex;gap:10px;align-items:center;color:#cbd5e1;font-weight:700;font-size:.9rem;}
        .guide-actions input[type="checkbox"]{width:18px;height:18px;accent-color:#6366f1;}
        .guide-btn{border:none;border-radius:12px;padding:10px 14px;font-weight:900;cursor:pointer;}
        .guide-btn-primary{background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);color:#fff;}
        .guide-btn-ghost{background:transparent;color:#cbd5e1;border:1px solid rgba(148,163,184,.25);}

        /* Make it behave like Bootstrap centered modal */
        .og-toast-guide-overlay{position:fixed;inset:0;z-index:1055;background:rgba(15,23,42,.6);display:none;align-items:center;justify-content:center;padding:20px;}
    </style>
</head>

<body>
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="og-modal-overlay" id="ogImageModal" role="dialog" aria-modal="true" aria-label="OG Image Preview">
        <div class="og-modal">
            <div class="og-modal-header">
                <p class="og-modal-title">Panduan IT Submissions</p>
                <button type="button" class="og-modal-close" id="ogImageModalClose" aria-label="Close">&times;</button>
            </div>
            <div class="og-modal-body">
                <img id="ogImageModalImg" src="{{ asset('img/og-image.png') }}" alt="OG Image" />
            </div>
        </div>
    </div>



    <div class="glass-card animate__animated animate__fadeInUp">

        <div class="side-info">
            <div class="badge-new">
                <span class="badge bg-primary me-2">New</span> IT Submissions V2
            </div>
            <!-- <h1 class="display-5 fw-800 mb-4 lh-sm"><br>
                <span style="color: var(--accent);">IT Submission</span>
            </h1> -->
            
            <p class="fs-5 opacity-75 mb-5 fw-light">Pantau status pengajuan secara real-time mulai dari drafting hingga persetujuan akhir dalam satu Dashboard terpusat</p>

            <img id="ogImageGuide" src="{{ asset('img/og-image.png') }}" alt="OG Image" style="width: 100%; height: auto; border-radius: 1rem; object-fit: contain;" class="mb-4" role="button" tabindex="0" aria-label="Klik untuk memperbesar OG image">

            <div class="mt-auto">
                <div class="d-flex align-items-center mb-3">

                    <div class="bg-white bg-opacity-10 p-2 rounded-3 me-3">
                        <i class="bi bi-patch-check text-info fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold">ISO 27001 Certified</div>
                        <div class="small opacity-50">Data Aman & Terenkripsi</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="side-form">
            <div class="mb-5 text-center text-lg-start">
                <h2 class="fw-800 text-dark mb-2"></h2>
                <p class="text-muted mb-0"></p>
            </div>

            <form method="POST" action="{{ route('login.process') }}">
                @csrf

                <div class="text-center mb-4">
                    <img src="{{ asset('img/logo.png') }}" alt="Company Logo" style="height: 56px; width: auto;" class="mb-3">
                    <h3 class="fw-800 text-dark mb-1">IT Submission</h3>
                    <p class="text-muted mb-0">Silahkan Login Untuk Melanjutkan</p>
                </div>

                <div class="input-box mt-4">
                    <input type="text" name="username" class="form-control" placeholder="Username atau NIK" required autocomplete="off" value="{{ old('username') }}">
                    <i class="bi bi-person-circle fs-5"></i>
                </div>


                <div class="input-box">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                    <i class="bi bi-shield-lock fs-5"></i>

                    <button type="button" class="password-toggle" aria-label="Show/Hide Password" title="Show/Hide Password">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>

                <div id="capsWarning" class="small text-danger d-none" style="padding-left: 0.1rem; margin-top: -10px; margin-bottom: 12px;">
                    Caps Lock aktif.
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label small text-muted" for="remember">Ingat saya</label>
                    </div>
                    <a href="#" class="small text-primary fw-bold text-decoration-none">Bantuan Login?</a>
                </div>

                <button type="submit" class="btn-login w-100 mb-4">
                    LOGIN<i class="bi bi-chevron-right ms-2"></i>
                </button>

                @error('username')
                    <div class="alert alert-danger py-2 px-3 border-0 rounded-3 small animate__animated animate__shakeX">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $message }}
                    </div>
                @enderror
            
                <div class="text-center mt-5">
                    <small class="text-muted opacity-50">IT Submission | 2026 &copy; Rymutich</small>
                </div>
            </form>
        </div>
    </div>
</body>

</html>

