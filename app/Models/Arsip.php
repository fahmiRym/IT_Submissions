<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class Arsip extends Model
{
    protected $fillable = [
        'no_registrasi',
        'jenis_pengajuan',
        'keterangan',
        'ket_eror',
        'kategori',
        'tgl_pengajuan',
        'tgl_arsip',
        'admin_id',
        'superadmin_id',
        'department_id',
        'manager_id',
        'unit_id',
        'no_doc',
        'no_transaksi',
        'ba',
        'arsip',
        'ket_process',
        'status',
        'bukti_scan',
        'total_qty_in',
        'total_qty_out',
        'detail_barang',
        'pemohon',
    ];

    protected $casts = [
        'tgl_pengajuan' => 'datetime',
        'tgl_arsip'     => 'date',
        'detail_barang' => 'array'
    ];


         /**
     * ======================================================
     * GENERATE NO REGISTRASI (FINAL)
     * ======================================================
     * Dipakai saat data DIARSIP oleh Superadmin
     */
    public static function generateNoRegistrasi($request)
    {
        $now = now();
        $date = $now->format('ymd');
        
        // 1. Ambil Data Departemen untuk Prefix (Contoh: PUR, GBB, dll)
        $deptModel = \App\Models\Department::find($request->department_id);
        $kodeDept = 'IT'; // Default jika tidak ditemukan
        if ($deptModel) {
            // Gunakan 3 huruf pertama nama departemen sebagai prefix
            $kodeDept = strtoupper(substr(str_replace(' ', '', $deptModel->name), 0, 3));
        }

        // 2. Ambil Data Unit
        $unitModel = \App\Models\Unit::find($request->unit_id);
        if ($unitModel) {
            $kodeUnit = str_replace(['Unit ', 'Unit', ' '], ['U', 'U', ''], $unitModel->name);
        } else {
            $kodeUnit = 'U';
        }

        // Gunakan Prefix Departemen untuk No Registrasi (Badge Biru)
        $prefix = "{$kodeDept}-{$date}-{$kodeUnit}-";
        
        // Cari urutan terakhir hari ini untuk kombinasi tanggal & unit & dept tersebut
        $lastArsip = self::where('no_registrasi', 'like', $prefix . '%')
                          ->orderBy('no_registrasi', 'desc')
                          ->first();

        $lastSeq = 0;
        if ($lastArsip) {
            $parts = explode('-', $lastArsip->no_registrasi);
            $lastSegment = end($parts);
            if (is_numeric($lastSegment)) {
                $lastSeq = (int) $lastSegment;
            }
        }

        return $prefix . str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
    }

    /* ===================== RELATIONS ===================== */

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function superadmin()
    {
        return $this->belongsTo(User::class, 'superadmin_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /* ===================== ITEMS ===================== */

    public function adjustItems()
    {
        return $this->hasMany(ArsipAdjustItem::class);
    }

    public function mutasiItems()
    {
        return $this->hasMany(ArsipMutasiItem::class);
    }

    public function bundelItems()
    {
        return $this->hasMany(ArsipBundelItem::class);
    }

    /* ===================== HELPERS ===================== */

    public function isAdjust()
    {
        return $this->jenis_pengajuan === 'Adjust';
    }

    public function isMutasiBillet()
    {
        return $this->jenis_pengajuan === 'Mutasi_Billet';
    }

    public function isMutasiProduk()
    {
        return $this->jenis_pengajuan === 'Mutasi_Produk';
    }

    public function isCancel()
    {
        return $this->jenis_pengajuan === 'Cancel';
    }

    public function isInternalMemo()
    {
        return $this->jenis_pengajuan === 'Internal_Memo';
    }

    public function isBundel()
    {
        return $this->jenis_pengajuan === 'Bundel';
    }

    /**
     * Compute Total In dynamically if column is 0/null but items exist
     */
    public function getTotalQtyInAttribute($value)
    {
        if ($value > 0) return $value;

        // Jika 0, coba hitung dari relasi JIKA relasi sudah di-eager-load
        // Untuk Adjust, sum qty_in
        if ($this->relationLoaded('adjustItems')) {
            $sum = $this->adjustItems->sum('qty_in');
            if ($sum > 0) return $sum;
        }
        // Untuk Mutasi, Tujuan = In
        if ($this->relationLoaded('mutasiItems')) {
            $sum = $this->mutasiItems->where('type', 'tujuan')->sum('qty');
            if ($sum > 0) return $sum;
        }

        return 0;
    }

    /**
     * Compute Total Out dynamically if column is 0/null but items exist
     */
    public function getTotalQtyOutAttribute($value)
    {
        if ($value > 0) return $value;

        // Adjust Out
        if ($this->relationLoaded('adjustItems')) {
            $sum = $this->adjustItems->sum('qty_out');
            if ($sum > 0) return $sum;
        }
        // Mutasi Asal = Out
        if ($this->relationLoaded('mutasiItems')) {
            $sum = $this->mutasiItems->where('type', 'asal')->sum('qty');
            if ($sum > 0) return $sum;
        }
        // Bundel = Out
        if ($this->relationLoaded('bundelItems')) {
            $sum = $this->bundelItems->sum('qty');
            if ($sum > 0) return $sum;
        }

        return 0;
    }

    /**
     * Formatting Accessor for no_doc
     */
    public function getNoDocRowsAttribute(): array
    {
        if (!$this->no_doc) return [];
        return collect(preg_split("/\n\s*\n/", trim($this->no_doc)))
            ->map(fn($b) => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $b)))))
            ->toArray();
    }

    /**
     * Formatting Accessor for no_transaksi
     */
    public function getNoTransaksiRowsAttribute(): array
    {
        if (!$this->no_transaksi) return [];
        return collect(preg_split("/\n\s*\n/", trim($this->no_transaksi)))
            ->map(fn($b) => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $b)))))
            ->toArray();
    }
}
