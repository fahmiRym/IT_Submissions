<!DOCTYPE html>
<html>
<head>
    <title>Laporan Eksekutif Arsip</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #1e3a8a; }
        .header p { margin: 5px 0; color: #555; font-size: 9pt; }
        
        .box { border: 1px solid #ddd; padding: 10px; border-radius: 5px; background-color: #f9fafb; margin-bottom: 20px; }
        .box-title { margin: 0 0 10px 0; font-size: 11pt; color: #1d4ed8; border-bottom: 1px solid #bfdbfe; padding-bottom: 5px; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        th { background-color: #e5e7eb; font-weight: bold; color: #1f2937; }
        td { font-size: 9pt; }
        .text-left { text-align: left; }
        
        .footer { margin-top: 30px; text-align: right; font-size: 8pt; color: #777; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>IT SUBMISSIONS REPORT</h2>
        <p>Filter: {{ $filterDate }} | Departemen: {{ $departmentName }}</p>
    </div>

    <!-- 1. SUMMARY & CHARTS -->
    <table style="width: 100%; border: none; margin-bottom: 20px;">
        <tr style="vertical-align: top;">
            <!-- Left: Text & Analysis -->
            <td style="border: none; width: 40%; padding-right: 15px;">
                <div class="box" style="height: 250px;">
                    <h3 class="box-title">ANALISIS PEMBATALAN & KINERJA</h3>
                    <p style="text-align: justify; margin-bottom: 15px; line-height: 1.4; font-size: 10pt;">{!! $conclusion !!}</p>
                    <p style="font-size: 9pt; color: #666; font-style: italic;">Note: Fokus analisis adalah tingkat pembatalan (Jenis Pengajuan: Cancel) yang mengindikasikan kesalahan input.</p>
                </div>
            </td>
            <!-- Center: Top Dept -->
            <td style="border: none; width: 30%; padding-left: 5px; padding-right: 5px;">
                <div class="box" style="height: 250px;">
                    <h3 class="box-title" style="font-size: 10pt;">DEPARTEMEN TERAKTIF</h3>
                    <div style="text-align: center; margin-top: 10px;">
                        <img src="{{ $chartBarUrl }}" style="max-height: 180px; width: 100%;" alt="Activity Chart">
                    </div>
                </div>
            </td>
            <!-- Right: Top Cancel -->
            <td style="border: none; width: 30%; padding-left: 5px;">
                <div class="box" style="height: 250px; background-color: #fef2f2; border-color: #fca5a5;">
                    <h3 class="box-title" style="color: #b91c1c; border-color: #fecaca; font-size: 10pt;">PENGAJU PEMBATALAN TERBANYAK</h3>
                    <div style="text-align: center; margin-top: 10px;">
                        <img src="{{ $chartPieUrl }}" style="max-height: 180px; width: 100%;" alt="Cancel Chart">
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- 2. MATRIX TABLE -->
    <h3 style="margin-bottom: 5px; margin-top: 0; color: #1f2937;">RINCIAN AKTIVITAS PER DEPARTEMEN</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="text-align: left;">Departemen</th>
                <th style="width: 10%;">Cancel</th>
                <th style="width: 10%;">Adjust</th>
                <th style="width: 10%;">Mutasi</th>
                <th style="width: 10%;">Bundel</th>
                <th style="width: 10%;">Memo</th>
                <th style="width: 12%; background-color: #dbeafe;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pivotData as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="text-left font-bold">{{ $row->name }}</td>
                <td>{{ $row->cancel ?: '-' }}</td>
                <td>{{ $row->adjust ?: '-' }}</td>
                <td>{{ $row->mutasi ?: '-' }}</td>
                <td>{{ $row->bundel ?: '-' }}</td>
                <td>{{ $row->memo ?: '-' }}</td>
                <td style="background-color: #eff6ff; font-weight: bold;">{{ $row->total }}</td>
            </tr>
            @endforeach
            
            <!-- GRAND TOTAL -->
            <tr style="background-color: #f3f4f6; font-weight: bold;">
                <td colspan="2" style="text-align: right; padding-right: 10px;">GRAND TOTAL</td>
                <td>{{ $pivotData->sum('cancel') }}</td>
                <td>{{ $pivotData->sum('adjust') }}</td>
                <td>{{ $pivotData->sum('mutasi') }}</td>
                <td>{{ $pivotData->sum('bundel') }}</td>
                <td>{{ $pivotData->sum('memo') }}</td>
                <td style="background-color: #dbeafe; color: #1e40af;">{{ $pivotData->sum('total') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 3. USER CANCEL LIST -->
    @if(count($userCancelList) > 0)
    <div style="page-break-inside: avoid; margin-top: 20px;">
        <h3 style="margin-bottom: 5px; color: #b91c1c; border-bottom: 2px solid #fecaca; padding-bottom: 5px; display: inline-block;">
            DAFTAR USER SERING CANCEL
        </h3>
        <table style="width: 60%;">
            <thead>
                <tr>
                    <th style="width: 10%;">Rank</th>
                    <th style="text-align: left;">Nama User</th>
                    <th style="text-align: left;">Departemen</th>
                    <th style="width: 20%; background-color: #fee2e2; color: #991b1b;">Jumlah Cancel</th>
                </tr>
            </thead>
            <tbody>
                @foreach($userCancelList as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-left font-bold">{{ $user['name'] }}</td>
                    <td class="text-left">{{ $user['dept'] }}</td>
                    <td style="background-color: #fef2f2; font-weight: bold; color: #991b1b;">{{ $user['count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        Generated by E-Arsip System | {{ now()->format('d M Y H:i') }}
    </div>

</body>
</html>
