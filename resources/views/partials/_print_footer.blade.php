{{-- Footer cetak — gaya minimal: "Dicetak pada <tgl>, <jam> oleh <user> — IT Submissions"
     Pakai: @include('partials._print_footer') --}}
@php
    \Carbon\Carbon::setLocale('id');
    $itsubFooterUser = auth()->user()->name ?? 'System';
    $itsubFooterDate = \Carbon\Carbon::now()->translatedFormat('j F Y, H:i');
@endphp
<div class="itsub-print-footer">
    Dicetak pada <b>{{ $itsubFooterDate }}</b> oleh <b>{{ $itsubFooterUser }}</b> <span class="itsub-brand"> ~ IT Submissions ~</span>
</div>

<style>
    .itsub-print-footer {
        position: fixed;
        left: 0; right: 0; bottom: 4mm;
        text-align: center;
        font-size: 9px;
        color: #475569;
        font-style: italic;
        font-family: 'DejaVu Sans', sans-serif;
        letter-spacing: 0.2px;
        z-index: 999;
    }
    /* .itsubFooterUser {
        position: fixed;
        left: 0; right: 0; bottom: 4mm;
        text-align: center;
        font-size: 9px;
        color: #475569;
        font-style: italic;
        font-family: 'DejaVu Sans', sans-serif;
        letter-spacing: 0.2px;
        z-index: 999;
    } */
    .itsub-print-footer b {
        font-weight: 700;
        color: #1e293b;
        font-style: normal,italic;
    }
    .itsub-print-footer .itsub-brand {
        font-weight: 800;
        color: #f80000;
        font-style: italic;
        letter-spacing: 0.3px;
    }
    @media print {
        .itsub-print-footer { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
