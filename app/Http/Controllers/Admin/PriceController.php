<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Master Harga per kode barang.
 * Akses dijaga route middleware + Gate 'view-price'.
 */
class PriceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('view-price'), 403);

        $q = ItemPrice::query();
        if ($s = trim($request->get('q', ''))) {
            $q->where(function ($w) use ($s) {
                $w->where('kode_barang', 'like', "%{$s}%")
                  ->orWhere('nama_barang', 'like', "%{$s}%");
            });
        }
        $prices = $q->orderBy('kode_barang')->paginate(25)->withQueryString();

        // Derive list kode_barang yg pernah dipakai di item tables tapi belum punya master harga
        $usedKodes = collect()
            ->merge(DB::table('arsip_adjust_items')->whereNotNull('product_code')->distinct()->pluck('product_code'))
            ->merge(DB::table('arsip_mutasi_items')->whereNotNull('product_code')->distinct()->pluck('product_code'))
            ->unique()
            ->values();

        $existingKodes = ItemPrice::pluck('kode_barang');
        $missingCount = $usedKodes->diff($existingKodes)->count();

        $stats = [
            'total_master' => ItemPrice::count(),
            'used_kodes' => $usedKodes->count(),
            'missing'    => $missingCount,
            'total_value'=> (float) ItemPrice::sum('harga'),
        ];

        return view('admin.prices.index', compact('prices', 'stats'));
    }

    public function store(Request $request)
    {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('view-price'), 403);

        $data = $request->validate([
            'kode_barang' => 'required|string|max:64|unique:item_prices,kode_barang',
            'nama_barang' => 'nullable|string|max:191',
            'harga'       => 'required|numeric|min:0',
            'satuan'      => 'nullable|string|max:32',
            'keterangan'  => 'nullable|string|max:500',
        ]);

        ItemPrice::create($data + [
            'currency'   => 'IDR',
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', "Harga untuk {$data['kode_barang']} berhasil ditambahkan.");
    }

    public function update(Request $request, ItemPrice $price)
    {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('view-price'), 403);

        $data = $request->validate([
            'nama_barang' => 'nullable|string|max:191',
            'harga'       => 'required|numeric|min:0',
            'satuan'      => 'nullable|string|max:32',
            'keterangan'  => 'nullable|string|max:500',
        ]);

        $price->fill($data + ['updated_by' => auth()->id()])->save();

        return back()->with('success', "Harga {$price->kode_barang} diupdate jadi Rp ".number_format($price->harga, 0, ',', '.'));
    }

    public function destroy(ItemPrice $price)
    {
        abort_unless(\Illuminate\Support\Facades\Gate::allows('view-price'), 403);

        $kode = $price->kode_barang;
        $price->delete();
        return back()->with('success', "Harga {$kode} dihapus.");
    }
}
