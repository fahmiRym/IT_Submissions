<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller; // ✅ WAJIB
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $arsipCounts = \DB::table('arsips')
            ->select('unit_id', \DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('unit_id')
            ->groupBy('unit_id')
            ->pluck('cnt', 'unit_id');

        $userCounts = \DB::table('users')
            ->select('work_unit_id', \DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('work_unit_id')
            ->groupBy('work_unit_id')
            ->pluck('cnt', 'work_unit_id');

        $lastActivities = \DB::table('arsips')
            ->select('unit_id', \DB::raw('MAX(updated_at) as last_at'))
            ->whereNotNull('unit_id')
            ->groupBy('unit_id')
            ->pluck('last_at', 'unit_id');

        $q = trim((string) $request->get('q', ''));
        $perPageRaw = $request->input('per_page', 15);
        $perPage = ($perPageRaw === 'all') ? 99999 : max(1, (int) $perPageRaw);
        $units = Unit::query()
            ->when($q !== '', fn ($w) => $w->where(function ($x) use ($q) {
                $x->where('name', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%");
            }))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $units->getCollection()->each(function ($u) use ($arsipCounts, $userCounts, $lastActivities) {
            $u->arsips_count = (int) ($arsipCounts[$u->id] ?? 0);
            $u->users_count  = (int) ($userCounts[$u->id] ?? 0);
            $u->last_activity = $lastActivities[$u->id] ?? null;
        });

        $totalUnit = Unit::count();
        $totalActive = Unit::where('is_active', true)->count();
        $totalArsipLinked = array_sum($arsipCounts->toArray());
        $latestUnit = Unit::latest()->first()->name ?? '-';

        return view('units.index', compact('units', 'totalUnit', 'totalActive', 'totalArsipLinked', 'latestUnit'));
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
