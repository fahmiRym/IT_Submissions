<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

use App\Traits\HasAuditLogs;

class Arsip extends Model
{
    use HasAuditLogs;

    protected $fillable = [
        'no_registrasi',
        'verify_token',
        'jenis_pengajuan',
        'keterangan',
        'tindakan',
        'catatan_it',
        'tindakan_in',
        'ket_tindakan_in',
        'tindakan_out',
        'ket_tindakan_out',
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
        'scan_ba_accounting',
        'scan_final',
        'total_qty_in',
        'total_qty_out',
        'detail_barang',
        'pemohon',
        'updated_by',
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
                case 'Mutasi_Produk':
                    $prefixDoc = 'RPP';
                    $padding = 4;
                    $useDay = false;
                    break;
                case 'Mutasi_Billet':
                    $prefixDoc = 'DCB';
                    $padding = 5;
                    $useDay = false;
                    break;
                case 'Adjust':
                    $prefixDoc = 'DC';
                    $padding = 4;
                    $useDay = true;
                    break;
                case 'Internal_Memo':
                    $prefixDoc = 'IM';
                    $padding = 4;
                    $useDay = false;
                    break;
                case 'Bundel':
                    $prefixDoc = 'BDL';
                    $padding = 4;
                    $useDay = false;
                    break;
                case 'Produk_Baru':
                    $prefixDoc = 'PB';
                    $padding = 4;
                    $useDay = false;
                    break;
                case 'Cancel':
                case 'Cancelled':
                    $prefixDoc = 'CANCEL';
                    $padding = 4;
                    $useDay = false;
                    break;
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
                'updated_by' => auth()->id(),
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

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    public function tindakanItems()
    {
        return $this->hasMany(ArsipTindakanItem::class)->orderBy('sort_order');
    }

    public function adjustItems()
    {
        return $this->hasMany(ArsipAdjustItem::class);
    }

    public function requesters()
    {
        return $this->hasMany(ArsipRequester::class)->orderByDesc('is_primary');
    }

    public function lampirans()
    {
        return $this->hasMany(ArsipLampiran::class)->orderBy('sort_order')->orderBy('id');
    }

    public function mutasiItems()
    {
        return $this->hasMany(ArsipMutasiItem::class);
    }

    public function bundelItems()
    {
        return $this->hasMany(ArsipBundelItem::class);
    }

    public function produkBaruItems()
    {
        return $this->hasMany(ArsipProdukBaruItem::class);
    }

    public function signatures()
    {
        return $this->hasMany(ArsipSignature::class);
    }

    public function approvals()
    {
        return $this->hasMany(ArsipApproval::class)->orderBy('step_order');
    }

    /** User-user yang di-share manual oleh pemohon/admin (Layer 2 access). */
    public function shares()
    {
        return $this->hasMany(ArsipShare::class);
    }

    public function personalNotes()
    {
        return $this->hasMany(ArsipPersonalNote::class)->orderBy('created_at');
    }

    /**
     * Cek apakah user current boleh mengedit arsip ini.
     * Aturan:
     *  - Superadmin → selalu boleh
     *  - Owner (admin_id = u.id) → boleh
     *  - Accounting + jenis Adjust → boleh (rule existing)
     *  - User di arsip_shares (user_id atau role match) → boleh edit (Layer 2 share = read+edit)
     */
    public function canBeEditedBy(?User $u): bool
    {
        if (!$u) return false;
        if ($u->role === 'superadmin') return true;
        if ((int) $this->admin_id === (int) $u->id) return true;
        if ($u->role === 'accounting' && $this->jenis_pengajuan === 'Adjust') return true;

        // Layer 2: share by user_id atau role
        return ArsipShare::where('arsip_id', $this->id)
            ->where(function ($q) use ($u) {
                $q->where(function ($x) use ($u) {
                    $x->where('target_type', 'user')->where('user_id', $u->id);
                })->orWhere(function ($x) use ($u) {
                    $x->where('target_type', 'role')->where('role', $u->role);
                });
            })
            ->exists();
    }

    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'arsip_shares')
            ->withPivot(['shared_by', 'note'])
            ->withTimestamps();
    }

    /**
     * Tahap approval yang sedang aktif (boleh ditindak):
     * step pending paling awal yang semua step sebelumnya sudah approved.
     * null = belum ada chain, sudah selesai, atau ada yang rejected.
     */
    public function currentApproval()
    {
        $steps = $this->relationLoaded('approvals')
            ? $this->approvals
            : $this->approvals()->get();

        foreach ($steps as $step) {
            if ($step->status === 'rejected') {
                return null;
            }
            if ($step->status === 'pending') {
                return $step;
            }
        }
        return null;
    }

    public function isFullyApproved(): bool
    {
        $steps = $this->relationLoaded('approvals') ? $this->approvals : $this->approvals()->get();
        return $steps->isNotEmpty() && $steps->every(fn($s) => $s->status === 'approved');
    }

    public function hasApprovalChain(): bool
    {
        return ($this->relationLoaded('approvals') ? $this->approvals->count() : $this->approvals()->count()) > 0;
    }

    /**
     * True jika rantai approval sudah mulai berjalan (ada selain Pemohon yg approved/rejected).
     */
    public function approvalStarted(): bool
    {
        $steps = $this->relationLoaded('approvals') ? $this->approvals : $this->approvals()->get();
        return $steps->contains(fn($s) => $s->role_label !== 'Pemohon' && in_array($s->status, ['approved', 'rejected']));
    }

    /**
     * Pastikan dokumen punya token verifikasi publik (untuk QR).
     */
    public function ensureVerifyToken(): string
    {
        if (empty($this->verify_token)) {
            $this->verify_token = (string) \Illuminate\Support\Str::uuid();
            $this->saveQuietly();
        }
        return $this->verify_token;
    }

    /**
     * Ambil tanda tangan untuk satu peran (Pemohon / Accounting / Departemen IT).
     */
    public function signatureFor(string $roleLabel)
    {
        if ($this->relationLoaded('signatures')) {
            return $this->signatures->firstWhere('role_label', $roleLabel);
        }
        return $this->signatures()->where('role_label', $roleLabel)->first();
    }

    /**
     * Serialize arsip untuk response API detail (dipakai ArsipApiController::show,
     * ApprovalApiController::approve/reject, SignsArsip::signArsip).
     *
     * Include: base toArray + relasi (approvals, signatures, items, lampirans)
     * + enrichment fields (is_fully_approved, current_step, actions_available).
     *
     * $user (opsional): kalau ada, isi `actions_available` (can_approve/reject/sign_self)
     * berdasarkan role user relatif ke arsip ini.
     */
    public function toApiDetailArray($user = null): array
    {
        // Eager-load semua relasi yg dipakai UI Android
        $this->loadMissing([
            'department', 'unit', 'manager', 'admin', 'superadmin',
            'adjustItems', 'mutasiItems', 'bundelItems', 'produkBaruItems',
            'signatures.delegatedFrom',
            'approvals.approver', 'approvals.delegatedFrom',
            'lampirans',
            'requesters.user:id,employee_id,name',
        ]);

        $cur = $this->currentApproval();

        $extras = [
            'is_fully_approved' => $this->isFullyApproved(),
            'approval_started'  => $this->approvalStarted(),
            'verify_url'        => $this->verify_token ? url("/verify/{$this->verify_token}") : null,
            'current_step'      => $cur ? [
                'id'              => $cur->id,
                'step'            => $cur->step_order,
                'step_order'      => $cur->step_order,
                'role_label'      => $cur->role_label,
                'role'            => $cur->role_label,
                'approver_id'     => $cur->approver_id,
                'approver'        => $cur->approver ? [
                    'id'   => $cur->approver->id,
                    'name' => $cur->approver->name,
                ] : null,
                'delegated_from'  => optional($cur->delegatedFrom)->name,
                'is_mine'         => $user && (
                    (int) $cur->approver_id === (int) $user->id
                    || ($user->role === 'superadmin' && $cur->role_label === ArsipApproval::FINAL_ROLE)
                ),
            ] : null,
        ];

        if ($user) {
            $canAct = $cur && (
                (int) $cur->approver_id === (int) $user->id
                || ($user->role === 'superadmin' && $cur->role_label === ArsipApproval::FINAL_ROLE)
            );
            $selfSignRole = match ($user->role) {
                'superadmin' => 'Departemen IT',
                'accounting' => $this->jenis_pengajuan === 'Adjust' ? 'Accounting' : null,
                default      => (int) $this->admin_id === (int) $user->id ? 'Pemohon' : null,
            };
            $extras['actions_available'] = [
                'can_approve'    => (bool) $canAct,
                'can_reject'     => (bool) $canAct,
                'can_sign_self'  => $selfSignRole !== null
                    && !$this->signatures->contains('role_label', $selfSignRole),
                'self_sign_role' => $selfSignRole,
            ];
        }

        return array_merge($this->toArray(), $extras);
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

    public function isProdukBaru()
    {
        return $this->jenis_pengajuan === 'Produk_Baru';
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

    /**
     * Text for Copy All (No Doc + Sub Transaksi for Cancel, or No Doc + Transaksi)
     */
    public function getCopyAllTextAttribute(): string
    {
        $text = $this->no_doc ?? '';
        if (!$this->no_transaksi)
            return $text;

        if (in_array(trim($this->jenis_pengajuan), ['Cancel', 'Cancelled'])) {
            $indukPrefixes = ['MO', 'PO', 'SOF', 'LL'];
            $lines = preg_split('/\r\n|\n|\r/', $this->no_transaksi);
            $subLines = array_filter($lines, function ($line) use ($indukPrefixes) {
                $trimmed = trim($line);
                if ($trimmed === '')
                    return false;
                foreach ($indukPrefixes as $prefix) {
                    if (str_starts_with($trimmed, $prefix))
                        return false;
                }
                return true;
            });
            $subText = implode("\n", array_map('trim', array_values($subLines)));
            if ($subText) {
                $text .= ($text ? "\n" : "") . $subText;
            }
        } else {
            $text .= ($text ? "\n" : "") . trim($this->no_transaksi);
        }
        return $text;
    }
}
