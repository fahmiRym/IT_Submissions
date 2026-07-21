@extends('layouts.app')

@section('title', 'Aplikasi Sedang Dikembangkan')
@section('page-title', 'Pemeliharaan Sistem')

@section('content')
<div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 82vh; background: radial-gradient(circle at 85% 15%, rgba(13, 110, 253, 0.06), transparent 45%), radial-gradient(circle at 15% 85%, rgba(255, 193, 7, 0.04), transparent 45%), #f8fafc;">
    <div class="row justify-content-center w-100">
        <div class="col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6 text-center">
            
            {{-- Main Interactive Container --}}
            <div class="card border-0 shadow-lg rounded-5 p-4 p-sm-5 position-relative overflow-hidden bg-white match-glass">
                
                {{-- Futuristic Top Tech Line Accent --}}
                <div class="position-absolute top-0 start-0 w-100 d-flex" style="height: 6px;">
                    <div class="bg-primary flex-grow-1" style="opacity: 0.9;"></div>
                    <div class="bg-info" style="width: 25%; opacity: 0.7;"></div>
                    <div class="bg-warning" style="width: 15%; opacity: 0.8;"></div>
                </div>
                
                {{-- Background Geometric Watermarks --}}
                <div class="position-absolute text-light opacity-10" style="top: -20px; right: -20px; transform: rotate(15deg); z-index: 0; pointer-events: none;">
                    <i class="bi bi-cpu" style="font-size: 12rem;"></i>
                </div>
                <div class="position-absolute text-light opacity-10" style="bottom: -30px; left: -30px; transform: rotate(-15deg); z-index: 0; pointer-events: none;">
                    <i class="bi bi-code-slash" style="font-size: 10rem;"></i>
                </div>

                <div class="position-relative" style="z-index: 1;">
                    {{-- Micro-interaction Illustration Engine --}}
                    <div class="mb-5 position-relative d-inline-block mx-auto">
                        <div class="position-absolute top-50 start-50 translate-middle bg-primary bg-opacity-5 rounded-circle animate-pulse-ring" style="width: 180px; height: 180px;"></div>
                        <div class="position-absolute top-50 start-50 translate-middle bg-primary bg-opacity-10 rounded-circle shadow-xs" style="width: 135px; height: 135px;"></div>
                        
                        <div class="bg-gradient-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center position-relative border border-white border-4 shadow-md bg-white overflow-hidden" style="width: 100px; height: 100px;">
                            <div class="illustration-grid position-absolute w-100 h-100 opacity-25"></div>
                            <i class="bi bi-cone-striped display-5 text-primary position-absolute animate-bounce-subtle"></i>
                            <i class="bi bi-gear-fill position-absolute text-warning animate-spin-slow" style="top: 14px; right: 14px; font-size: 1.5rem;"></i>
                            <i class="bi bi-braces position-absolute text-info" style="bottom: 12px; left: 16px; font-size: 1.1rem; transform: rotate(-15deg);"></i>
                        </div>
                    </div>

                    {{-- Dynamic Typography Headers --}}
                    <h2 class="fw-black text-dark mb-2 tracking-tight">Sistem Sedang Diperbarui</h2>
                    <p class="text-secondary mb-4 px-2 px-md-4 lh-relaxed font-sans" style="font-size: 0.975rem;">
                        Modul <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1.5 rounded-3 fw-bold border border-primary border-opacity-10 shadow-xs">Manajemen Master Produk</span> sedang dimigrasikan ke arsitektur engine terbaru guna meningkatkan kecepatan olah data serta kestabilan transaksi Anda.
                    </p>

                    {{-- Innovative Feature Timeline Matrix --}}
                    <div class="p-4 mb-4 rounded-4 text-start border border-dashed border-light-dark bg-slate-50 transition-all hover-scale-card">
                        <div class="row g-3">
                            <div class="col-12 d-flex align-items-start">
                                <div class="bg-white text-primary border rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 42px; height: 42px; min-width: 42px;">
                                    <i class="bi bi-terminal-dash fs-5"></i>
                                </div>
                                <div class="ms-3 w-100">
                                    <div class="text-uppercase text-muted fw-bold tracking-wider" style="font-size: 0.65rem;">Aktivitas Utama</div>
                                    <div class="text-dark fw-bold mt-0.5" style="font-size: 0.925rem;">Refactoring Skema & Optimalisasi Query</div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div style="height: 1px; background: linear-gradient(90deg, #e2e8f0 0%, transparent 100%);"></div>
                            </div>

                            <div class="col-12 d-flex align-items-start">
                                <div class="bg-white text-warning border rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 42px; height: 42px; min-width: 42px;">
                                    <i class="bi bi-activity fs-5"></i>
                                </div>
                                <div class="ms-3 w-100">
                                    <div class="text-uppercase text-muted fw-bold tracking-wider" style="font-size: 0.65rem;">Estimasi Sinkronisasi</div>
                                    <div class="text-dark fw-bold mt-0.5 d-flex align-items-center" style="font-size: 0.925rem;">
                                        <span>Segera Dapat Diakses</span>
                                        <div class="modern-loader-dots ms-2">
                                            <span class="dot bg-warning"></span>
                                            <span class="dot bg-warning"></span>
                                            <span class="dot bg-warning"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SMART INTERACTIVE REFRESH TRIGGER --}}
                    <div class="d-flex flex-column flex-sm-row gap-2.5 justify-content-center align-items-center">
                        <a href="{{ url()->previous() == url()->current() ? url('/') : url()->previous() }}" class="btn btn-outline-secondary border-2 px-4 py-2.5 fw-bold rounded-3 d-inline-flex align-items-center justify-content-center transition-all w-100 w-sm-auto btn-hover-back">
                            <i class="bi bi-arrow-left-short fs-4 me-1"></i> Kembali
                        </a>
                        <button onclick="window.location.reload();" class="btn btn-primary px-4 py-2.5 fw-bold rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center transition-all w-100 w-sm-auto btn-hover-refresh">
                            <i class="bi bi-arrow-clockwise fs-5 me-2 animate-spin-hover"></i> Cek Status Terbaru
                        </button>
                    </div>
                </div>
            </div>
            
            {{-- Footer Support System & Business Contact Badge --}}
            <div class="d-flex flex-column align-items-center gap-2 mt-4 animate-fade-in">
                <div class="opacity-75 d-inline-flex align-items-center gap-1.5 bg-light px-3 py-1.5 rounded-pill border shadow-xs" style="font-size: 0.8rem;">
                    <span class="badge bg-success rounded-circle p-1 d-inline-block" style="width:6px; height:6px;"></span>
                    <span class="text-muted fw-medium">Butuh bantuan darurat ekosistem data ? </span> 
                    <!-- <a href="mailto:support@system.com" class="text-primary fw-bold text-decoration-none hover-underline">Hubungi IT Support</a> -->
                    <a href="https://wa.me/6285111034050" target="_blank" class="btn btn-sm btn-success px-3 py-1.5 rounded-pill shadow-xs border-0 d-inline-flex align-items-center justify-content-center font-monospace hover-whatsapp" style="font-size: 0.75rem; background-color: #25D366; font-weight: 700;">
                    <i class="bi bi-whatsapp me-2 fs-6"></i> Hubungi Kami
                </a>
                </div>
                
                <!-- <a href="https://wa.me/6285111034050" target="_blank" class="btn btn-sm btn-success px-3 py-1.5 rounded-pill shadow-xs border-0 d-inline-flex align-items-center justify-content-center font-monospace hover-whatsapp" style="font-size: 0.75rem; background-color: #25D366; font-weight: 700;">
                    <i class="bi bi-whatsapp me-2 fs-6"></i> Hubungi Kami
                </a> -->
            </div>

        </div>
    </div>
</div>

<style>
    /* Variable Extensions */
    .fw-black { font-weight: 900; }
    .tracking-tight { letter-spacing: -0.03em; }
    .tracking-wider { letter-spacing: 0.08em; }
    .px-2\.5 { padding-left: 0.65rem !important; padding-right: 0.65rem !important; }
    .py-1\.5 { padding-top: 0.35rem !important; padding-bottom: 0.35rem !important; }
    .gap-2\.5 { gap: 0.65rem !important; }
    .mt-0\.5 { margin-top: 0.15rem !important; }
    .bg-slate-50 { background-color: #f8fafc !important; }
    .border-dashed { border-style: dashed !important; }
    .border-light-dark { border-color: #e2e8f0 !important; }
    .shadow-xs { box-shadow: 0 2px 5px rgba(0,0,0,0.015) !important; }
    .lh-relaxed { line-height: 1.625; }

    /* Glassmorphism Compound Effect */
    .match-glass {
        border: 1px solid rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(10px);
    }

    /* Grid watermark background inside circle */
    .illustration-grid {
        background-image: linear-gradient(#e2e8f0 1px, transparent 1px), linear-gradient(90deg, #e2e8f0 1px, transparent 1px);
        background-size: 10px 10px;
    }

    /* Custom Animation Engines */
    @keyframes spin-slow {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .animate-spin-slow {
        animation: spin-slow 16s infinite linear;
    }

    @keyframes bounce-subtle {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px) rotate(3deg); }
    }
    .animate-bounce-subtle {
        animation: bounce-subtle 4s infinite ease-in-out;
    }

    @keyframes pulse-ring {
        0% { transform: translate(-50%, -50%) scale(0.85); opacity: 0.9; }
        100% { transform: translate(-50%, -50%) scale(1.25); opacity: 0; }
    }
    .animate-pulse-ring {
        animation: pulse-ring 3s infinite cubic-bezier(0.25, 1, 0.5, 1);
    }

    @keyframes dot-bounce {
        0%, 100% { transform: translateY(0); opacity: 0.3; }
        50% { transform: translateY(-4px); opacity: 1; }
    }
    .modern-loader-dots {
        display: inline-flex;
        gap: 3px;
        align-items: center;
    }
    .modern-loader-dots .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        animation: dot-bounce 1.2s infinite ease-in-out;
    }
    .modern-loader-dots .dot:nth-child(2) { animation-delay: 0.2s; }
    .modern-loader-dots .dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.8s forwards ease-out;
    }

    /* Micro Interaction Hover Rules */
    .hover-scale-card {
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .hover-scale-card:hover {
        transform: translateY(-2px);
        background-color: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
    }

    .btn-hover-refresh {
        transition: all 0.2s ease-in-out;
    }
    .btn-hover-refresh:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 8px 22px rgba(13, 110, 253, 0.28) !important;
    }
    .btn-hover-refresh:hover .animate-spin-hover {
        display: inline-block;
        animation: spin-slow 1s infinite linear;
    }

    .btn-hover-back:hover {
        background-color: #f1f5f9;
        color: #334155 !important;
        border-color: #cbd5e1 !important;
    }

    .hover-whatsapp {
        transition: transform 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-whatsapp:hover {
        background-color: #128C7E !important;
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35) !important;
    }
</style>
@endsection