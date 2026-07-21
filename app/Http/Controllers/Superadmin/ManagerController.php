<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller; // ✅ WAJIB
use App\Models\Manager;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function index(Request $request)
    {
        $arsipCounts = \DB::table('arsips')
            ->select('manager_id', \DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('manager_id')
            ->groupBy('manager_id')
            ->pluck('cnt', 'manager_id');

        $lastActivities = \DB::table('arsips')
            ->select('manager_id', \DB::raw('MAX(updated_at) as last_at'))
            ->whereNotNull('manager_id')
            ->groupBy('manager_id')
            ->pluck('last_at', 'manager_id');

        $q = trim((string) $request->get('q', ''));
        $perPageRaw = $request->input('per_page', 15);
        $perPage = ($perPageRaw === 'all') ? 99999 : max(1, (int) $perPageRaw);
        $managers = Manager::query()
            ->when($q !== '', fn ($w) => $w->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $managers->getCollection()->each(function ($m) use ($arsipCounts, $lastActivities) {
            $m->arsips_count = (int) ($arsipCounts[$m->id] ?? 0);
            $m->last_activity = $lastActivities[$m->id] ?? null;
        });

        $totalManager = Manager::count();
        $totalActive = Manager::where('is_active', true)->count();
        $totalArsipLinked = array_sum($arsipCounts->toArray());
        $latestManager = Manager::latest()->first()->name ?? '-';

        return view('managers.index', compact('managers', 'totalManager', 'totalActive', 'totalArsipLinked', 'latestManager'));
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

    public function toggleIsActive(Manager $manager)
    {
        $manager->update(['is_active' => !$manager->is_active]);
        $status = $manager->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Manager \"{$manager->name}\" berhasil {$status}.");
    }
}
