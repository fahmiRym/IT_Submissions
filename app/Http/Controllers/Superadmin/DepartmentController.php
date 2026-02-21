<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller; // ✅ WAJIB
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->get();
        $totalDept = Department::count();
        $totalUser = \App\Models\User::count();
        $latestDept = Department::latest()->first()->name ?? '-';

        return view('departments.index', compact('departments', 'totalDept', 'totalUser', 'latestDept'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:departments,name'
        ]);

        Department::create($request->only('name'));

        return redirect()->route('superadmin.departments.index')
            ->with('success', 'Departemen berhasil ditambahkan');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|unique:departments,name,' . $department->id
        ]);

        $department->update($request->only('name'));

        return redirect()->route('superadmin.departments.index')
            ->with('success', 'Departemen berhasil diupdate');
    }

    public function destroy(Department $department)
    {
        // Hitung pengajuan yang terkait (untuk info kepada user)
        $jumlahArsip = \App\Models\Arsip::where('department_id', $department->id)->count();

        $nama = $department->name;
        $department->delete();
        // Catatan: department_id pada tabel arsips akan otomatis di-set NULL
        // (bukan dihapus) karena foreign key sudah menggunakan nullOnDelete().

        $pesan = "Departemen \"{$nama}\" berhasil dihapus.";
        if ($jumlahArsip > 0) {
            $pesan .= " {$jumlahArsip} pengajuan terkait tetap tersimpan (departemen di-set kosong).";
        }

        return redirect()->route('superadmin.departments.index')
            ->with('success', $pesan);
    }

    public function toggleIsActive(Department $department)
    {
        $department->update(['is_active' => !$department->is_active]);
        
        $status = $department->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Departemen \"{$department->name}\" berhasil {$status}.");
    }
}
