<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller; // ✅ WAJIB
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('name')->get();
        $totalUnit = Unit::count();
        $latestUnit = Unit::latest()->first()->name ?? '-';

        return view('units.index', compact('units', 'totalUnit', 'latestUnit'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:units,name'
        ]);

        Unit::create($request->only('name'));

        return redirect()->route('superadmin.units.index')
            ->with('success', 'Unit berhasil ditambahkan');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|unique:units,name,' . $unit->id
        ]);

        $unit->update($request->only('name'));

        return redirect()->route('superadmin.units.index')
            ->with('success', 'Unit berhasil diupdate');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('superadmin.units.index')
            ->with('success', 'Unit berhasil dihapus');
    }

    public function toggleIsActive(Unit $unit)
    {
        $unit->update(['is_active' => !$unit->is_active]);
        $status = $unit->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Unit \"{$unit->name}\" berhasil {$status}.");
    }
}
