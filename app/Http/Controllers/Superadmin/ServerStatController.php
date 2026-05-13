<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServerStatController extends Controller
{
    public function index()
    {
        $stats = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'os' => PHP_OS,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'disk_free' => $this->formatBytes(disk_free_space(base_path())),
            'disk_total' => $this->formatBytes(disk_total_space(base_path())),
            'disk_used_percent' => round((1 - (disk_free_space(base_path()) / disk_total_space(base_path()))) * 100, 2),
            'database_size' => $this->getDatabaseSize(),
        ];

        return view('superadmin.server_stats.index', compact('stats'));
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function getDatabaseSize()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $results = \Illuminate\Support\Facades\DB::select("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
            return round($results[0]->size, 2) . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
}
