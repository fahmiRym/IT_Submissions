<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Arsip</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
h1,h2 { margin-bottom: 6px; }
table { width:100%; border-collapse:collapse; margin-top:8px; }
th,td { border:1px solid #333; padding:5px; }
th { background:#eee; }
.footer { position:fixed; bottom:10px; width:100%; text-align:center; font-size:9px; }
</style>
</head>
<body>

<h1>LAPORAN ARSIP</h1>
<p>Tanggal Cetak: {{ now()->format('d-m-Y H:i') }}</p>
<hr>

<h2>Ringkasan Eksekutif</h2>
<p>
Total arsip sebanyak <strong>{{ $totalArsip }}</strong>.
Sebanyak <strong>{{ $persenDone }}%</strong> telah selesai,
dan <strong>{{ $persenOpen }}%</strong> masih dalam proses.
</p>

<p>
Departemen terbanyak: <strong>{{ $topDepartment->department->name ?? '-' }}</strong><br>
User terbanyak: <strong>{{ $topUser->user->name ?? '-' }}</strong>
</p>

<hr>

<h2>Visualisasi</h2>
<p>Status Arsip</p>
<img src="{{ $statusChartUrl }}" width="350">

<p>Arsip per Departemen</p>
<img src="{{ $deptChartUrl }}" width="500">

<hr>

<h2>Rincian Arsip</h2>
<table>
<thead>
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>User</th>
    <th>Departemen</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
@foreach($arsips as $a)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $a->tgl_pengajuan }}</td>
    <td>{{ $a->user->name ?? '-' }}</td>
    <td>{{ $a->department->name ?? '-' }}</td>
    <td>{{ $a->status }}</td>
</tr>
@endforeach
</tbody>
</table>

<div class="footer">
Laporan ini dihasilkan otomatis oleh Sistem E-Arsip
</div>

</body>
</html>
