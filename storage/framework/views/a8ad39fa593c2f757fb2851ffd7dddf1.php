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
<p>Tanggal Cetak: <?php echo e(now()->format('d-m-Y H:i')); ?></p>
<hr>

<h2>Ringkasan Eksekutif</h2>
<p>
Total arsip sebanyak <strong><?php echo e($totalArsip); ?></strong>.
Sebanyak <strong><?php echo e($persenDone); ?>%</strong> telah selesai,
dan <strong><?php echo e($persenOpen); ?>%</strong> masih dalam proses.
</p>

<p>
Departemen terbanyak: <strong><?php echo e($topDepartment->department->name ?? '-'); ?></strong><br>
User terbanyak: <strong><?php echo e($topUser->user->name ?? '-'); ?></strong>
</p>

<hr>

<h2>Visualisasi</h2>
<p>Status Arsip</p>
<img src="<?php echo e($statusChartUrl); ?>" width="350">

<p>Arsip per Departemen</p>
<img src="<?php echo e($deptChartUrl); ?>" width="500">

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
<?php $__currentLoopData = $arsips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
    <td><?php echo e($loop->iteration); ?></td>
    <td><?php echo e($a->tgl_pengajuan); ?></td>
    <td><?php echo e($a->user->name ?? '-'); ?></td>
    <td><?php echo e($a->department->name ?? '-'); ?></td>
    <td><?php echo e($a->status); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>

<div class="footer">
Laporan ini dihasilkan otomatis oleh Sistem E-Arsip
</div>

</body>
</html>
<?php /**PATH C:\laragon\www\e_arsip\resources\views\exports\arsip-pdf.blade.php ENDPATH**/ ?>