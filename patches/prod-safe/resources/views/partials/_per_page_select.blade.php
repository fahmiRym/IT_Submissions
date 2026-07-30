{{-- Reusable per-page selector.
     Pakai: @include('partials._per_page_select', ['id' => 'perPageProducts'])
     - $id : DOM id unik per halaman
     - $default (opsional) : nilai default kalau param `per_page` belum ada di URL
     - $sizes (opsional) : override daftar opsi jumlah baris
     Backend: baca request('per_page'); kalau === 'all' → cap ke 99999. --}}
@php
    $id = $id ?? 'perPageSelect';
    $default = $default ?? 15;
    $sizes = $sizes ?? [10, 25, 50, 100, 250, 500, 1000];
    $current = request('per_page', $default);
@endphp
<div class="d-flex align-items-center bg-white rounded-pill px-3 py-1 shadow-sm border">
    <small class="text-secondary fw-bold me-2" style="font-size: 0.72rem; letter-spacing: 0.03em;">SHOW:</small>
    <select id="{{ $id }}"
            class="form-select form-select-sm border-0 bg-transparent fw-bold text-primary py-0 ps-0 pe-4 per-page-select"
            style="width: auto; cursor: pointer; box-shadow: none; font-size: 0.85rem;">
        @foreach($sizes as $size)
            <option value="{{ $size }}" {{ (string) $current === (string) $size ? 'selected' : '' }}>
                {{ $size }} Rows
            </option>
        @endforeach
        <option value="all" {{ $current === 'all' ? 'selected' : '' }}>Unlimited</option>
    </select>
</div>

@once
    @push('scripts')
    <script>
        // Delegated handler — cover semua per-page-select yg di-render di page.
        document.addEventListener('change', function (e) {
            if (!e.target.classList.contains('per-page-select')) return;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', e.target.value);
            url.searchParams.set('page', 1); // reset ke page 1 saat ganti size
            window.location.href = url.toString();
        });
    </script>
    @endpush
@endonce
