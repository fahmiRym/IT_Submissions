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
        $branches = Branch::withCount('departments')->orderBy('name')->get();
        return view('superadmin.branches.index', compact('branches'));
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
}
