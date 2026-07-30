<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServerStatController extends Controller
{
    public function index()
    {
        $diskFree  = @disk_free_space(base_path()) ?: 0;
        $diskTotal = @disk_total_space(base_path()) ?: 1;
        $diskUsed  = $diskTotal - $diskFree;
        $diskPct   = round(($diskUsed / max($diskTotal, 1)) * 100, 2);

        // System memory (real RAM), fallback ke PHP memory kalau /proc/meminfo tidak ada (Windows)
        $sysMem = $this->getSystemMemory();
        $memUsage = memory_get_usage(true);
        $memPeak  = memory_get_peak_usage(true);
        $memLimit = $this->iniBytes(ini_get('memory_limit'));

        $loadAvg = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];

        $cpuCount = $this->getCpuCount();
        // Real CPU % dari /proc/stat (Linux only). Beda dgn load average — ini current utilization instant.
        $cpuUsage = $this->getRealCpuUsage();

        $stats = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'os' => PHP_OS . ' (' . php_uname('r') . ')',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Built-in (artisan serve)',
            'server_name' => gethostname() ?: 'localhost',
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? request()->server('SERVER_ADDR') ?? '-',
            'timezone' => config('app.timezone'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'ON' : 'OFF',
            'app_url' => config('app.url'),

            'disk_free' => $this->formatBytes($diskFree),
            'disk_used' => $this->formatBytes($diskUsed),
            'disk_total' => $this->formatBytes($diskTotal),
            'disk_used_percent' => $diskPct,
            'disk_free_percent' => round(100 - $diskPct, 2),

            // PHP process memory (buat debug PHP-level saja)
            'mem_usage' => $this->formatBytes($memUsage),
            'mem_peak' => $this->formatBytes($memPeak),
            'mem_limit' => $memLimit > 0 ? $this->formatBytes($memLimit) : (ini_get('memory_limit') ?: 'unlimited'),
            'mem_used_percent' => $memLimit > 0 ? round(($memUsage / $memLimit) * 100, 2) : 0,

            // SYSTEM memory (real RAM — ini yang bermakna untuk operator)
            'sys_mem_total' => $this->formatBytes($sysMem['total']),
            'sys_mem_used'  => $this->formatBytes($sysMem['used']),
            'sys_mem_free'  => $this->formatBytes($sysMem['available']),
            'sys_mem_pct'   => $sysMem['pct_used'],

            'load_1m' => $loadAvg[0] ?? 0,
            'load_5m' => $loadAvg[1] ?? 0,
            'load_15m' => $loadAvg[2] ?? 0,
            'cpu_count' => $cpuCount,
            'load_pct_1m' => $cpuCount > 0 ? min(100, round(($loadAvg[0] / $cpuCount) * 100, 1)) : 0,
            // Real CPU utilization (instant, dari /proc/stat) — bandingan yg lebih akurat drpd load avg
            'cpu_usage_pct' => $cpuUsage['usage_pct'],
            'cpu_iowait_pct' => $cpuUsage['iowait_pct'],
            'cpu_idle_pct'  => $cpuUsage['idle_pct'],

            'uptime' => $this->getUptime(),

            'database_size' => $this->getDatabaseSize(),
            'database_size_mb' => $this->getDatabaseSizeMB(),
            'database_engine' => 'MySQL/MariaDB',
            'database_name' => config('database.connections.mysql.database'),
            'database_host' => config('database.connections.mysql.host'),

            'table_breakdown' => $this->getTableBreakdown(),
            'top_users' => $this->getTopUsers(),
            'recent_traffic' => $this->getRecentTraffic(),
            'recent_traffic_hourly' => $this->getRecentTrafficHourly(),
            'storage_breakdown' => $this->getStorageBreakdown(),
            'queue_health' => $this->getQueueHealth(),
            'php_extensions' => $this->getKeyExtensions(),

            // ── INOVASI BARU ──
            'response_time_ms' => $this->measureSelfResponseTime(),
            'db_response_ms' => $this->measureDbResponseTime(),
            'active_users_24h' => $this->countActiveUsers24h(),
            'jenis_distribution' => $this->getJenisDistribution(),
            'pending_approvals' => $this->getPendingApprovals(),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'mail_driver' => config('mail.default'),
            'app_logs_size' => $this->formatBytes($this->dirSize(storage_path('logs'))),
            'recent_error_count' => $this->countRecentErrors(),
            'alerts' => $this->buildAlerts($diskPct, $sysMem['pct_used'], $cpuUsage['usage_pct'], $cpuUsage['iowait_pct']),
        ];

        // Health score 0-100
        $stats['health_score'] = $this->calculateHealthScore($stats);

        return view('superadmin.server_stats.index', compact('stats'));
    }

    /* ============================================================
       LIVE METRICS ENDPOINT (untuk auto-refresh chart)
       ============================================================ */
    public function metrics()
    {
        $diskFree  = @disk_free_space(base_path()) ?: 0;
        $diskTotal = @disk_total_space(base_path()) ?: 1;
        $memUsage  = memory_get_usage(true);
        $memLimit  = $this->iniBytes(ini_get('memory_limit'));
        $loadAvg   = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        $cpuCount  = $this->getCpuCount();
        $sysMem    = $this->getSystemMemory();
        $cpuUsage  = $this->getRealCpuUsage();

        return response()->json([
            'ts' => now()->format('H:i:s'),
            'disk_pct'    => round((1 - $diskFree / max($diskTotal, 1)) * 100, 2),
            'mem_pct'     => $sysMem['pct_used'], // system RAM, bukan PHP process
            'cpu_pct'     => $cpuUsage['usage_pct'], // real utilization, bukan load avg ratio
            'cpu_iowait'  => $cpuUsage['iowait_pct'],
            'load_1m'     => round($loadAvg[0], 2),
            'load_ratio'  => $cpuCount > 0 ? round($loadAvg[0] / $cpuCount, 2) : 0, // 1.0 = fully utilized
        ]);
    }

    /* Read system RAM dari /proc/meminfo (Linux) */
    protected function getSystemMemory(): array
    {
        if (!file_exists('/proc/meminfo') || !is_readable('/proc/meminfo')) {
            // Fallback: PHP process memory (Windows)
            $mu = memory_get_usage(true);
            $ml = $this->iniBytes(ini_get('memory_limit'));
            return [
                'total'     => $ml ?: $mu,
                'used'      => $mu,
                'available' => max(0, $ml - $mu),
                'pct_used'  => $ml > 0 ? round(($mu / $ml) * 100, 1) : 0,
            ];
        }

        $lines = @file('/proc/meminfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $parse = fn ($k) => (int) (preg_match('/^' . preg_quote($k) . ':\s+(\d+)/i',
            implode("\n", array_filter($lines, fn ($l) => str_starts_with($l, $k))), $m) ? $m[1] : 0);

        $totalKb     = $parse('MemTotal');
        $availableKb = $parse('MemAvailable') ?: $parse('MemFree');
        $usedKb      = max(0, $totalKb - $availableKb);
        $pct         = $totalKb > 0 ? round(($usedKb / $totalKb) * 100, 1) : 0;

        return [
            'total'     => $totalKb * 1024,
            'used'      => $usedKb * 1024,
            'available' => $availableKb * 1024,
            'pct_used'  => $pct,
        ];
    }

    /* Read real CPU utilization dari /proc/stat (Linux). Sample 2x dgn jeda 200ms. */
    protected function getRealCpuUsage(): array
    {
        if (!file_exists('/proc/stat') || !is_readable('/proc/stat')) {
            return ['usage_pct' => 0, 'iowait_pct' => 0, 'idle_pct' => 100];
        }
        $s1 = $this->readCpuLine();
        usleep(200000); // 200ms
        $s2 = $this->readCpuLine();
        if (!$s1 || !$s2) return ['usage_pct' => 0, 'iowait_pct' => 0, 'idle_pct' => 100];

        $totalDelta = 0;
        $delta = [];
        foreach (['user', 'nice', 'system', 'idle', 'iowait', 'irq', 'softirq', 'steal'] as $k) {
            $delta[$k] = max(0, ($s2[$k] ?? 0) - ($s1[$k] ?? 0));
            $totalDelta += $delta[$k];
        }
        if ($totalDelta === 0) return ['usage_pct' => 0, 'iowait_pct' => 0, 'idle_pct' => 100];

        $idle   = $delta['idle'] + $delta['iowait'];
        $active = $totalDelta - $idle;
        return [
            'usage_pct'  => round(($active / $totalDelta) * 100, 1),
            'iowait_pct' => round(($delta['iowait'] / $totalDelta) * 100, 1),
            'idle_pct'   => round(($delta['idle'] / $totalDelta) * 100, 1),
        ];
    }
    protected function readCpuLine(): ?array
    {
        $first = @file_get_contents('/proc/stat');
        if (!$first) return null;
        $line = strtok($first, "\n");
        if (!str_starts_with($line, 'cpu ')) return null;
        $parts = preg_split('/\s+/', trim($line));
        array_shift($parts); // remove 'cpu' label
        return [
            'user' => (int) ($parts[0] ?? 0),
            'nice' => (int) ($parts[1] ?? 0),
            'system' => (int) ($parts[2] ?? 0),
            'idle' => (int) ($parts[3] ?? 0),
            'iowait' => (int) ($parts[4] ?? 0),
            'irq' => (int) ($parts[5] ?? 0),
            'softirq' => (int) ($parts[6] ?? 0),
            'steal' => (int) ($parts[7] ?? 0),
        ];
    }

    /* ============================================================
       Helpers
       ============================================================ */

    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = max((float) $bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[(int) $pow];
    }

    protected function iniBytes($val): int
    {
        $val = trim((string) $val);
        if ($val === '' || $val === '-1') return 0;
        $unit = strtolower($val[strlen($val) - 1]);
        $num = (int) $val;
        return match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => $num,
        };
    }

    protected function getCpuCount(): int
    {
        if (function_exists('shell_exec') && PHP_OS_FAMILY === 'Linux') {
            $n = (int) @shell_exec('nproc 2>/dev/null');
            if ($n > 0) return $n;
        }
        if (PHP_OS_FAMILY === 'Windows') {
            return (int) ($_SERVER['NUMBER_OF_PROCESSORS'] ?? getenv('NUMBER_OF_PROCESSORS') ?? 1);
        }
        return 1;
    }

    protected function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Linux' && @is_readable('/proc/uptime')) {
            $u = (float) explode(' ', (string) @file_get_contents('/proc/uptime'))[0];
            if ($u <= 0) return '-';
            $d = floor($u / 86400);
            $h = floor(($u % 86400) / 3600);
            $m = floor(($u % 3600) / 60);
            return "{$d}d {$h}h {$m}m";
        }
        return '-';
    }

    protected function getDatabaseSize(): string
    {
        return $this->getDatabaseSizeMB() . ' MB';
    }

    protected function getDatabaseSizeMB(): float
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $r = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
            return (float) ($r[0]->size ?? 0);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    protected function getTableBreakdown(): array
    {
        try {
            $db = config('database.connections.mysql.database');
            $rows = DB::select("
                SELECT TABLE_NAME AS name,
                       TABLE_ROWS AS rows_count,
                       ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?
                ORDER BY (data_length + index_length) DESC
                LIMIT 10
            ", [$db]);
            return array_map(fn($r) => (array) $r, $rows);
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getTopUsers(): array
    {
        try {
            return DB::table('arsips')
                ->select('admin_id', DB::raw('COUNT(*) as total'))
                ->whereNotNull('admin_id')
                ->groupBy('admin_id')
                ->orderByDesc('total')
                ->limit(6)
                ->get()
                ->map(function ($r) {
                    $u = DB::table('users')->where('id', $r->admin_id)->first(['name']);
                    return ['name' => $u->name ?? 'User #' . $r->admin_id, 'total' => $r->total];
                })
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getRecentTraffic(): array
    {
        // Hitung jumlah submission per hari, 14 hari terakhir
        try {
            $rows = DB::table('arsips')
                ->select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as c'))
                ->where('created_at', '>=', now()->subDays(13)->startOfDay())
                ->groupBy('d')
                ->orderBy('d')
                ->get()
                ->keyBy('d');
            $out = [];
            for ($i = 13; $i >= 0; $i--) {
                $day = now()->subDays($i)->toDateString();
                $out[] = ['label' => now()->subDays($i)->format('d/m'), 'count' => (int) ($rows[$day]->c ?? 0)];
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getStorageBreakdown(): array
    {
        $dirs = [
            'app'         => storage_path('app'),
            'logs'        => storage_path('logs'),
            'framework'   => storage_path('framework'),
            'public_storage' => public_path('storage'),
        ];
        $out = [];
        foreach ($dirs as $label => $path) {
            $out[] = [
                'label' => $label,
                'size_mb' => round($this->dirSize($path) / 1024 / 1024, 2),
            ];
        }
        return $out;
    }

    protected function dirSize(string $path): int
    {
        if (!is_dir($path)) return 0;
        $size = 0;
        try {
            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iter as $file) {
                if ($file->isFile()) $size += $file->getSize();
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return $size;
    }

    protected function getQueueHealth(): array
    {
        try {
            $jobsTable = config('queue.connections.database.table', 'jobs');
            $failedTable = config('queue.failed.table', 'failed_jobs');
            $jobs = \Schema::hasTable($jobsTable) ? DB::table($jobsTable)->count() : null;
            $failed = \Schema::hasTable($failedTable) ? DB::table($failedTable)->count() : null;
            return [
                'pending' => $jobs,
                'failed' => $failed,
                'driver' => config('queue.default'),
            ];
        } catch (\Throwable $e) {
            return ['pending' => null, 'failed' => null, 'driver' => config('queue.default')];
        }
    }

    protected function getKeyExtensions(): array
    {
        $list = ['pdo_mysql', 'mbstring', 'openssl', 'gd', 'curl', 'json', 'zip', 'fileinfo', 'xml', 'bcmath'];
        $out = [];
        foreach ($list as $e) $out[$e] = extension_loaded($e);
        return $out;
    }

    /* ============================================================
       Inovasi: response time, distribution, alerts, health score
       ============================================================ */

    protected function measureSelfResponseTime(): int
    {
        // Approx: time-since-request-start (paling akurat) tanpa external curl.
        $start = defined('LARAVEL_START') ? LARAVEL_START : ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
        return (int) ((microtime(true) - $start) * 1000);
    }

    protected function measureDbResponseTime(): int
    {
        try {
            $t0 = microtime(true);
            DB::select('SELECT 1 AS ok');
            return (int) ((microtime(true) - $t0) * 1000);
        } catch (\Throwable $e) {
            return -1;
        }
    }

    protected function countActiveUsers24h(): int
    {
        try {
            // user dianggap aktif kalau ada arsip dibuat/update 24 jam terakhir
            return DB::table('arsips')
                ->where(function ($q) {
                    $q->where('updated_at', '>=', now()->subDay())
                      ->orWhere('created_at', '>=', now()->subDay());
                })
                ->whereNotNull('admin_id')
                ->distinct('admin_id')
                ->count('admin_id');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function getJenisDistribution(): array
    {
        try {
            $rows = DB::table('arsips')
                ->select('jenis_pengajuan', DB::raw('COUNT(*) as cnt'))
                ->whereNotNull('jenis_pengajuan')
                ->groupBy('jenis_pengajuan')
                ->orderByDesc('cnt')
                ->limit(8)
                ->get();
            return $rows->map(fn($r) => [
                'label' => str_replace('_', ' ', $r->jenis_pengajuan),
                'count' => (int) $r->cnt,
            ])->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getPendingApprovals(): int
    {
        try {
            return \Schema::hasTable('arsip_approvals')
                ? DB::table('arsip_approvals')->where('status', 'pending')->count()
                : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function getRecentTrafficHourly(): array
    {
        try {
            $rows = DB::table('arsips')
                ->select(DB::raw("DATE_FORMAT(created_at, '%H:00') as h"), DB::raw('COUNT(*) as c'))
                ->where('created_at', '>=', now()->subDay())
                ->groupBy('h')
                ->orderBy('h')
                ->get()
                ->keyBy('h');
            $out = [];
            for ($i = 23; $i >= 0; $i--) {
                $hour = now()->subHours($i)->format('H:00');
                $out[] = ['label' => $hour, 'count' => (int) ($rows[$hour]->c ?? 0)];
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function countRecentErrors(): int
    {
        $logFile = storage_path('logs/laravel.log');
        if (!is_file($logFile)) return 0;
        try {
            // Baca 200 KB terakhir saja agar cepat
            $size = filesize($logFile);
            $offset = max(0, $size - 200 * 1024);
            $fp = fopen($logFile, 'r');
            if (!$fp) return 0;
            fseek($fp, $offset);
            $tail = fread($fp, 200 * 1024);
            fclose($fp);
            return substr_count($tail, '.ERROR:') + substr_count($tail, '.CRITICAL:') + substr_count($tail, '.EMERGENCY:');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function buildAlerts(float $diskPct, float $memPct, float $cpuPct, float $iowaitPct = 0): array
    {
        $alerts = [];
        if ($diskPct > 90) {
            $alerts[] = ['level' => 'danger', 'icon' => 'bi-hdd-fill', 'title' => 'Disk Hampir Penuh', 'msg' => "Disk terpakai {$diskPct}%. Segera bersihkan file atau perbesar storage."];
        } elseif ($diskPct > 75) {
            $alerts[] = ['level' => 'warning', 'icon' => 'bi-hdd', 'title' => 'Disk Tinggi', 'msg' => "Disk terpakai {$diskPct}%. Pertimbangkan pembersihan."];
        }
        // Memory: pakai SYSTEM RAM (bukan PHP process). Threshold realistic untuk server 8GB.
        if ($memPct > 90) {
            $alerts[] = ['level' => 'danger', 'icon' => 'bi-memory', 'title' => 'Memory Kritis', 'msg' => "System RAM terpakai {$memPct}%. Cek proses berat / pertimbangkan tambah RAM."];
        } elseif ($memPct > 75) {
            $alerts[] = ['level' => 'warning', 'icon' => 'bi-memory', 'title' => 'Memory Tinggi', 'msg' => "System RAM terpakai {$memPct}%."];
        }
        // CPU: pakai REAL utilization (bukan load avg ratio). Load avg tinggi tanpa CPU% tinggi = bursty, bukan masalah.
        if ($cpuPct > 85) {
            $alerts[] = ['level' => 'danger', 'icon' => 'bi-cpu-fill', 'title' => 'CPU Beban Tinggi', 'msg' => "CPU utilization {$cpuPct}% (sustained). Cek proses aktif."];
        } elseif ($cpuPct > 65) {
            $alerts[] = ['level' => 'warning', 'icon' => 'bi-cpu', 'title' => 'CPU Cukup Tinggi', 'msg' => "CPU utilization {$cpuPct}%."];
        }
        // IO Wait — indikator bottleneck disk / network
        if ($iowaitPct > 30) {
            $alerts[] = ['level' => 'danger', 'icon' => 'bi-hourglass-split', 'title' => 'IO Wait Tinggi', 'msg' => "IO wait {$iowaitPct}% — proses banyak nunggu disk/network. Cek slow query / disk saturation."];
        } elseif ($iowaitPct > 15) {
            $alerts[] = ['level' => 'warning', 'icon' => 'bi-hourglass', 'title' => 'IO Wait Naik', 'msg' => "IO wait {$iowaitPct}%."];
        }
        if (config('app.debug')) {
            $alerts[] = ['level' => 'warning', 'icon' => 'bi-bug-fill', 'title' => 'APP_DEBUG=ON', 'msg' => 'Mode debug aktif. Matikan di produksi (set APP_DEBUG=false).'];
        }
        if (config('app.env') === 'production' && config('session.driver') === 'database') {
            $alerts[] = ['level' => 'info', 'icon' => 'bi-info-circle-fill', 'title' => 'Session Driver Database', 'msg' => 'Pertimbangkan ganti ke file/redis untuk hindari deadlock GC session.'];
        }
        return $alerts;
    }

    protected function calculateHealthScore(array $stats): int
    {
        $score = 100;
        if ($stats['disk_used_percent'] > 90)        $score -= 25;
        elseif ($stats['disk_used_percent'] > 75)    $score -= 10;
        // Pakai SYSTEM RAM sekarang (bukan PHP process)
        $memPct = $stats['sys_mem_pct'] ?? $stats['mem_used_percent'];
        if ($memPct > 90)                            $score -= 20;
        elseif ($memPct > 75)                        $score -= 8;
        // Pakai REAL CPU utilization (bukan load avg ratio yg bisa tinggi karena bursty)
        $cpuPct = $stats['cpu_usage_pct'] ?? $stats['load_pct_1m'];
        if ($cpuPct > 85)                            $score -= 20;
        elseif ($cpuPct > 65)                        $score -= 8;
        if (($stats['cpu_iowait_pct'] ?? 0) > 20)    $score -= 10;
        if (($stats['queue_health']['failed'] ?? 0) > 10) $score -= 10;
        if ($stats['recent_error_count'] > 50)       $score -= 15;
        elseif ($stats['recent_error_count'] > 10)   $score -= 5;
        if (config('app.debug'))                     $score -= 5;
        return max(0, min(100, $score));
    }
}
