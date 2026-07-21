{{-- Timeline status approval. Pakai: @include('partials._approval_timeline', ['arsip' => $arsip]) --}}
@php
    $arsip->loadMissing('approvals.delegatedFrom');
    $steps = $arsip->approvals;
@endphp
@if($steps->isNotEmpty())
<div class="d-flex flex-column gap-1">
    @foreach($steps as $s)
        @php
            $c = match($s->status) {
                'approved' => ['bg'=>'#dcfce7','text'=>'#166534','icon'=>'bi-check-circle-fill','label'=>'Disetujui'],
                'rejected' => ['bg'=>'#fee2e2','text'=>'#991b1b','icon'=>'bi-x-circle-fill','label'=>'Ditolak'],
                default    => ['bg'=>'#f1f5f9','text'=>'#475569','icon'=>'bi-clock','label'=>'Menunggu'],
            };
        @endphp
        <div class="d-flex align-items-center gap-2 px-2 py-1 rounded-2" style="background: {{ $c['bg'] }};">
            <i class="bi {{ $c['icon'] }}" style="color: {{ $c['text'] }};"></i>
            <div class="flex-grow-1 lh-1">
                <span class="fw-bold" style="font-size:0.72rem; color: {{ $c['text'] }};">{{ $s->step_order }}. {{ $s->role_label }}</span>
                <span class="text-muted" style="font-size:0.66rem;">
                    — {{ $s->approver->name ?? ($s->role_label === 'Departemen IT' ? 'Tim IT' : 'belum ditentukan') }}
                    @if($s->acted_at) · {{ $s->acted_at->format('d/m/Y H:i') }} @endif
                </span>
                @if($s->delegated_from_id && $s->delegatedFrom)
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1"
                          style="font-size:0.55rem; padding:2px 6px;"
                          title="Diwakilkan dari {{ $s->delegatedFrom->name }}">
                        <i class="bi bi-arrow-return-left"></i>
                        WAKIL DARI {{ $s->delegatedFrom->name }}
                    </span>
                @endif
                @if($s->status === 'rejected' && $s->note)
                    <div class="text-danger" style="font-size:0.64rem;">Alasan: {{ $s->note }}</div>
                @endif
            </div>
            <span class="badge" style="background: {{ $c['text'] }}; font-size:0.58rem;">{{ strtoupper($c['label']) }}</span>
        </div>
    @endforeach
</div>
@else
    <div class="text-muted small fst-italic">Tidak ada alur persetujuan.</div>
@endif
