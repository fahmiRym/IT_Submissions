<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller; // ✅ WAJIB
use App\Models\Manager;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function index()
    {
        $managers = Manager::orderBy('name')->get();
        $totalManager = Manager::count();
        $latestManager = Manager::latest()->first()->name ?? '-';

        return view('managers.index', compact('managers', 'totalManager', 'latestManager'));
    }

    public function create()
    {
        return view('managers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:managers,name'
        ]);

        Manager::create($request->only('name'));

        return redirect()->route('superadmin.managers.index')
            ->with('success', 'Manager berhasil ditambahkan');
    }

    public function edit(Manager $manager)
    {
        return view('managers.edit', compact('manager'));
    }

    public function update(Request $request, Manager $manager)
    {
        $request->validate([
            'name' => 'required|unique:managers,name,' . $manager->id
        ]);

        $manager->update($request->only('name'));

        return redirect()->route('superadmin.managers.index')
            ->with('success', 'Manager berhasil diupdate');
    }

    public function destroy(Manager $manager)
    {
        $manager->delete();

        return redirect()->route('superadmin.managers.index')
            ->with('success', 'Manager berhasil dihapus');
    }
}
