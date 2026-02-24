<style>
.modal-header .btn-light:hover {
    background-color: #e3f2fd;
}
</style>


<div class="modal fade" id="modalViewBukti"
     tabindex="-1"
     aria-hidden="true"
     data-bs-backdrop="static">

  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content rounded-4 shadow overflow-hidden">

      <div class="modal-header bg-info text-white px-3 py-2 position-relative">

    {{-- LEFT : TITLE --}}
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-text fs-5"></i>
        <span class="fw-semibold">Bukti Scan Dokumen</span>
    </div>

    {{-- CENTER : ACTION BUTTON --}}
    <div class="d-flex gap-2 position-absolute start-50 translate-middle-x">
        <a id="btnOpenTab"
           class="btn btn-sm btn-light rounded-circle"
           target="_blank"
           title="Buka di tab baru">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>

        <a id="btnDownload"
           class="btn btn-sm btn-light rounded-circle"
           download
           title="Download">
            <i class="bi bi-download"></i>
        </a>
    </div>

    {{-- RIGHT : CLOSE --}}
    <button type="button"
            class="btn-close btn-close-white position-absolute end-0 me-3"
            style="top:50%; transform:translateY(-50%)"
            data-bs-dismiss="modal"
            aria-label="Close"></button>

</div>


      {{-- BODY --}}
      <div class="modal-body p-0 bg-dark" style="height:82vh;">
        <div class="w-100 h-100 d-flex justify-content-center align-items-center bg-secondary-subtle">

          {{-- IMAGE --}}
          <img id="buktiImage"
               src=""
               alt="Bukti Scan"
               class="img-fluid d-none"
               style="max-height:100%; max-width:100%; object-fit:contain;">

          {{-- PDF --}}
          <iframe id="buktiFrame"
                  src=""
                  class="w-100 h-100 d-none"
                  style="border:none;"></iframe>

        </div>
      </div>

    </div>
  </div>
</div>


<script>
function showBukti(url) {
    const modalEl = document.getElementById('modalViewBukti');
    const frame   = document.getElementById('buktiFrame');
    const img     = document.getElementById('buktiImage');
    const openTab = document.getElementById('btnOpenTab');
    const dl      = document.getElementById('btnDownload');

    frame.classList.add('d-none');
    img.classList.add('d-none');
    frame.src = '';
    img.src = '';

    openTab.href = url;
    dl.href = url;

    const ext = url.split('.').pop().toLowerCase();

    if (['jpg','jpeg','png','gif','webp','bmp'].includes(ext)) {
        img.src = url;
        img.classList.remove('d-none');
    } else {
        frame.src = url;
        frame.classList.remove('d-none');
    }

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}
</script>
