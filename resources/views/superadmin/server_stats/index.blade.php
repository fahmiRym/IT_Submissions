@extends('layouts.app')

@section('title', 'Statistik Server')
@section('page-title', 'Server Health & Metrics')

@push('styles')
<style>
    .ss-kpi {
        border-radius: 18px;
        padding: 18px;
        color: #fff;
        position: relative;
        overflow: hidden;
        min-height: 130px;
    }
    .ss-kpi-icon {
        position: absolute; right: -10px; bottom: -10px;
        font-size: 5rem; opacity: 0.16; transform: rotate(-8deg);
    }
    .ss-kpi-cpu  { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
    .ss-kpi-mem  { background: linear-gradient(135deg, #0891b2 0%, #155e75 100%); }
    .ss-kpi-disk { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); }
    .ss-kpi-db   { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); }
    .ss-kpi-lbl  { font-size: 0.65rem; letter-spacing: 0.15em; font-weight: 800; opacity: 0.85; text-transform: uppercase; }
    .ss-kpi-val  { font-size: 2.1rem; font-weight: 800; letter-spacing: -1px; line-height: 1; margin: 4px 0; }
    .ss-kpi-sub  { font-size: 0.72rem; opacity: 0.8; }
    .ss-kpi-bar  { background: rgba(255,255,255,0.18); height: 6px; border-radius: 6px; overflow: hidden; margin-top: 10px; }
    .ss-kpi-bar > div { background: rgba(255,255,255,0.85); height: 100%; transition: width 0.6s ease; }

    .ss-card { border-radius: 18px; border: 1px solid #e2e8f0; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04); }
    .ss-card-header {
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-bottom: 1px solid #e2e8f0;
        padding: 0.85rem 1.25rem;
        display: flex; align-items: center; gap: 8px;
    }
    .ss-card-header h6 { font-weight: 800; font-size: 0.9rem; margin: 0; }
    .ss-card-body { padding: 1rem 1.25rem; }

    .ss-meta-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9; font-size: 0.85rem; }
    .ss-meta-row:last-child { border-bottom: none; }
    .ss-meta-row .lbl { color: #64748b; }
    .ss-meta-row .val { font-weight: 700; color: #0f172a; }
    .ss-meta-row .val.mono { font-family: monospace; color: #4f46e5; }

    .ss-ext { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 99px; font-size: 0.65rem; font-weight: 700; margin: 2px; }
    .ss-ext.on  { background: #dcfce7; color: #15803d; }
    .ss-ext.off { background: #fee2e2; color: #b91c1c; }
    .ss-ext i { font-size: 0.6rem; }

    .ss-status-dot {
        width: 10px; height: 10px; border-radius: 50%; display: inline-block;
        margin-right: 6px;
    }
    .ss-status-dot.up   { background: #22c55e; box-shadow: 0 0 0 0 rgba(34,197,94,.5); animation: pulse-live 1.6s infinite; }
    .ss-status-dot.warn { background: #f59e0b; }
    .ss-status-dot.down { background: #ef4444; }
    @keyframes pulse-live {
        0% { box-shadow: 0 0 0 0 rgba(34,197,94,.5); }
        70% { box-shadow: 0 0 0 8px rgba(34,197,94,0); }
        100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }
    .live-badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: #fee2e2; color: #b91c1c;
        font-size: 0.6rem; font-weight: 800; letter-spacing: 0.15em;
        padding: 3px 9px; border-radius: 99px;
    }
    .live-badge::before {
        content: ''; width: 6px; height: 6px; border-radius: 50%;
        background: #ef4444;
        animation: pulse-live 1.5s infinite;
    }

    .ss-table-mini { font-size: 0.8rem; margin: 0; }
    .ss-table-mini th { background: #f8fafc; color: #64748b; font-weight: 800; font-size: 0.65rem; letter-spacing: 0.08em; padding: 0.55rem 0.7rem; border-bottom: 1px solid #e2e8f0; }
    .ss-table-mini td { padding: 0.55rem 0.7rem; border-bottom: 1px solid #f1f5f9; }
    .ss-table-mini tr:last-child td { border-bottom: none; }
    .ss-bar-row {
        display: flex; align-items: center; gap: 8px; padding: 6px 0;
    }
    .ss-bar-row .name { font-size: 0.78rem; min-width: 100px; font-weight: 700; color: #1e293b; }
    .ss-bar-row .bar  { flex: 1; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; }
    .ss-bar-row .bar > div { height: 100%; background: linear-gradient(90deg, #4f46e5, #7c3aed); border-radius: 4px; }
    .ss-bar-row .val  { font-size: 0.72rem; font-weight: 700; min-width: 60px; text-align: right; color: #475569; font-family: monospace; }
</style>
@endpush

@section('content')

    {{-- ============ TOP: Hero header live status ============ --}}
    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-speedometer2 text-primary me-2"></i>Server Health
            @php
                $statusOk = ($stats['disk_used_percent'] ?? 0) < 90 && ($stats['mem_used_percent'] ?? 0) < 90;
            @endphp
            <span class="ss-status-dot {{ $statusOk ? 'up' : 'warn' }}"></span>
            <span class="small {{ $statusOk ? 'text-success' : 'text-warning' }} fw-bold ms-1">
                {{ $statusOk ? 'OPERATIONAL' : 'HIGH UTILIZATION' }}
            </span>
        </h5>
        <span class="live-badge ms-auto">LIVE</span>
        <small class="text-muted" id="ssLastUpdate">Last update: just now</small>
    </div>

    {{-- ============ HEALTH SCORE + ALERTS PANEL ============ --}}
    @php
        $hs = $stats['health_score'];
        $hsColor = $hs >= 85 ? '#16a34a' : ($hs >= 60 ? '#f59e0b' : '#dc2626');
        $hsLabel = $hs >= 85 ? 'EXCELLENT' : ($hs >= 60 ? 'WARNING' : 'CRITICAL');
        $hsDash = round(($hs / 100) * 282.6, 1);
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-4">
            <div class="ss-card h-100">
                <div class="ss-card-header">
                    <i class="bi bi-shield-check text-primary"></i>
                    <h6>Health Score</h6>
                </div>
                <div class="ss-card-body text-center py-3">
                    <div class="hs-gauge mx-auto position-relative" style="width:160px; height:160px;">
                        <svg viewBox="0 0 100 100" style="width:160px; height:160px; transform: rotate(-90deg);">
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                            <circle cx="50" cy="50" r="45" fill="none" stroke="{{ $hsColor }}" stroke-width="8"
                                stroke-linecap="round"
                                stroke-dasharray="282.6" stroke-dashoffset="{{ 282.6 - $hsDash }}"/>
                        </svg>
                        <div class="position-absolute" style="top:50%; left:50%; transform:translate(-50%,-50%);">
                            <div style="font-size:2.4rem; font-weight:900; line-height:1; color: {{ $hsColor }}">{{ $hs }}</div>
                            <div style="font-size:0.6rem; letter-spacing:0.15em; font-weight:800; color:{{ $hsColor }}">{{ $hsLabel }}</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-around mt-3 small">
                        <div><div class="fw-bold text-primary" style="font-size:1.1rem;">{{ $stats['response_time_ms'] }}ms</div><div class="text-muted" style="font-size:0.65rem;">RESPONSE</div></div>
                        <div><div class="fw-bold text-info" style="font-size:1.1rem;">{{ $stats['db_response_ms'] }}ms</div><div class="text-muted" style="font-size:0.65rem;">DB QUERY</div></div>
                        <div><div class="fw-bold text-success" style="font-size:1.1rem;">{{ $stats['active_users_24h'] }}</div><div class="text-muted" style="font-size:0.65rem;">ACTIVE 24H</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="ss-card h-100">
                <div class="ss-card-header">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    <h6>System Alerts</h6>
                    <span class="badge bg-secondary ms-auto">{{ count($stats['alerts']) }}</span>
                </div>
                <div class="ss-card-body">
                    @forelse($stats['alerts'] as $a)
                        @php
                            $bg = $a['level'] === 'danger' ? '#fee2e2' : ($a['level'] === 'warning' ? '#fef3c7' : '#dbeafe');
                            $fg = $a['level'] === 'danger' ? '#991b1b' : ($a['level'] === 'warning' ? '#92400e' : '#1e40af');
                        @endphp
                        <div class="d-flex gap-3 align-items-start p-2 rounded-3 mb-2"
                             style="background:{{ $bg }}; border-left: 3px solid {{ $fg }};">
                            <i class="bi {{ $a['icon'] }} mt-1" style="color:{{ $fg }}; font-size:1.2rem;"></i>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold" style="color:{{ $fg }}; font-size:0.85rem;">{{ $a['title'] }}</div>
                                <div class="small" style="color:{{ $fg }}; opacity:0.85;">{{ $a['msg'] }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <div class="display-3 mb-2" style="opacity:0.5;">✨</div>
                            <h6 class="fw-bold text-success">All Systems Operational</h6>
                            <small class="text-muted">Tidak ada alert. Server dalam kondisi optimal.</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ============ 4 KPI CARDS ============ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="ss-kpi ss-kpi-cpu">
                <div class="ss-kpi-lbl"><i class="bi bi-cpu-fill me-1"></i>CPU Load</div>
                <div class="ss-kpi-val" id="kpiCpu">{{ $stats['load_pct_1m'] }}%</div>
                <div class="ss-kpi-sub">1m: {{ number_format($stats['load_1m'], 2) }} · {{ $stats['cpu_count'] }} cores</div>
                <div class="ss-kpi-bar"><div id="kpiCpuBar" style="width: {{ $stats['load_pct_1m'] }}%;"></div></div>
                <i class="bi bi-cpu ss-kpi-icon"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="ss-kpi ss-kpi-mem">
                <div class="ss-kpi-lbl"><i class="bi bi-memory me-1"></i>Memory</div>
                <div class="ss-kpi-val" id="kpiMem">{{ $stats['mem_used_percent'] }}%</div>
                <div class="ss-kpi-sub" id="kpiMemSub">{{ $stats['mem_usage'] }} / {{ $stats['mem_limit'] }}</div>
                <div class="ss-kpi-bar"><div id="kpiMemBar" style="width: {{ $stats['mem_used_percent'] }}%;"></div></div>
                <i class="bi bi-memory ss-kpi-icon"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="ss-kpi ss-kpi-disk">
                <div class="ss-kpi-lbl"><i class="bi bi-hdd-fill me-1"></i>Disk</div>
                <div class="ss-kpi-val">{{ $stats['disk_used_percent'] }}%</div>
                <div class="ss-kpi-sub">Free {{ $stats['disk_free'] }} / Total {{ $stats['disk_total'] }}</div>
                <div class="ss-kpi-bar"><div style="width: {{ $stats['disk_used_percent'] }}%;"></div></div>
                <i class="bi bi-hdd ss-kpi-icon"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="ss-kpi ss-kpi-db">
                <div class="ss-kpi-lbl"><i class="bi bi-database-fill me-1"></i>Database</div>
                <div class="ss-kpi-val">{{ $stats['database_size_mb'] }} <small style="font-size:1rem; opacity:0.7;">MB</small></div>
                <div class="ss-kpi-sub text-truncate">{{ $stats['database_name'] }} · {{ $stats['database_host'] }}</div>
                <div class="ss-kpi-bar"><div style="width: {{ min(100, ($stats['database_size_mb'] / 1024) * 100) }}%;"></div></div>
                <i class="bi bi-database ss-kpi-icon"></i>
            </div>
        </div>
    </div>

    {{-- ============ LIVE CHART + TRAFFIC ============ --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-7">
            <div class="ss-card">
                <div class="ss-card-header">
                    <i class="bi bi-activity text-primary"></i>
                    <h6>Resource Usage (Live)</h6>
                    <span class="live-badge ms-auto">REAL-TIME · 5s</span>
                </div>
                <div class="ss-card-body">
                    <canvas id="chartLive" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="ss-card">
                <div class="ss-card-header">
                    <i class="bi bi-pie-chart-fill text-primary"></i>
                    <h6>Distribusi Jenis Pengajuan</h6>
                </div>
                <div class="ss-card-body">
                    <canvas id="chartJenis" height="160"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ TRAFFIC: 14 hari + 24 jam ============ --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-7">
            <div class="ss-card">
                <div class="ss-card-header">
                    <i class="bi bi-bar-chart-fill text-primary"></i>
                    <h6>Submission Traffic (14 hari)</h6>
                </div>
                <div class="ss-card-body">
                    <canvas id="chartTraffic" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="ss-card">
                <div class="ss-card-header">
                    <i class="bi bi-clock-history text-primary"></i>
                    <h6>Traffic Per Jam (24h)</h6>
                </div>
                <div class="ss-card-body">
                    <canvas id="chartHourly" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ TABLE BREAKDOWN + STORAGE + TOP USERS ============ --}}
    <div class="row g-3 mb-4">
        {{-- Table sizes --}}
        <div class="col-12 col-lg-5">
            <div class="ss-card">
                <div class="ss-card-header">
                    <i class="bi bi-table text-primary"></i>
                    <h6>Top 10 Tabel Database</h6>
                </div>
                <div class="ss-card-body p-0">
                    <table class="table ss-table-mini">
                        <thead><tr><th class="ps-3">TABLE</th><th class="text-end">ROWS</th><th class="text-end pe-3">SIZE</th></tr></thead>
                        <tbody>
                        @forelse($stats['table_breakdown'] as $t)
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-primary">{{ $t['name'] }}</td>
                                <td class="text-end">{{ number_format($t['rows_count']) }}</td>
                                <td class="text-end pe-3 fw-bold">{{ number_format($t['size_mb'], 2) }} <small class="text-muted">MB</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data tabel.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Storage --}}
        <div class="col-12 col-lg-4">
            <div class="ss-card h-100">
                <div class="ss-card-header">
                    <i class="bi bi-folder-fill text-primary"></i>
                    <h6>Storage Breakdown</h6>
                </div>
                <div class="ss-card-body">
                    @php $maxSize = max(1, max(array_column($stats['storage_breakdown'], 'size_mb'))); @endphp
                    @foreach($stats['storage_breakdown'] as $s)
                        <div class="ss-bar-row">
                            <div class="name">{{ $s['label'] }}</div>
                            <div class="bar"><div style="width: {{ ($s['size_mb'] / $maxSize) * 100 }}%;"></div></div>
                            <div class="val">{{ number_format($s['size_mb'], 1) }} MB</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Top Users --}}
        <div class="col-12 col-lg-3">
            <div class="ss-card h-100">
                <div class="ss-card-header">
                    <i class="bi bi-people-fill text-primary"></i>
                    <h6>Top Contributors</h6>
                </div>
                <div class="ss-card-body">
                    @forelse($stats['top_users'] as $u)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                 style="width:32px;height:32px;background:linear-gradient(135deg,#4f46e5,#7c3aed);font-size:0.75rem;">
                                {{ strtoupper(substr($u['name'], 0, 1)) }}
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-truncate" style="font-size:0.8rem;">{{ $u['name'] }}</div>
                                <div class="text-muted" style="font-size:0.65rem;">{{ $u['total'] }} pengajuan</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">Belum ada data.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ============ SOFTWARE INFO + QUEUE + EXTENSIONS ============ --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="ss-card h-100">
                <div class="ss-card-header">
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    <h6>Software Environment</h6>
                </div>
                <div class="ss-card-body">
                    <div class="ss-meta-row"><span class="lbl">App Env</span><span class="val mono">{{ strtoupper($stats['app_env']) }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">App Debug</span><span class="val mono">{{ $stats['app_debug'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">App URL</span><span class="val text-truncate" style="max-width:60%">{{ $stats['app_url'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">PHP Version</span><span class="val mono">{{ $stats['php_version'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">Laravel</span><span class="val mono">v{{ $stats['laravel_version'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">OS</span><span class="val">{{ $stats['os'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">Web Server</span><span class="val text-truncate" style="max-width:60%">{{ $stats['server_software'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">Hostname</span><span class="val mono">{{ $stats['server_name'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">IP</span><span class="val mono">{{ $stats['server_ip'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">Timezone</span><span class="val">{{ $stats['timezone'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">Uptime</span><span class="val">{{ $stats['uptime'] }}</span></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="ss-card mb-3">
                <div class="ss-card-header">
                    <i class="bi bi-list-task text-primary"></i>
                    <h6>Queue Health</h6>
                </div>
                <div class="ss-card-body">
                    <div class="ss-meta-row"><span class="lbl">Default Driver</span><span class="val mono">{{ $stats['queue_health']['driver'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">Pending Jobs</span>
                        <span class="val">
                            @if($stats['queue_health']['pending'] === null)
                                <span class="text-muted small">(tabel tidak ada)</span>
                            @else
                                <span class="badge bg-info-subtle text-info">{{ number_format($stats['queue_health']['pending']) }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="ss-meta-row"><span class="lbl">Failed Jobs</span>
                        <span class="val">
                            @if($stats['queue_health']['failed'] === null)
                                <span class="text-muted small">(tabel tidak ada)</span>
                            @elseif($stats['queue_health']['failed'] > 0)
                                <span class="badge bg-danger">{{ number_format($stats['queue_health']['failed']) }}</span>
                            @else
                                <span class="badge bg-success-subtle text-success"><i class="bi bi-check2-circle me-1"></i>0</span>
                            @endif
                        </span>
                    </div>
                    <div class="ss-meta-row"><span class="lbl">Pending Approvals</span>
                        <span class="val">
                            @if($stats['pending_approvals'] > 0)
                                <span class="badge bg-warning-subtle text-warning">{{ number_format($stats['pending_approvals']) }}</span>
                            @else
                                <span class="badge bg-success-subtle text-success"><i class="bi bi-check2-circle me-1"></i>0</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="ss-card mb-3">
                <div class="ss-card-header">
                    <i class="bi bi-gear-wide-connected text-primary"></i>
                    <h6>Application Drivers</h6>
                </div>
                <div class="ss-card-body">
                    <div class="ss-meta-row"><span class="lbl">Cache</span><span class="val mono">{{ $stats['cache_driver'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">Session</span><span class="val mono">{{ $stats['session_driver'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">Mail</span><span class="val mono">{{ $stats['mail_driver'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">Log Size</span><span class="val mono">{{ $stats['app_logs_size'] }}</span></div>
                    <div class="ss-meta-row"><span class="lbl">Recent Errors</span>
                        <span class="val">
                            @if($stats['recent_error_count'] > 0)
                                <span class="badge bg-danger-subtle text-danger">{{ $stats['recent_error_count'] }}</span>
                            @else
                                <span class="badge bg-success-subtle text-success">0</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="ss-card">
                <div class="ss-card-header">
                    <i class="bi bi-puzzle-fill text-primary"></i>
                    <h6>PHP Extensions</h6>
                </div>
                <div class="ss-card-body">
                    @foreach($stats['php_extensions'] as $ext => $on)
                        <span class="ss-ext {{ $on ? 'on' : 'off' }}">
                            <i class="bi {{ $on ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>{{ $ext }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const liveCtx = document.getElementById('chartLive');
    const trafficCtx = document.getElementById('chartTraffic');

    // Live chart — rolling 20 points
    const labels = Array(20).fill('');
    const cpuData = Array(20).fill(null);
    const memData = Array(20).fill(null);
    cpuData[19] = {{ $stats['load_pct_1m'] }};
    memData[19] = {{ $stats['mem_used_percent'] }};

    const chartLive = new Chart(liveCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'CPU %',
                    data: cpuData,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.12)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    borderWidth: 2,
                },
                {
                    label: 'Memory %',
                    data: memData,
                    borderColor: '#0891b2',
                    backgroundColor: 'rgba(8,145,178,0.12)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    borderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11, weight: '700' }, usePointStyle: true } },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { display: false }, grid: { display: false } },
            }
        }
    });

    // Traffic chart 14 hari
    const traffic = @json($stats['recent_traffic']);
    new Chart(trafficCtx, {
        type: 'bar',
        data: {
            labels: traffic.map(t => t.label),
            datasets: [{
                label: 'Submissions',
                data: traffic.map(t => t.count),
                backgroundColor: traffic.map((_, i) => i === traffic.length - 1 ? '#4f46e5' : 'rgba(99,102,241,0.45)'),
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } },
            }
        }
    });

    // Jenis Distribution doughnut
    const jenisCtx = document.getElementById('chartJenis');
    if (jenisCtx) {
        const jenis = @json($stats['jenis_distribution']);
        if (jenis.length > 0) {
            const palette = ['#4f46e5','#0891b2','#16a34a','#d97706','#dc2626','#7c3aed','#ec4899','#10b981'];
            new Chart(jenisCtx, {
                type: 'doughnut',
                data: {
                    labels: jenis.map(j => j.label),
                    datasets: [{
                        data: jenis.map(j => j.count),
                        backgroundColor: jenis.map((_, i) => palette[i % palette.length]),
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'right', labels: { font: { size: 10 }, padding: 8, usePointStyle: true } },
                        tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed.toLocaleString('id-ID') } }
                    }
                }
            });
        }
    }

    // Hourly traffic (24h) area chart
    const hourlyCtx = document.getElementById('chartHourly');
    if (hourlyCtx) {
        const hourly = @json($stats['recent_traffic_hourly']);
        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: hourly.map(h => h.label),
                datasets: [{
                    label: 'Submissions',
                    data: hourly.map(h => h.count),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124,58,237,0.15)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2,
                    pointBackgroundColor: '#7c3aed',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, font: { size: 9 } } },
                    x: { ticks: { font: { size: 9 }, maxRotation: 0 } }
                }
            }
        });
    }

    // Poll live metrics
    function pollMetrics() {
        fetch("{{ route('superadmin.server-stats.metrics') }}")
            .then(r => r.json())
            .then(j => {
                cpuData.shift(); cpuData.push(j.cpu_pct);
                memData.shift(); memData.push(j.mem_pct);
                labels.shift(); labels.push(j.ts);
                chartLive.data.labels = labels;
                chartLive.update('none');

                document.getElementById('kpiCpu').textContent = j.cpu_pct + '%';
                document.getElementById('kpiMem').textContent = j.mem_pct + '%';
                document.getElementById('kpiCpuBar').style.width = j.cpu_pct + '%';
                document.getElementById('kpiMemBar').style.width = j.mem_pct + '%';
                document.getElementById('kpiMemSub').textContent = j.mem_usage + ' used';

                document.getElementById('ssLastUpdate').textContent = 'Last update: ' + j.ts + ' WIB';
            })
            .catch(() => {});
    }
    setInterval(pollMetrics, 5000);
});
</script>
@endpush
