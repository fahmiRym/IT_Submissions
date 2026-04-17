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
        'tgl_arsip' => 'date',
        'detail_barang' => 'array'
    ];



    /**
     * ======================================================
     * PROSES ARSIP SISTEM (SHARED LOGIC)
     * ======================================================
     * Digunakan oleh Web Controller dan API Barcode Android
     */
    public static function processArchiving($id, $sequenceNumber = null)
    {
        $arsip = self::with(['department', 'unit'])->findOrFail($id);

        // Hanya blokir jika sudah punya No Dokumen (Final)
        if ($arsip->status === 'Done' && !empty($arsip->no_doc)) {
             throw new \Exception('Dokumen ini sudah diarsipkan dengan No Doc: ' . $arsip->no_doc);
        }

        return DB::transaction(function () use ($arsip, $sequenceNumber, $id) {
            $now = Carbon::now();

            // 1. Generate No Registrasi jika belum ada
            $noRegistrasiFix = $arsip->no_registrasi;
            if (empty($noRegistrasiFix)) {
                $deptObj = $arsip->department;
                $unitObj = $arsip->unit;
                $kodeDept = $deptObj->code ?? strtoupper(substr($deptObj->name, 0, 3));
                $tglCode = $now->format('ymd');

                if (!empty($unitObj->code)) {
                    $kodeUnit = $unitObj->code;
                } else {
                    $kodeUnit = str_replace(['Unit ', 'Unit', ' '], ['U', 'U', ''], $unitObj->name);
                }

                $prefixReg = "{$kodeDept}-{$tglCode}-{$kodeUnit}-";
                $lastArsip = self::where('no_registrasi', 'like', $prefixReg . '%')
                    ->where('id', '!=', $id)
                    ->orderBy('no_registrasi', 'desc')
                    ->first();

                $newSeq = 1;
                if ($lastArsip) {
                    $parts = explode('-', $lastArsip->no_registrasi);
                    $lastSegment = end($parts);
                    $newSeq = is_numeric($lastSegment) ? (int) $lastSegment + 1 : 1;
                }
                $noRegistrasiFix = $prefixReg . str_pad($newSeq, 3, '0', STR_PAD_LEFT);
            }

            // 2. Generate No Doc
            $tahun = $now->format('Y');
            $bulan = $now->format('m');
            $hari = $now->format('d');
            $prefixDoc = 'DOC';
            $padding = 4;
            $useDay = false;

            $jenis = trim(str_replace(' ', '_', $arsip->jenis_pengajuan));
            switch ($jenis) {
                case 'Mutasi_Produk': $prefixDoc = 'RPP'; $padding = 4; $useDay = false; break;
                case 'Mutasi_Billet': $prefixDoc = 'DCB'; $padding = 5; $useDay = false; break;
                case 'Adjust': $prefixDoc = 'DC'; $padding = 4; $useDay = true; break;
                case 'Internal_Memo': $prefixDoc = 'IM'; $padding = 4; $useDay = false; break;
                case 'Bundel': $prefixDoc = 'BDL'; $padding = 4; $useDay = false; break;
                case 'Cancel':
                case 'Cancelled': $prefixDoc = 'CANCEL'; $padding = 4; $useDay = false; break;
                default:
                    $prefixDoc = strtoupper(substr(str_replace(' ', '', $arsip->jenis_pengajuan), 0, 3));
                    $padding = 4;
                    $useDay = false;
                    break;
            }

            $rawSeq = $sequenceNumber;
            if (!$rawSeq) {
                $allDocs = self::where('jenis_pengajuan', $arsip->jenis_pengajuan)
                    ->whereNotNull('no_doc')
                    ->where('no_doc', 'like', "%{$tahun}%")
                    ->pluck('no_doc');

                $maxNumber = 0;
                foreach ($allDocs as $docStr) {
                    if ($jenis === 'Cancel' || $jenis === 'Cancelled') {
                        if (preg_match('/Cancelled No Doc\s*:\s*(\d+)/', $docStr, $m)) {
                            $maxNumber = max($maxNumber, (int) $m[1]);
                        }
                    } else {
                        $parts = explode('/', $docStr);
                        $lastSegment = end($parts);
                        if (is_numeric($lastSegment)) {
                            $maxNumber = max($maxNumber, (int) $lastSegment);
                        }
                    }
                }
                $rawSeq = $maxNumber + 1;
            }

            $seqDoc = str_pad($rawSeq, $padding, '0', STR_PAD_LEFT);
            if ($jenis === 'Cancel' || $jenis === 'Cancelled') {
                $finalNoDoc = "Cancelled No Doc : {$seqDoc}/{$bulan}/IT/{$tahun}";
            } else {
                $finalNoDoc = $useDay ? "{$prefixDoc}/{$tahun}/{$bulan}/{$hari}/{$seqDoc}" : "{$prefixDoc}/{$tahun}/{$bulan}/{$seqDoc}";
            }

            // 3. Update Record
            $arsip->update([
                'no_registrasi' => $noRegistrasiFix,
                'no_doc' => $finalNoDoc,
                'tgl_arsip' => $now,
                'status' => 'Done',
                'ba' => 'Done',
                'arsip' => 'Done',
                'ket_process' => 'Done',
            ]);

            // 4. Buat Notifikasi
            \App\Models\Notification::create([
                'user_id' => $arsip->admin_id,
                'arsip_id' => $arsip->id,
                'title' => 'Arsip Selesai',
                'message' => "Dokumen telah diarsipkan via Sistem.\nNo Reg: {$noRegistrasiFix}\nNo Doc: {$finalNoDoc}",
                'role_target' => 'admin',
            ]);

            return $arsip;
        });
    }

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
        if ($value > 0)
            return $value;

        // Jika 0, coba hitung dari relasi JIKA relasi sudah di-eager-load
        // Untuk Adjust, sum qty_in
        if ($this->relationLoaded('adjustItems')) {
            $sum = $this->adjustItems->sum('qty_in');
            if ($sum > 0)
                return $sum;
        }
        // Untuk Mutasi, Tujuan = In
        if ($this->relationLoaded('mutasiItems')) {
            $sum = $this->mutasiItems->where('type', 'tujuan')->sum('qty');
            if ($sum > 0)
                return $sum;
        }

        return 0;
    }

    /**
     * Compute Total Out dynamically if column is 0/null but items exist
     */
    public function getTotalQtyOutAttribute($value)
    {
        if ($value > 0)
            return $value;

        // Adjust Out
        if ($this->relationLoaded('adjustItems')) {
            $sum = $this->adjustItems->sum('qty_out');
            if ($sum > 0)
                return $sum;
        }
        // Mutasi Asal = Out
        if ($this->relationLoaded('mutasiItems')) {
            $sum = $this->mutasiItems->where('type', 'asal')->sum('qty');
            if ($sum > 0)
                return $sum;
        }
        // Bundel = Out
        if ($this->relationLoaded('bundelItems')) {
            $sum = $this->bundelItems->sum('qty');
            if ($sum > 0)
                return $sum;
        }

        return 0;
    }

    /**
     * Formatting Accessor for no_doc
     */
    public function getNoDocRowsAttribute(): array
    {
        if (!$this->no_doc)
            return [];
        return collect(preg_split("/\n\s*\n/", trim($this->no_doc)))
            ->map(fn($b) => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $b)))))
            ->toArray();
    }

    /**
     * Formatting Accessor for no_transaksi
     */
    public function getNoTransaksiRowsAttribute(): array
    {
        if (!$this->no_transaksi)
            return [];
        return collect(preg_split("/\n\s*\n/", trim($this->no_transaksi)))
            ->map(fn($b) => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $b)))))
            ->toArray();
    }
}
