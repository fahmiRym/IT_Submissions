<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller; // ✅ WAJIB
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $userCounts = \DB::table('users')
            ->select('department_id', \DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->pluck('cnt', 'department_id');

        $arsipCounts = \DB::table('arsips')
            ->select('department_id', \DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->pluck('cnt', 'department_id');

        $lastActivities = \DB::table('arsips')
            ->select('department_id', \DB::raw('MAX(updated_at) as last_at'))
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->pluck('last_at', 'department_id');

        $q = trim((string) $request->get('q', ''));
        $branchFilter = $request->get('branch_id');
        $perPageRaw = $request->input('per_page', 15);
        $perPage = ($perPageRaw === 'all') ? 99999 : max(1, (int) $perPageRaw);
        $departments = Department::query()
            ->with('branch:id,name,code,kota')
            ->when($q !== '', fn ($w) => $w->where('name', 'like', "%{$q}%"))
            ->when($branchFilter === 'none', fn ($w) => $w->whereNull('branch_id'))
            ->when($branchFilter && $branchFilter !== 'none', fn ($w) => $w->where('branch_id', $branchFilter))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $branches = \App\Models\Branch::active()->orderBy('name')->get(['id', 'name', 'kota']);

        $departments->getCollection()->each(function ($d) use ($userCounts, $arsipCounts, $lastActivities) {
            $d->users_count = (int) ($userCounts[$d->id] ?? 0);
            $d->arsips_count = (int) ($arsipCounts[$d->id] ?? 0);
            $d->last_activity = $lastActivities[$d->id] ?? null;
        });

        $totalDept = Department::count();
        $totalUser = \App\Models\User::count();
        $totalArsipLinked = array_sum($arsipCounts->toArray());
        $latestDept = Department::latest()->first()->name ?? '-';

        return view('departments.index', compact('departments', 'branches', 'totalDept', 'totalUser', 'totalArsipLinked', 'latestDept'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::active()->orderBy('name')->get(['id', 'name', 'kota']);
        return view('departments.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|unique:departments,name',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        Department::create($request->only('name', 'branch_id'));

        return redirect()->route('superadmin.departments.index')
            ->with('success', 'Departemen berhasil ditambahkan');
    }

    public function edit(Department $department)
    {
        $branches = \App\Models\Branch::active()->orderBy('name')->get(['id', 'name', 'kota']);
        return view('departments.edit', compact('department', 'branches'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'      => 'required|unique:departments,name,' . $department->id,
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $department->update($request->only('name', 'branch_id'));

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
