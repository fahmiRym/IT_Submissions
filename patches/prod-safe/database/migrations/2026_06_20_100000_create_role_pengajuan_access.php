<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_pengajuan_access', function (Blueprint $t) {
            $t->id();
            $t->string('role', 32);  // admin, accounting, spv, kabag, manager
            $t->string('jenis', 32); // Cancel, Adjust, Mutasi_Billet, Mutasi_Produk, Internal_Memo, Bundel
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();

            $t->unique(['role', 'jenis']);
            $t->index('role');
        });

        // Backfill — default sensible:
        // admin & accounting → full access (preserve existing behavior)
        // spv, kabag, manager → full access juga (superadmin restrict belakangan kalau perlu)
        // superadmin tidak perlu di-insert: selalu bypass di code.
        $roles  = ['admin', 'accounting', 'spv', 'kabag', 'manager'];
        $jenis  = ['Cancel', 'Adjust', 'Mutasi_Billet', 'Mutasi_Produk', 'Internal_Memo', 'Bundel'];
        $now    = now();
        $rows   = [];
        foreach ($roles as $r) {
            foreach ($jenis as $j) {
                $rows[] = [
                    'role' => $r,
                    'jenis' => $j,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('role_pengajuan_access')->insert($rows);

        // Drop tabel per-user — diganti total ke role-based.
        Schema::dropIfExists('user_pengajuan_access');
    }

    public function down(): void
    {
        // Re-create user_pengajuan_access (struktur dasar) — tanpa data
        Schema::create('user_pengajuan_access', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('jenis', 32);
            $t->unsignedBigInteger('granted_by')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'jenis']);
        });

        Schema::dropIfExists('role_pengajuan_access');
    }
};
