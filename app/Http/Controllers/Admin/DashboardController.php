<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Arsip;
use App\Models\Department;
use App\Models\Manager;
use App\Models\Unit;

class DashboardController extends Controller
{
    public function index()
    {
        $adminId = auth()->id();
        $notifications = auth()->user()
        ->notifications()
        ->latest()
        ->limit(5)
        ->get();


        return view('admin.dashboard.index', [
            // statistik
            'total'   => Arsip::where('admin_id',$adminId)->count(),
            'Review'  => Arsip::where('admin_id',$adminId)->where('ket_process','Review')->count(),
            'process' => Arsip::where('admin_id',$adminId)->where('ket_process','Process')->count(),
            'done'    => Arsip::where('admin_id',$adminId)->where('ket_process','Done')->count(),

            // data pendukung
            'departments' => Department::orderBy('name')->get(),
            'managers'    => Manager::orderBy('name')->get(),
            'units'       => Unit::orderBy('name')->get(),

            // histori
            'arsips' => Arsip::with(['department','manager','unit', 'adjustItems', 'mutasiItems', 'bundelItems'])
                ->where('admin_id',$adminId)
                ->latest()
                ->paginate(10),
        ]);
    }
}
