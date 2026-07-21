<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        // Location dipakai di adjust_items dan mutasi_items (kolom 'location' string, not FK)
        $adjustUsage = \DB::table('arsip_adjust_items')
            ->select('location', \DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('location')
            ->groupBy('location')
            ->pluck('cnt', 'location');

        $mutasiUsage = \DB::table('arsip_mutasi_items')
            ->select('location', \DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('location')
            ->groupBy('location')
            ->pluck('cnt', 'location');

        $q = trim((string) $request->get('q', ''));
        $perPageRaw = $request->input('per_page', 15);
        $perPage = ($perPageRaw === 'all') ? 99999 : max(1, (int) $perPageRaw);
        $locations = Location::query()
            ->when($q !== '', fn ($w) => $w->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $locations->getCollection()->each(function ($l) use ($adjustUsage, $mutasiUsage) {
            $l->adjust_count = (int) ($adjustUsage[$l->name] ?? 0);
            $l->mutasi_count = (int) ($mutasiUsage[$l->name] ?? 0);
            $l->total_usage = $l->adjust_count + $l->mutasi_count;
        });

        $totalLoc = Location::count();
        $totalActive = Location::where('is_active', true)->count();
        // Total usage dihitung dari array sum (full dataset, bukan paginated)
        $totalUsage = (int) array_sum($adjustUsage->toArray()) + (int) array_sum($mutasiUsage->toArray());
        $latestLoc = Location::latest()->first()->name ?? '-';

        return view('locations.index', compact('locations', 'totalLoc', 'totalActive', 'totalUsage', 'latestLoc'));
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
