@extends('layouts.app')

@section('title','Pengajuan')
@section('page-title','📁 Pengajuan')

@push('styles')
<style>
    /* Admin Standard Table Tweaks to match Dashboard Style */
    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    .text-xs { font-size: 0.75rem; }
    .text-sm { font-size: 0.875rem; }
</style>
@endpush

@section('content')

{{-- HEADER & ACTION --}}
<div class="d-flex justify-content-between align-items-center mb-4 animate-on-scroll">
    <div>
         <h5 class="fw-bold text-dark mb-1"><i class="bi bi-folder-fill text-primary me-2"></i>Daftar Pengajuan</h5>
         <small class="text-muted">Kelola semua pengajuan Anda di sini.</small>
    </div>
    
    <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm rounded-pill px-4 fw-bold"
            data-bs-toggle="modal"
            data-bs-target="#modalTambahArsip">
        <i class="bi bi-plus-lg fs-6"></i> 
        <span>Buat Baru</span>
    </button>
</div>

{{-- DATA TABLE CARD --}}
<div class="card shadow-sm mb-4 border-0 animate-on-scroll" style="border-radius: 12px; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4">No Registrasi</th>
                        <th>Tgl Pengajuan</th>
                        <th class="text-center">Jenis</th>
                        <th>Departemen</th>
                        <th>Unit</th>
                        <th class="text-center">Qty In</th>
                        <th class="text-center">Qty Out</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($arsips as $a)
                    <tr class="transition-hover">
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $a->no_registrasi }}</div>
                            @if($a->kategori)
                            <span class="badge bg-light text-secondary border border-secondary border-opacity-25 text-xs">
                                {{ $a->kategori }}
                            </span>
                            @endif
                        </td>
                        <td class="text-nowrap text-dark text-sm">
                            <div><i class="bi bi-calendar3 me-1 text-muted"></i> {{ optional($a->tgl_pengajuan)->format('d M Y') }}</div>
                            <div class="text-muted text-xs"><i class="bi bi-clock me-1 ms-1"></i> {{ optional($a->created_at)->format('H:i') }} WIB</div>
                        </td>

                        <td class="text-center">
                            @php
                                $jc = 'secondary';
                                $icon = 'bi-file-earmark';
                                if($a->jenis_pengajuan == 'Cancel') { $jc = 'danger'; $icon = 'bi-x-circle'; }
                                if($a->jenis_pengajuan == 'Adjust') { $jc = 'warning'; $icon = 'bi-sliders'; }
                                if(str_contains($a->jenis_pengajuan, 'Mutasi')) { $jc = 'success'; $icon = 'bi-arrow-left-right'; }
                                if($a->jenis_pengajuan == 'Bundel') { $jc = 'info'; $icon = 'bi-collection'; }
                            @endphp
                            <span class="badge bg-{{ $jc }} bg-opacity-10 text-{{ $jc }} border border-{{ $jc }} border-opacity-25 px-3 py-2 rounded-pill">
                                <i class="bi {{ $icon }} me-1"></i>
                                {{ str_replace('_', ' ', $a->jenis_pengajuan) }}
                            </span>
                        </td>

                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark text-sm">{{ $a->department->name ?? '-' }}</span>
                                <small class="text-xs text-muted">{{ $a->manager->name ?? '-' }}</small>
                            </div>
                        </td>
                        <td class="text-secondary text-sm">{{ $a->unit->name ?? '-' }}</td>

                        {{-- QTY COLUMNS --}}
                        @php
                            $showQty = in_array($a->jenis_pengajuan, ['Adjust', 'Mutasi_Billet', 'Mutasi_Produk', 'Bundel']);
                        @endphp
                        @if($showQty)
                            <td class="text-center fw-bold text-success">{{ (int)$a->total_qty_in }}</td>
                            <td class="text-center fw-bold text-danger">{{ (int)$a->total_qty_out }}</td>
                        @else
                            <td class="text-center text-muted">-</td>
                            <td class="text-center text-muted">-</td>
                        @endif

                        <td class="text-center">
                            @php
                                $colors = [
                                    'Review' => 'info', 'Process' => 'warning', 'Done' => 'success',
                                    'Partial Done' => 'primary', 'Pending' => 'secondary'
                                ];
                                $sc = $colors[$a->ket_process] ?? 'secondary';
                            @endphp
                           <span class="badge bg-{{ $sc }} text-{{ $sc }} bg-opacity-10 border border-{{ $sc }} border-opacity-25 rounded-pill px-3">
                                {{ $a->ket_process }}
                           </span>
                        </td>

                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-sm btn-light text-secondary border hover-bg-slate shadow-sm" onclick="editArsip({{ $a->id }})" title="Edit Data">
                                <i class="bi bi-pencil-square text-primary"></i> Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-light rounded-circle p-4 mb-3">
                                    <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                                </div>
                                <h6 class="text-secondary fw-bold">Belum ada data pengajuan</h6>
                                <p class="text-muted small mb-0">Klik tombol "Buat Baru" untuk membuat pengajuan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($arsips->hasPages())
        <div class="card-footer bg-white border-top border-light p-3">
             {{ $arsips->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

{{-- Include Modal Partial --}}
@include('admin.arsip._create')
@include('admin.arsip._edit')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    // =========================================================================
    // A. LOGIKA TAMPILAN & TAMBAH DATA BARU (CREATE)
    // =========================================================================
    
    // Check if element exists to avoid errors on other pages
    const $jenisSelect  = $('#jenisPengajuanTambahAdmin');
    if(!$jenisSelect.length) return;

    const $wrapKategori = $('#wrapperKategori');

    // 1. SHOW/HIDE SECTION
    $jenisSelect.on('change', function() {
        const val = $(this).val();
        
        // Reset tampilan
        $('.dynamic-section').addClass('d-none');
        // Reset Inputs inside dynamic sections
        $('.dynamic-section input').prop('required', false).val('');
        $('.dynamic-section textarea').prop('required', false).val('');
        
        // Clear dynamic rows
        $('tbody.dynamic-row-container').empty(); 

        if (val === 'Cancel') {
            $wrapKategori.removeClass('d-none');
            $('#sectionNoTrans').removeClass('d-none');
            $('#sectionNoTrans textarea').prop('required', true);
        } 
        else if (val === 'Adjust') {
            $wrapKategori.addClass('d-none');
            $('#sectionAdjust').removeClass('d-none');
        } 
        else if (val && val.includes('Mutasi')) {
            $wrapKategori.addClass('d-none');
            $('#sectionMutasi').removeClass('d-none');
        } 
        else if (val === 'Bundel') {
            $wrapKategori.addClass('d-none');
            $('#sectionBundel').removeClass('d-none');
        }
        else {
            $wrapKategori.addClass('d-none');
        }
    });

    // Helper Random Index
    function getIndex() { return Math.floor(Math.random() * 100000); }

    // 2. TAMBAH BARIS ITEM (CREATE)
    // -- ADJUST --
    $('#btnAddAdjust').on('click', function() {
        let idx = getIndex();
        $('#wrapperAdjust').append(`
            <tr>
                <td><input type="text" name="adjust[${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode" required></td>
                <td><input type="text" name="adjust[${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Barang" required></td>
                <td><input type="number" name="adjust[${idx}][qty_in]" class="form-control form-control-sm border-0 bg-light text-success fw-bold" value="0" style="min-width: 80px;"></td>
                <td><input type="number" name="adjust[${idx}][qty_out]" class="form-control form-control-sm border-0 bg-light text-danger fw-bold" value="0" style="min-width: 80px;"></td>
                <td><input type="text" name="adjust[${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot"></td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    });

    // -- MUTASI --
    window.addMutasiRow = function(targetId, prefixName) {
        let idx = getIndex();
        $(`#${targetId}`).append(`
            <tr>
                <td><input type="text" name="${prefixName}[${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode" required></td>
                <td><input type="text" name="${prefixName}[${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Produk" required></td>
                <td><input type="number" name="${prefixName}[${idx}][qty]" class="form-control form-control-sm border-0 bg-light fw-bold" value="0" required style="min-width: 80px;"></td>
                <td><input type="text" name="${prefixName}[${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot"></td>
                <td><input type="text" name="${prefixName}[${idx}][panjang]" class="form-control form-control-sm border-0 bg-light" placeholder="Pjg"></td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    }
    // Bind click events
    $('#btnAddAsal').on('click', () => window.addMutasiRow('wrapperAsal', 'mutasi_asal'));
    $('#btnAddTujuan').on('click', () => window.addMutasiRow('wrapperTujuan', 'mutasi_tujuan'));

    // -- BUNDEL --
    $('#btnAddBundel').on('click', function() {
        let idx = getIndex();
        $('#wrapperBundel').append(`
            <tr>
                <td><input type="text" name="bundel[${idx}][no_doc]" class="form-control form-control-sm border-0 bg-light" placeholder="No Dokumen" required></td>
                <td><input type="number" name="bundel[${idx}][qty]" class="form-control form-control-sm border-0 bg-light fw-bold" value="1" required></td>
                <td><input type="text" name="bundel[${idx}][keterangan]" class="form-control form-control-sm border-0 bg-light" placeholder="Keterangan"></td>
                <td><button type="button" class="btn btn-sm text-secondary btnRemove"><i class="bi bi-x-circle-fill fs-6 text-danger"></i></button></td>
            </tr>
        `);
    });

    // -- HAPUS BARIS --
    $(document).on('click', '.btnRemove', function() { $(this).closest('tr').remove(); });
    
    // Trigger change saat load agar form create bersih
    if($jenisSelect.length) $jenisSelect.trigger('change');


    // =========================================================================
    // B. LOGIKA SIMPAN PERUBAHAN (EDIT / UPDATE)
    // =========================================================================
    
    $('#formEditArsip').on('submit', function(e) {
        e.preventDefault(); // STOP submit bawaan browser

        let id = $('#editArsipId').val(); // Ambil ID
        
        if(!id) {
            alert("Error: ID Arsip tidak ditemukan! Silakan refresh halaman.");
            return;
        }

        // Susun URL Update
        let baseUrl = window.location.origin; 
        let urlUpdate = baseUrl + '/admin/arsip/' + id; 

        // Siapkan Data (FormData menangani file upload)
        let formData = new FormData(this);
        formData.append('_method', 'PUT'); // Method Spoofing untuk Laravel

        $.ajax({
            url: urlUpdate,
            type: 'POST', // POST with _method=PUT
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('button[type="submit"]', '#formEditArsip').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
            },
            success: function(response) {
                $('#modalEditArsip').modal('hide');
                alert('Data Berhasil Diupdate!');
                location.reload(); 
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                let pesan = xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText;
                alert('Gagal Update: ' + pesan);
                $('button[type="submit"]', '#formEditArsip').prop('disabled', false).text('Simpan Perubahan');
            }
        });
    });

}); // End Document Ready


// =========================================================================
// C. FUNGSI GLOBAL UNTUK EDIT MODAL (Diakses dari tombol tabel)
// =========================================================================

// Helper Add Row Edit
window.addAdjustRowEdit = function(code = '', name = '', qty_in = 0, qty_out = 0, lot = '') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    let html = `
        <tr>
            <td class="ps-3"><input type="text" name="items[adjust][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode" value="${code}" required style="width: 80px;"></td>
            <td><input type="text" name="items[adjust][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Barang" value="${name}" required></td>
            <td><input type="number" name="items[adjust][${idx}][qty_in]" class="form-control form-control-sm text-center border-0 bg-light" value="${qty_in}" style="min-width: 80px;"></td>
            <td><input type="number" name="items[adjust][${idx}][qty_out]" class="form-control form-control-sm text-center border-0 bg-light" value="${qty_out}" style="min-width: 80px;"></td>
            <td><input type="text" name="items[adjust][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot" value="${lot}"></td>
            <td class="text-end pe-2"><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>`;
    $('#wrapperAdjustEdit').append(html);
};

window.addMutasiRowEdit = function(type, code = '', name = '', qty = 1, lot = '', panjang = '') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    let color = type === 'asal' ? 'danger' : 'success';
    let html = `
        <tr>
            <td class="ps-3"><input type="text" name="items[mutasi][${type}][${idx}][product_code]" class="form-control form-control-sm border-0 bg-light" placeholder="Kode" value="${code}" required style="width: 80px;"></td>
            <td><input type="text" name="items[mutasi][${type}][${idx}][nama_produk]" class="form-control form-control-sm border-0 bg-light" placeholder="Nama Produk" value="${name}" required></td>
            <td><input type="number" name="items[mutasi][${type}][${idx}][qty]" class="form-control form-control-sm text-center border-0 bg-light" value="${qty}" style="min-width: 80px;"></td>
            <td><input type="text" name="items[mutasi][${type}][${idx}][lot]" class="form-control form-control-sm border-0 bg-light" placeholder="Lot" value="${lot}"></td>
            <td><input type="text" name="items[mutasi][${type}][${idx}][panjang]" class="form-control form-control-sm border-0 bg-light" placeholder="Pjg" value="${panjang}"></td>
            <td class="text-end pe-2"><button type="button" class="btn btn-link text-${color} p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>`;
    if(type === 'asal') $('#wrapperAsalEdit').append(html);
    else $('#wrapperTujuanEdit').append(html);
};

window.addBundelRowEdit = function(no_doc = '', qty = 1, ket = '') {
    let idx = Date.now() + Math.floor(Math.random() * 1000);
    let html = `
        <tr>
            <td class="ps-3"><input type="text" name="items[bundel][${idx}][no_doc]" class="form-control form-control-sm border-0 bg-light" placeholder="No Dokumen" value="${no_doc}" required></td>
            <td><input type="number" name="items[bundel][${idx}][qty]" class="form-control form-control-sm text-center border-0 bg-light" value="${qty}" style="min-width: 80px;"></td>
            <td><input type="text" name="items[bundel][${idx}][keterangan]" class="form-control form-control-sm border-0 bg-light" placeholder="Ket..." value="${ket}"></td>
            <td class="text-end pe-2"><button type="button" class="btn btn-link text-info p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle-fill"></i></button></td>
        </tr>`;
    $('#wrapperBundelEdit').append(html);
};

// FUNGSI UTAMA EDIT (AJAX CALL)
window.editArsip = function(id) {
    // 1. Reset Form Edit
    $('#formEditArsip')[0].reset();
    $('.dynamic-section-edit').addClass('d-none'); 
    $('#wrapperAdjustEdit, #wrapperAsalEdit, #wrapperTujuanEdit, #wrapperBundelEdit').empty(); 
    
    // 2. Set ID ke Hidden Input
    $('#editArsipId').val(id); 
    
    // 3. Ambil Data dari Server
    let urlShow = "{{ route('admin.arsip.edit', ':id') }}"; 
    urlShow = urlShow.replace(':id', id);

    $.ajax({
    url: urlShow,
    type: "GET",
    success: function(response) {
        let data = response.data;

        // Reset Tampilan
        $('#sectionNoTransEdit').addClass('d-none'); 
        $('#editWrapperKategori').addClass('d-none');
        $('#sectionBundelEdit').addClass('d-none');
        $('#sectionAdjustEdit').addClass('d-none');
        $('#sectionMutasiEdit').addClass('d-none');
        
        $('#editNoTransaksi').prop('required', false);

        // Update Action URL
        let urlUpdate = "{{ route('admin.arsip.update', ':id') }}";
        urlUpdate = urlUpdate.replace(':id', data.id);
        $('#formEditArsip').attr('action', urlUpdate);

        // Fill Inputs
        $('#editNoRegistrasi').val(data.no_registrasi);
        $('#editJenisPengajuan').val(data.jenis_pengajuan); 
        $('#editDepartment').val(data.department_id);
        $('#editUnit').val(data.unit_id);
        $('#editManager').val(data.manager_id);
        $('#editKeterangan').val(data.keterangan);
        
        // Link Bukti Scan
        // Link Bukti Scan
        if(data.bukti_scan) {
            $('#linkBuktiSaatIni').html(
                `<a href="/preview-file/${data.bukti_scan}" target="_blank" class="text-decoration-none fw-bold small">
                    <i class="bi bi-file-earmark-pdf text-danger"></i> Lihat File
                </a>`
            );
        } else {
            $('#linkBuktiSaatIni').text('Belum ada file.');
        }

        // Logic display based on Jenis
        let jenis = data.jenis_pengajuan;

        if(jenis === 'Cancel') {
            $('#sectionNoTransEdit').removeClass('d-none');
            $('#editWrapperKategori').removeClass('d-none');
            $('#editNoTransaksi').val(data.no_transaksi).prop('required', true);
            $('#editKategori').val(data.kategori); 
        } 
        else if(jenis === 'Bundel') {
            $('#sectionBundelEdit').removeClass('d-none');
            if(data.bundel_items) {
                data.bundel_items.forEach(item => {
                    addBundelRowEdit(item.no_doc, item.qty, item.keterangan); 
                });
            }
        }
        else if(jenis === 'Adjust') {
            $('#sectionAdjustEdit').removeClass('d-none');
            if(data.adjust_items) {
                data.adjust_items.forEach(item => {
                    let code = item.product_code || '';
                    let nama = item.product_name || item.no_doc || '';
                    let qty_in = item.qty_in || 0;
                    let qty_out = item.qty_out || 0;
                    let lot = item.lot || item.keterangan || '';
                    addAdjustRowEdit(code, nama, qty_in, qty_out, lot);
                });
            }
        }
        else if(jenis && jenis.includes('Mutasi')) {
            $('#sectionMutasiEdit').removeClass('d-none');
            if(data.mutasi_items) {
                data.mutasi_items.forEach(item => {
                    let type = (item.type === 'asal') ? 'asal' : 'tujuan';
                    let code = item.product_code || '';
                    let nama = item.product_name || item.no_doc || '';
                    let qty = item.qty || 0;
                    let lot = item.lot || item.keterangan || '';
                    let panjang = item.panjang || '';
                    addMutasiRowEdit(type, code, nama, qty, lot, panjang);
                });
            }
        }

        $('#modalEditArsip').modal('show');
    },
    error: function(xhr) {
        console.error("Error:", xhr);
        alert('Gagal mengambil data. Silakan coba lagi.');
    }
});
}
</script>
@endpush