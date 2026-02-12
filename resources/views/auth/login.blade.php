<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | E-Arsip</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Custom Modern Theme --}}
    <link href="{{ asset('css/modern-theme.css') }}" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #4f46e5 0%, #0ea5e9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .login-header {
            background: rgba(79, 70, 229, 0.05);
            padding: 2rem 2rem 1rem;
            border-radius: 1.5rem 1.5rem 0 0;
            text-align: center;
        }
        .login-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 1rem;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }
    </style>
</head>
<body>

<div class="login-card animate-on-scroll">
    <div class="login-header">
        <div class="login-icon bg-white p-2">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <h4 class="fw-bold text-dark mb-1">Selamat Datang</h4>
        <p class="text-secondary small mb-0">Silakan login untuk mengakses IT Submission</p>
    </div>
    
    <div class="p-4 pt-3">
        @error('login')
            <div class="alert alert-danger d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                <div>{{ $message }}</div>
            </div>
        @enderror

        <form method="POST" action="{{ route('login.process') }}">
            @csrf

            <div class="mb-4">
                <label class="form-label text-sm text-secondary fw-bold text-uppercase">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required style="border-left:none" autocomplete="off">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-sm text-secondary fw-bold text-uppercase">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required style="border-left:none">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold shadow-lg">
                Masuk Sekarang <i class="bi bi-arrow-right ms-2"></i>
            </button>
            
            <div class="text-center mt-4">
                <small class="text-muted">© 2026_IT_Submission</small>
            </div>
        </form>
    </div>
</div>

</body>
</html>
