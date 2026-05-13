<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Arsip;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Arsip::whereNotNull('updated_by')
                      ->with(['editor', 'admin', 'department', 'unit']);

        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('no_registrasi', 'like', '%' . $request->q . '%')
                  ->orWhere('no_transaksi', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('user_id')) {
            $query->where('updated_by', $request->user_id);
        }

        $logs = $query->latest('updated_at')->paginate(20);
        $users = \App\Models\User::whereIn('id', Arsip::whereNotNull('updated_by')->distinct()->pluck('updated_by'))->get();

        return view('superadmin.activity_logs.index', compact('logs', 'users'));
    }
}
