@extends('layouts.app')

@section('title', 'Kelola APK Android')
@section('page-title', '📱 Kelola APK Android')

@section('content')
<div class="row g-4">
    {{-- ═════════════════════════════════════════════════════════ --}}
    {{-- LEFT: Form tambah/update app version                    --}}
    {{-- ═════════════════════════════════════════════════════════ --}}
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px;">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Tambah / Update Versi App</h6>
                <p class="text-muted small mb-0">Pakai `app_slug` sama untuk update (upsert).</p>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.app-versions.upsert') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">SLUG APP <span class="text-danger">*</span></label>
                        <input type="text" name="app_slug" class="form-control bg-light border-0 py-2 rounded-3 font-monospace"
                               placeholder="itsubmissions"
                               pattern="[a-z0-9_-]+"
                               title="huruf kecil, angka, underscore, dash saja"
                               required>
                        <small class="text-muted" style="font-size:0.7rem;">Identifier unik: <code>itsubmissions</code>, <code>itapproval</code>, dll.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">NAMA APP <span class="text-danger">*</span></label>
                        <input type="text" name="app_name" class="form-control bg-light border-0 py-2 rounded-3"
                               placeholder="IT Submissions" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-bold">VERSION NAME <span class="text-danger">*</span></label>
                            <input type="text" name="latest_version" class="form-control bg-light border-0 py-2 rounded-3 font-monospace"
                                   placeholder="1.2.3" required>
                            <small class="text-muted" style="font-size:0.7rem;">Semver ditampilkan ke user</small>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-bold">VERSION CODE <span class="text-danger">*</span></label>
                            <input type="number" name="version_code" class="form-control bg-light border-0 py-2 rounded-3 text-center"
                                   min="1" placeholder="1" required>
                            <small class="text-muted" style="font-size:0.7rem;">Integer, comparator</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">APK URL OVERRIDE (Opsional)</label>
                        <input type="url" name="apk_url_override" class="form-control bg-light border-0 py-2 rounded-3"
                               placeholder="https://cdn.example.com/apk/itsubmissions-1.2.3.apk">
                        <small class="text-muted" style="font-size:0.7rem;">Kalau APK di CDN eksternal. Kosongkan kalau upload ke server ini.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">CHANGELOG</label>
                        <textarea name="changelog" class="form-control bg-light border-0 rounded-3" rows="4"
                                  placeholder="- Fix login 502&#10;- Multi-worker artisan serve"></textarea>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="force_update" value="0">
                        <input type="checkbox" name="force_update" value="1" id="chkForceUpdate"
                               class="form-check-input" style="cursor:pointer; width:2.6em; height:1.4em;">
                        <label class="form-check-label ms-1 fw-semibold" for="chkForceUpdate">
                            Force Update <small class="text-muted">— user wajib update, tidak bisa dismiss</small>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                        <i class="bi bi-save2-fill me-2"></i>SIMPAN VERSI
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════ --}}
    {{-- RIGHT: List versions + upload APK                       --}}
    {{-- ═════════════════════════════════════════════════════════ --}}
    <div class="col-xl-7">
        @if(session('success'))
            <div class="alert alert-success border-0 rounded-4 shadow-sm d-flex align-items-center mb-3">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-collection-fill text-primary me-2"></i>App Terdaftar
                    <span class="badge bg-primary-subtle text-primary ms-2">{{ $versions->count() }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                @forelse($versions as $v)
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-android2 text-success fs-4"></i>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $v->app_name }}</div>
                                        <div class="font-monospace text-muted" style="font-size:0.72rem;">{{ $v->app_slug }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-dark text-white px-2 py-1 rounded-pill font-monospace" style="font-size:0.7rem;">
                                    v{{ $v->latest_version }} <span class="opacity-75">({{ $v->version_code }})</span>
                                </div>
                                @if($v->force_update)
                                    <div class="badge bg-danger text-white mt-1" style="font-size:0.6rem;">FORCE UPDATE</div>
                                @endif
                            </div>
                        </div>

                        {{-- APK info --}}
                        @if($v->apk_url)
                            <div class="p-2 rounded-3 bg-light mb-2" style="font-size:0.75rem;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-truncate me-2">
                                        <i class="bi bi-file-earmark-arrow-down text-primary me-1"></i>
                                        <a href="{{ $v->apk_url }}" target="_blank" class="text-decoration-none text-primary fw-semibold">
                                            {{ basename(parse_url($v->apk_url, PHP_URL_PATH) ?? '') ?: 'Download APK' }}
                                        </a>
                                    </div>
                                    @if($v->file_size)
                                        <span class="text-muted flex-shrink-0">{{ number_format($v->file_size / 1024 / 1024, 2) }} MB</span>
                                    @endif
                                </div>
                                @if($v->file_hash)
                                    <div class="font-monospace text-muted mt-1" style="font-size:0.62rem; word-break:break-all;">
                                        sha256:{{ substr($v->file_hash, 0, 16) }}…{{ substr($v->file_hash, -8) }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-muted small mb-2 fst-italic">
                                <i class="bi bi-info-circle me-1"></i>Belum ada APK. Upload atau isi URL override.
                            </div>
                        @endif

                        @if($v->changelog)
                            <div class="p-2 rounded-3 border border-info-subtle bg-info bg-opacity-10 mb-2" style="font-size:0.75rem;">
                                <div class="fw-bold text-info-emphasis mb-1"><i class="bi bi-journal-text me-1"></i>Changelog</div>
                                <div class="text-dark" style="white-space:pre-line;">{{ $v->changelog }}</div>
                            </div>
                        @endif

                        <div class="d-flex gap-2 flex-wrap">
                            {{-- Upload APK inline form --}}
                            <form action="{{ route('superadmin.app-versions.upload-apk', $v->id) }}" method="POST"
                                  enctype="multipart/form-data" class="d-flex gap-2 flex-grow-1">
                                @csrf
                                <input type="file" name="apk_file" accept=".apk,application/vnd.android.package-archive"
                                       class="form-control form-control-sm bg-white border" required>
                                <button type="submit" class="btn btn-sm btn-success text-nowrap">
                                    <i class="bi bi-upload me-1"></i>Upload
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form action="{{ route('superadmin.app-versions.destroy', $v->id) }}" method="POST"
                                  onsubmit="return confirm('Hapus versi {{ addslashes($v->app_name) }}?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="opacity-25 mb-3"><i class="bi bi-android2 display-1"></i></div>
                        <div class="text-muted">Belum ada app terdaftar. Isi form di kiri untuk mulai.</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Info endpoint publik --}}
        <div class="alert alert-info border-0 rounded-4 shadow-sm mt-3">
            <div class="fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i>Endpoint Publik (untuk Android Splash)</div>
            <div class="font-monospace small mb-1">
                GET <a href="{{ url('api/mobile/version?app=itsubmissions') }}" target="_blank" class="text-primary">/api/mobile/version?app={slug}</a>
            </div>
            <div class="text-muted" style="font-size:0.75rem;">
                Android app polling endpoint ini di splash. Compare <code>version_code</code> vs
                <code>BuildConfig.VERSION_CODE</code> → kalau server > local → prompt update.
            </div>
        </div>
    </div>
</div>
@endsection
