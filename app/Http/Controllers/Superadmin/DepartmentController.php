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
        return view('departments.index', compact('departments'));
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
        $department->delete();

        return redirect()->route('superadmin.departments.index')
            ->with('success', 'Departemen berhasil dihapus');
    }
}
