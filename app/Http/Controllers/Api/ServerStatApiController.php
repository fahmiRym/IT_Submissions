<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Superadmin\ServerStatController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API endpoint untuk Statistik Server (mobile Android — superadmin only).
 *
 * Extend ServerStatController supaya bisa reuse SEMUA protected helper
 * (formatBytes, getCpuCount, getTableBreakdown, dst) tanpa duplikasi.
 *
 * Endpoint:
 *   GET /api/superadmin/server-stats           → full snapshot (semua field)
 *   GET /api/superadmin/server-stats/metrics   → live-tick 5 field (auto-refresh chart)
 *
 * Guard: superadmin only → 403 selain itu.
 */
class ServerStatApiController extends ServerStatController
{
    /**
     * Full snapshot dashboard — Android render seluruh KPI + chart dari sini.
     */
    public function apiSnapshot(Request $request): JsonResponse
    {
        if (!$this->isSuperadmin()) {
            return response()->json(['success' => false, 'message' => 'Superadmin only'], 403);
        }

        $diskFree  = @disk_free_space(base_path()) ?: 0;
        $diskTotal = @disk_total_space(base_path()) ?: 1;
        $diskUsed  = $diskTotal - $diskFree;
        $diskPct   = round(($diskUsed / max($diskTotal, 1)) * 100, 2);

        $memUsage = memory_get_usage(true);
        $memPeak  = memory_get_peak_usage(true);
        $memLimit = $this->iniBytes(ini_get('memory_limit'));

        $loadAvg  = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        $cpuCount = $this->getCpuCount();

        $stats = [
            // ── Server identity ──
            'php_version'      => PHP_VERSION,
            'laravel_version'  => app()->version(),
            'os'               => PHP_OS . ' (' . php_uname('r') . ')',
            'server_software'  => $_SERVER['SERVER_SOFTWARE'] ?? 'Built-in',
            'server_name'      => gethostname() ?: 'localhost',
            'timezone'         => config('app.timezone'),
            'app_env'          => config('app.env'),
            'app_debug'        => (bool) config('app.debug'),
            'app_url'          => config('app.url'),

            // ── Disk ──
            'disk' => [
                'free'          => $this->formatBytes($diskFree),
                'used'          => $this->formatBytes($diskUsed),
                'total'         => $this->formatBytes($diskTotal),
                'used_percent'  => $diskPct,
                'free_percent'  => round(100 - $diskPct, 2),
            ],

            // ── Memory ──
            'memory' => [
                'usage'        => $this->formatBytes($memUsage),
                'peak'         => $this->formatBytes($memPeak),
                'limit'        => $memLimit > 0 ? $this->formatBytes($memLimit) : (ini_get('memory_limit') ?: 'unlimited'),
                'used_percent' => $memLimit > 0 ? round(($memUsage / $memLimit) * 100, 2) : 0,
            ],

            // ── CPU / Load ──
            'cpu' => [
                'count'       => $cpuCount,
                'load_1m'     => $loadAvg[0] ?? 0,
                'load_5m'     => $loadAvg[1] ?? 0,
                'load_15m'    => $loadAvg[2] ?? 0,
                'load_pct_1m' => $cpuCount > 0 ? min(100, round(($loadAvg[0] / $cpuCount) * 100, 1)) : 0,
            ],

            'uptime' => $this->getUptime(),

            // ── Database ──
            'database' => [
                'size_mb' => $this->getDatabaseSizeMB(),
                'engine'  => 'MySQL/MariaDB',
                'name'    => config('database.connections.mysql.database'),
                'host'    => config('database.connections.mysql.host'),
            ],

            // ── Aggregations & breakdowns ──
            'table_breakdown'       => $this->getTableBreakdown(),
            'top_users'             => $this->getTopUsers(),
            'recent_traffic'        => $this->getRecentTraffic(),         // 14 hari
            'recent_traffic_hourly' => $this->getRecentTrafficHourly(),   // 24 jam
            'storage_breakdown'     => $this->getStorageBreakdown(),
            'queue_health'          => $this->getQueueHealth(),
            'php_extensions'        => $this->getKeyExtensions(),

            // ── Innovation metrics ──
            'response_time_ms'    => $this->measureSelfResponseTime(),
            'db_response_ms'      => $this->measureDbResponseTime(),
            'active_users_24h'    => $this->countActiveUsers24h(),
            'jenis_distribution'  => $this->getJenisDistribution(),
            'pending_approvals'   => $this->getPendingApprovals(),
            'cache_driver'        => config('cache.default'),
            'session_driver'      => config('session.driver'),
            'mail_driver'         => config('mail.default'),
            'recent_error_count'  => $this->countRecentErrors(),
        ];

        $stats['alerts'] = $this->buildAlerts(
            $stats['disk']['used_percent'],
            $stats['memory']['used_percent'],
            $stats['cpu']['load_pct_1m'],
        );

        // Health score 0-100 (butuh flat shape utk reuse helper)
        $stats['health_score'] = $this->calculateHealthScore([
            'disk_used_percent'   => $stats['disk']['used_percent'],
            'mem_used_percent'    => $stats['memory']['used_percent'],
            'load_pct_1m'         => $stats['cpu']['load_pct_1m'],
            'queue_health'        => $stats['queue_health'],
            'recent_error_count'  => $stats['recent_error_count'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Server stats OK',
            'data'    => $stats,
        ]);
    }

    /**
     * Live-tick metrics untuk auto-refresh chart (poll 5-10 detik).
     * Payload ringan — cuma 5 field yg berubah cepat.
     */
    public function apiMetrics(Request $request): JsonResponse
    {
        if (!$this->isSuperadmin()) {
            return response()->json(['success' => false, 'message' => 'Superadmin only'], 403);
        }

        $diskFree  = @disk_free_space(base_path()) ?: 0;
        $diskTotal = @disk_total_space(base_path()) ?: 1;
        $memUsage  = memory_get_usage(true);
        $memLimit  = $this->iniBytes(ini_get('memory_limit'));
        $loadAvg   = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        $cpuCount  = $this->getCpuCount();

        return response()->json([
            'success'   => true,
            'ts'        => now()->format('H:i:s'),
            'disk_pct'  => round((1 - $diskFree / max($diskTotal, 1)) * 100, 2),
            'mem_pct'   => $memLimit > 0 ? round(($memUsage / $memLimit) * 100, 2) : 0,
            'cpu_pct'   => $cpuCount > 0 ? min(100, round(($loadAvg[0] / $cpuCount) * 100, 1)) : 0,
            'load_1m'   => round($loadAvg[0], 2),
        ]);
    }

    private function isSuperadmin(): bool
    {
        return auth()->check() && auth()->user()->role === 'superadmin';
    }
}
