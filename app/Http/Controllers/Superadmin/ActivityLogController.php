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
}
