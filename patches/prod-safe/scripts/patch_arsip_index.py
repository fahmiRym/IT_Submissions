#!/usr/bin/env python3
"""
Patch view arsip index prod-safe: inject dropdown no_transaksi + 3 tombol AKSI (Share, Note, Log Riwayat) + include 2 modal.
Idempoten: cek existence marker sebelum inject.
Usage: python3 patch_arsip_index.py /path/to/superadmin/arsip/index.blade.php
"""
import sys

p = sys.argv[1] if len(sys.argv) > 1 else '/root/it_submissions/resources/views/superadmin/arsip/index.blade.php'
s = open(p).read()

# 1. Inject Share + Note + Log Riwayat button (sebelum DELETE form)
old_delete = '''                                {{-- DELETE --}}
                                <form action="{{ route(\'superadmin.arsip.destroy\', $a->id) }}"'''

new_buttons = '''                                {{-- SHARE (Y2 M4 deploy 2026-07-30) --}}
                                @if(Route::has(\'arsip.shares.index\'))
                                <button type="button"
                                        class="btn btn-sm shadow-sm rounded-3 p-2 d-flex align-items-center btn-share"
                                        style="background:#06b6d4; color:white;"
                                        data-arsip-id="{{ $a->id }}"
                                        data-no-reg="{{ $a->no_registrasi ?? \'\' }}"
                                        title="Bagikan Pengajuan">
                                    <i class="bi bi-share-fill"></i>
                                </button>
                                @endif

                                {{-- PERSONAL NOTES (X3 M3 deploy 2026-07-30) --}}
                                @if(Route::has(\'arsip.notes.index\'))
                                <button type="button"
                                        class="btn btn-sm btn-warning text-dark shadow-sm rounded-3 p-2 d-flex align-items-center btn-notes"
                                        data-arsip-id="{{ $a->id }}"
                                        data-no-reg="{{ $a->no_registrasi ?? \'\' }}"
                                        title="Catatan Personal">
                                    <i class="bi bi-journal-text"></i>
                                </button>
                                @endif

                                {{-- LOG RIWAYAT (X2 deploy 2026-07-30) --}}
                                @if(Route::has(\'superadmin.activity-logs.index\'))
                                <a href="{{ route(\'superadmin.activity-logs.index\', [\'arsip_id\' => $a->id]) }}"
                                   class="btn btn-sm btn-dark text-white shadow-sm rounded-3 p-2 d-flex align-items-center"
                                   title="Log Riwayat Arsip Ini">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                @endif

                                {{-- DELETE --}}
                                <form action="{{ route(\'superadmin.arsip.destroy\', $a->id) }}"'''

if 'btn-share' in s:
    print('buttons already exist, skip button inject')
elif old_delete not in s:
    print('DELETE ANCHOR NOT FOUND — cek format button DELETE di view')
else:
    s = s.replace(old_delete, new_buttons)
    print('DONE injected 3 buttons (Share + Note + Log Riwayat)')

# 2. Replace no_transaksi block dgn collapse dropdown
old_nt = '''                                 {{-- No Transaksi Hierarchy with Monospace & Arrows --}}
                                 @if($a->no_transaksi)
                                     <div class="d-flex flex-column gap-1 ps-2">
                                         @if(!empty($a->no_transaksi_rows))
                                             @foreach($a->no_transaksi_rows as $group)
                                                 @foreach($group as $line)
                                                     @php $isInduk = $loop->first; @endphp
                                                     <div class="d-flex align-items-center {{ $isInduk ? \'mb-1\' : \'ms-3 mb-1\' }}">
                                                         <i class="bi {{ $isInduk ? \'bi-file-earmark-text\' : \'bi-arrow-return-right\' }} hierarchy-connector"></i>
                                                         <span class="text-secondary fw-bold font-monospace" style="font-size: 0.78rem; letter-spacing: -0.2px;">{{ $line }}</span>
                                                     </div>
                                                 @endforeach
                                             @endforeach
                                         @else
                                             <div class="d-flex align-items-center">
                                                 <i class="bi bi-file-earmark-text hierarchy-connector"></i>
                                                 <span class="text-secondary fw-bold font-monospace" style="font-size: 0.78rem;">{{ $a->no_transaksi }}</span>
                                             </div>
                                         @endif
                                     </div>
                                 @endif'''

new_nt = '''                                 {{-- No Transaksi Collapse Dropdown (X2 deploy 2026-07-30) --}}
                                 @if($a->no_transaksi)
                                     @php
                                         $ntTotal = 0;
                                         if(!empty($a->no_transaksi_rows)) {
                                             foreach($a->no_transaksi_rows as $g) { $ntTotal += count($g); }
                                         } else {
                                             $ntTotal = count(preg_split(\'/\\r\\n|\\r|\\n/\', trim($a->no_transaksi), -1, PREG_SPLIT_NO_EMPTY));
                                         }
                                         $ntId = \'notx-sa-\' . $a->id;
                                     @endphp
                                     <div class="mt-1 ps-2">
                                         <button type="button"
                                             class="btn btn-sm p-0 border-0 bg-transparent d-inline-flex align-items-center gap-1"
                                             data-bs-toggle="collapse" data-bs-target="#{{ $ntId }}"
                                             aria-expanded="false" aria-controls="{{ $ntId }}"
                                             style="font-size:0.72rem;">
                                             <i class="bi bi-file-earmark-text text-secondary"></i>
                                             <span class="text-secondary fw-bold">No Transaksi</span>
                                             @if($ntTotal > 1)
                                                 <span class="badge rounded-pill bg-secondary" style="font-size:0.6rem;">{{ $ntTotal }}</span>
                                             @endif
                                             <i class="bi bi-chevron-down text-secondary small"></i>
                                         </button>
                                         <div class="collapse" id="{{ $ntId }}">
                                             <div class="d-flex flex-column gap-1 mt-1">
                                                 @if(!empty($a->no_transaksi_rows))
                                                     @foreach($a->no_transaksi_rows as $group)
                                                         @foreach($group as $line)
                                                             @php $isInduk = $loop->first; @endphp
                                                             <div class="d-flex align-items-center {{ $isInduk ? \'mb-1\' : \'ms-3 mb-1\' }}">
                                                                 <i class="bi {{ $isInduk ? \'bi-file-earmark-text\' : \'bi-arrow-return-right\' }} hierarchy-connector"></i>
                                                                 <span class="text-secondary fw-bold font-monospace" style="font-size: 0.78rem; letter-spacing: -0.2px;">{{ $line }}</span>
                                                             </div>
                                                         @endforeach
                                                     @endforeach
                                                 @else
                                                     <div class="d-flex align-items-center">
                                                         <i class="bi bi-file-earmark-text hierarchy-connector"></i>
                                                         <span class="text-secondary fw-bold font-monospace" style="font-size: 0.78rem;">{{ $a->no_transaksi }}</span>
                                                     </div>
                                                 @endif
                                             </div>
                                         </div>
                                     </div>
                                 @endif'''

if 'notx-sa-' in s:
    print('NT dropdown already exists, skip NT inject')
elif old_nt not in s:
    print('NT ANCHOR NOT FOUND — mungkin format sudah beda di view')
else:
    s = s.replace(old_nt, new_nt)
    print('DONE injected NT collapse dropdown')

# 3. Include modal note + share sebelum @endsection
if 'admin.arsip._note_modal' in s:
    print('Note modal include already exists')
elif '@endsection' not in s:
    print('@endsection NOT FOUND, skip modal include')
else:
    s = s.replace('@endsection', "@include('admin.arsip._note_modal')\n@include('admin.arsip._share_modal')\n@endsection", 1)
    print('DONE injected 2 modal includes')

open(p, 'w').write(s)
print('=== PATCH COMPLETE ===')
