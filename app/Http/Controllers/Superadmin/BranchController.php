<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('departments')
            ->with(['departments' => function ($q) {
                $q->select('id', 'name', 'is_active', 'branch_id')
                  ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        // Dept tanpa branch
        $orphanDepts = \App\Models\Department::whereNull('branch_id')
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);

        // Semua dept dgn info branch saat ini — untuk modal Kelola Departemen
        $allDepts = \App\Models\Department::with('branch:id,name,code')
            ->orderBy('name')
            ->get(['id', 'name', 'is_active', 'branch_id']);

        return view('superadmin.branches.index', compact('branches', 'orphanDepts', 'allDepts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:20|regex:/^[A-Z0-9_-]+$/|unique:branches,code',
            'kota'      => 'required|string|max:60',
            'alamat'    => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ], [
            'code.regex' => 'Kode branch harus huruf besar/angka/dash/underscore saja.',
        ]);

        $data['code']      = strtoupper($data['code']);
        $data['kota']      = strtoupper($data['kota']);
        $data['is_active'] = $request->boolean('is_active', true);

        Branch::create($data);
        return back()->with('success', "Cabang '{$data['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:20|regex:/^[A-Z0-9_-]+$/|unique:branches,code,' . $id,
            'kota'      => 'required|string|max:60',
            'alamat'    => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $data['code']      = strtoupper($data['code']);
        $data['kota']      = strtoupper($data['kota']);
        $data['is_active'] = $request->boolean('is_active', true);

        $branch->update($data);
        return back()->with('success', "Cabang '{$branch->name}' berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $branch = Branch::withCount('departments')->findOrFail($id);

        if ($branch->departments_count > 0) {
            return back()->with('error',
                "Tidak bisa hapus '{$branch->name}' — masih dipakai oleh {$branch->departments_count} departemen. Pindahkan dulu.");
        }

        $branch->delete();
        return back()->with('success', "Cabang '{$branch->name}' dihapus.");
    }

    /**
     * Bulk assign dept ke branch — dipakai modal 'Kelola Departemen'.
     * Semantic:
     *   - Dept di dept_ids[] → branch_id = $id (dipindah kalau lagi di branch lain)
     *   - Dept TIDAK di dept_ids[] TAPI branch_id-nya = $id sekarang → set NULL (orphan)
     *   - Dept lain: tidak disentuh
     */
    public function syncDepts(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $request->validate([
            'dept_ids'   => 'nullable|array',
            'dept_ids.*' => 'integer|exists:departments,id',
        ]);

        $keepIds = collect($request->input('dept_ids', []))->map(fn ($v) => (int) $v)->all();

        DB::transaction(function () use ($branch, $keepIds, &$assigned, &$removed) {
            // 1) Dept yg dicentang → assign ke branch ini (from anywhere)
            $assigned = 0;
            if (!empty($keepIds)) {
                $assigned = DB::table('departments')
                    ->whereIn('id', $keepIds)
                    ->where(function ($q) use ($branch) {
                        $q->whereNull('branch_id')->orWhere('branch_id', '!=', $branch->id);
                    })
                    ->update(['branch_id' => $branch->id, 'updated_at' => now()]);
            }

            // 2) Dept yg sekarang di branch ini TAPI tidak dicentang → orphan
            $removed = DB::table('departments')
                ->where('branch_id', $branch->id)
                ->when(!empty($keepIds), fn ($q) => $q->whereNotIn('id', $keepIds))
                ->update(['branch_id' => null, 'updated_at' => now()]);
        });

        return back()->with('success',
            "Cabang '{$branch->name}' updated: {$assigned} dept ditambahkan, {$removed} dept dilepas ke orphan.");
    }
}
