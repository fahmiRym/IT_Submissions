<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::orderBy('name')->get();
        $totalLoc = Location::count();
        $latestLoc = Location::latest()->first()->name ?? '-';

        return view('locations.index', compact('locations', 'totalLoc', 'latestLoc'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:locations,name'
        ]);

        Location::create($request->only('name'));

        return redirect()->route('superadmin.locations.index')
            ->with('success', 'Lokasi berhasil ditambahkan');
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|unique:locations,name,' . $location->id
        ]);

        $location->update($request->only('name'));

        return redirect()->route('superadmin.locations.index')
            ->with('success', 'Lokasi berhasil diupdate');
    }

    public function destroy(Location $location)
    {
        $nama = $location->name;
        $location->delete();

        return redirect()->route('superadmin.locations.index')
            ->with('success', "Lokasi \"{$nama}\" berhasil dihapus.");
    }

    public function toggleIsActive(Location $location)
    {
        $location->update(['is_active' => !$location->is_active]);
        
        $status = $location->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Lokasi \"{$location->name}\" berhasil {$status}.");
    }
}
