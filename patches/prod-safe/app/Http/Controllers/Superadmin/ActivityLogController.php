<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'arsip.department', 'arsip.unit']);

        if ($request->filled('q')) {
            $query->whereHas('arsip', function($q) use ($request) {
                $q->where('no_registrasi', 'like', '%' . $request->q . '%')
                  ->orWhere('no_transaksi', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $perPageRaw = $request->input('per_page', 20);
        $perPage = ($perPageRaw === 'all') ? 99999 : max(1, (int) $perPageRaw);
        $logs = $query->latest()->paginate($perPage)->withQueryString();
        
        // Ambil user yang pernah melakukan perubahan saja untuk filter
        $users = User::whereIn('id', AuditLog::distinct()->pluck('user_id'))->get();

        return view('superadmin.activity_logs.index', compact('logs', 'users'));
    }

    /**
     * Hapus satu baris log by id.
     */
    public function destroy($id)
    {
        $log = AuditLog::findOrFail($id);
        $log->delete();

        return back()->with('success', 'Log dihapus.');
    }

    /**
     * Bulk delete — dua mode:
     *  1) ids=[...]        → hapus semua ID yang di-checkbox
     *  2) older_than_days  → hapus semua log > N hari yang lalu (retention)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'              => 'nullable|array',
            'ids.*'            => 'integer|exists:audit_logs,id',
            'older_than_days'  => 'nullable|integer|min:1|max:3650',
        ]);

        $deleted = 0;

        if ($request->filled('ids')) {
            $deleted = AuditLog::whereIn('id', $request->ids)->delete();
        } elseif ($request->filled('older_than_days')) {
            $cutoff = now()->subDays((int) $request->older_than_days);
            $deleted = AuditLog::where('created_at', '<', $cutoff)->delete();
        } else {
            return back()->with('error', 'Pilih baris atau isi jumlah hari retention.');
        }

        return back()->with('success', "Berhasil menghapus {$deleted} log.");
    }
}
