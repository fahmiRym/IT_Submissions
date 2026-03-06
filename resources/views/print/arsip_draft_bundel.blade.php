<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Print Draft Bundel - {{ $arsip->no_registrasi }}</title>

<style>

@page {
    size: A4 portrait;
    margin: 8mm 10mm;
}

body{
    font-family: Arial, sans-serif;
    font-size:10px;
    margin:0;
}

.print-container{
    width:100%;
}

.form-block{
    height:90mm;
    position:relative;
    margin-bottom:2mm;
}

.wrapper{
    border:2px solid #000;
    height:88mm;
}

table{
    border-collapse:collapse;
    width:100%;
}

/* HEADER */

.header-table td{
    border:1.5px solid #000;
}

.header-title{
    font-size:16px;
    font-weight:bold;
    letter-spacing:1px;
}

/* RIGHT INFO BOX */

.info-table td{
    border:1px solid #000;
    font-size:9px;
    padding:2px 4px;
}

/* DATE */

.date-row{
    border-bottom:1.5px solid #000;
    padding:2px 8px;
    margin-bottom:2px;
}

/* MAIN TABLE */

.main-table th,
.main-table td{
    border:1px solid #000;
    padding:3px;
    text-align:center;
}

.main-table th{
    font-size:9px;
}

.main-table td{
    height:16px;
}

/* SIGNATURE */

.signature{
    display:flex;
    height:30mm;
}

.sig-box{
    flex:1;
    text-align:center;
    padding-top:5mm;
}

.sig-title{
    font-size:10px;
}

.sig-name{
    margin-top:10mm;
    font-weight:bold;
    text-decoration:underline;
}

.sig-note{
    font-size:9px;
}

/* CUT LINE */

.cut-line{
    border-bottom:1px dashed #999;
    position:absolute;
    bottom:-1mm;
    width:100%;
}

.cut-line:after{
    content:"✄";
    position:absolute;
    left:0;
    top:-8px;
}

@media print{
.no-print{display:none;}
}

</style>
</head>

<body onload="window.print()">

<div class="print-container">

<div class="no-print" style="text-align:right;margin-bottom:10px;">
<button onclick="window.print()">Cetak</button>
</div>

@php
$allBundels = collect($arsip->bundelItems ?? []);
$chunks = $allBundels->chunk(5);
$totalChunks = $chunks->count();
$displayFormCount = max(3,(int)(ceil($totalChunks/3)*3));
@endphp


@for($k=0;$k<$displayFormCount;$k++)

@php
$currentItems = $chunks->get($k) ?? collect([]);
@endphp


<div class="form-block">

<div class="wrapper">

<!-- HEADER -->

<table class="header-table">

<tr>

<td style="width:25%; padding:4px; vertical-align:top;">
    <div style="font-size: 8px;">No. Registrasi:</div>
    <div style="font-weight: bold; font-size: 11px; margin-top: 2px;">{{ $arsip->no_registrasi }}</div>
    @if($arsip->no_doc)
        <div style="font-size: 8px; margin-top: 5px;">No. Document:</div>
        <div style="font-weight: bold; font-size: 10px; margin-top: 1px;">{{ $arsip->no_doc }}</div>
    @endif
</td>

<td style="width:50%;text-align:center; vertical-align:middle;">
    <div class="header-title">FORM PENGAJUAN ISI BUNDLE</div>
</td>

<td style="width:25%;padding:0;">

<table class="info-table" style="border:none;">

<tr>
<td style="width:40%; border-top:none; border-left:none;">NO.FORM</td>
<td style="border-top:none; border-right:none;">IJ. FR-QCE-021</td>
</tr>

<tr>
<td style="border-left:none;">REV. KE</td>
<td style="border-right:none;">REV. 01</td>
</tr>

<tr>
<td style="border-bottom:none; border-left:none;">TGL EFEKTIF</td>
<td style="border-bottom:none; border-right:none;">23 FEBRUARI 2023</td>
</tr>

</table>

</td>

</tr>

</table>


<div class="date-row">
Tanggal :
<b>{{ \Carbon\Carbon::parse($arsip->tgl_pengajuan)->format('d-m-Y') }}</b>
</div>


<table class="main-table">

<thead>

<tr>

<th rowspan="2" style="width:5%">NO</th>
<th rowspan="2" style="width:16%">SECTION</th>
<th rowspan="2" style="width:16%">BERAT STD</th>

<th colspan="2" style="width:16%">ISI</th>

<th rowspan="2" style="width:15%">BERAT TOTAL</th>
<th rowspan="2" style="width:12%">DIMENSI BUNDLE</th>
<th rowspan="2" style="width:20%">KETERANGAN</th>

</tr>

<tr>
<th>LAMA</th>
<th>BARU</th>
</tr>

</thead>

<tbody>

@for($i=0;$i<5;$i++)

@php
$item = $currentItems->values()->get($i);
@endphp

<tr>

<td>{{ $i+1 }}</td>

<td>{{ $item->no_doc ?? '' }}</td>

<td></td>

<td></td>

<td>{{ $item->qty ?? '' }}</td>

<td></td>

<td></td>

<td style="text-align:left;padding-left:5px;">
{{ $item->keterangan ?? '' }}
</td>

</tr>

@endfor

</tbody>

</table>


<div class="signature">

<div class="sig-box">

<div class="sig-title">YANG MEMBUAT</div>

<div class="sig-name">{{ ucwords(mb_strtolower($arsip->admin->name ?? '')) }}</div>

<div class="sig-note">( TTD & Nama Jelas )</div>

</div>


<div class="sig-box">

<div class="sig-title">YANG MENYETUJUI</div>

<div class="sig-name">MANAGER PRODUCTION</div>

<div class="sig-note">( TTD & Nama Jelas )</div>

</div>


<div class="sig-box">

<div class="sig-title">YANG MENGETAHUI</div>

<div class="sig-name">EDP</div>

<div class="sig-note">( TTD & Nama Jelas )</div>

</div>

</div>


</div>

@if(($k+1)%3!=0)
<div class="cut-line"></div>
@endif

</div>

@endfor

</div>

</body>
</html>